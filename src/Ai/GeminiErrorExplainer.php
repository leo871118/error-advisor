<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Ai;

use LeandroPerez\ErrorAdvisor\Dto\AdviceReport;
use LeandroPerez\ErrorAdvisor\Exception\ErrorAdvisorException;

/**
 * Optional Google Gemini adapter for generating root-cause analysis on unknown or complex errors.
 */
final readonly class GeminiErrorExplainer
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gemini-2.5-flash',
        private float $timeout = 25.0,
    ) {}

    public function explain(AdviceReport $report, string $language = 'it'): string
    {
        $apiKey = trim($this->apiKey);
        if ($apiKey === '') {
            throw new ErrorAdvisorException('Gemini API key cannot be empty.');
        }

        if (!function_exists('curl_init')) {
            throw new ErrorAdvisorException('PHP cURL extension is required to use GeminiErrorExplainer.');
        }

        $model = trim($this->model);
        if ($model === '') {
            $model = 'gemini-2.5-flash';
        }

        $langInstruction = $language === 'it'
            ? 'Respond strictly in Italian using concise technical Markdown.'
            : 'Respond strictly in English using concise technical Markdown.';

        $prompt = <<<PROMPT
            You are a Senior Software Engineer & Debugging Specialist.
            Analyze the following error message or stack trace and provide a practical, concise resolution guide:
            1. **Root Cause**: Explain clearly why this error occurs.
            2. **Step-by-Step Fix**: Provide concrete troubleshooting and remediation steps.
            3. **Code / Config Example**: Provide a minimal corrected snippet if applicable.

            {$langInstruction} Keep the response under 280 words.

            ERROR INPUT:
            {$report->rawInput}
            PROMPT;

        $body = json_encode([
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 2048,
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
        $ch = curl_init($url);
        if ($ch === false) {
            throw new ErrorAdvisorException('Failed to initialize cURL session.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT_MS => (int) round(min($this->timeout, 10.0) * 1000),
            CURLOPT_TIMEOUT_MS => (int) round($this->timeout * 1000),
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_USERAGENT => 'LeandroPerez-ErrorAdvisor/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey,
                'Expect:',
            ],
        ]);

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        unset($ch);

        if (!is_string($raw) || $curlError !== '') {
            throw new ErrorAdvisorException('Gemini API network error: ' . ($curlError !== '' ? $curlError : 'empty response'));
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new ErrorAdvisorException(sprintf('Invalid JSON response from Gemini API (HTTP %d).', $statusCode));
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $errMsg = is_string($decoded['error']['message'] ?? null)
                ? $decoded['error']['message']
                : sprintf('HTTP %d error', $statusCode);

            throw new ErrorAdvisorException('Gemini API error: ' . $errMsg);
        }

        $parts = $decoded['candidates'][0]['content']['parts'] ?? [];
        $textChunks = [];
        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (!is_array($part) || !empty($part['thought'])) {
                    continue;
                }
                if (is_string($part['text'] ?? null)) {
                    $textChunks[] = $part['text'];
                }
            }
        }

        $output = trim(implode('', $textChunks));
        if ($output === '') {
            throw new ErrorAdvisorException('Gemini API returned an empty explanation.');
        }

        return $output;
    }
}
