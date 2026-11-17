<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor;

use DateTimeImmutable;
use DateTimeInterface;
use LeandroPerez\ErrorAdvisor\Catalog\DefaultErrorCatalog;
use LeandroPerez\ErrorAdvisor\Dto\AdviceReport;
use LeandroPerez\ErrorAdvisor\Dto\DiagnosisMatch;
use LeandroPerez\ErrorAdvisor\Dto\ErrorRule;
use LeandroPerez\ErrorAdvisor\Exception\ErrorAdvisorException;
use LeandroPerez\ErrorAdvisor\Parser\ErrorContextExtractor;

/**
 * Main entry point for diagnosing error messages, exceptions, and stack traces.
 */
final class ErrorAdvisor
{
    /** @var list<ErrorRule> */
    private array $rules;

    private ErrorContextExtractor $extractor;

    /**
     * @param list<ErrorRule>|null $rules
     */
    public function __construct(
        ?array $rules = null,
        ?ErrorContextExtractor $extractor = null,
    ) {
        $this->rules = $rules ?? DefaultErrorCatalog::all();
        $this->extractor = $extractor ?? new ErrorContextExtractor();
    }

    public function addRule(ErrorRule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @return list<ErrorRule>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function analyze(
        string $rawError,
        ?string $categoryFilter = null,
        string $language = 'it',
    ): AdviceReport {
        if (trim($rawError) === '') {
            throw new ErrorAdvisorException('Error input cannot be empty.');
        }

        $normalizedLang = strtolower(trim($language));
        if ($normalizedLang !== 'it' && $normalizedLang !== 'en') {
            $normalizedLang = 'en';
        }

        $normalized = $this->extractor->normalize($rawError);
        $context = $this->extractor->extract($normalized);
        $matches = [];

        $cleanFilter = $categoryFilter !== null ? strtolower(trim($categoryFilter)) : null;
        if ($cleanFilter === '') {
            $cleanFilter = null;
        }

        foreach ($this->rules as $rule) {
            if ($cleanFilter !== null && !$this->matchesCategoryFilter($rule, $cleanFilter)) {
                continue;
            }

            if (@preg_match($rule->pattern, $normalized, $captures) === 1) {
                $capturedSymbol = isset($captures[1]) && trim($captures[1]) !== ''
                    ? trim($captures[1])
                    : ($context->symbol ?? 'target');

                $title = ($normalizedLang === 'it' && $rule->titleIt !== null && trim($rule->titleIt) !== '')
                    ? $rule->titleIt
                    : $rule->title;

                $explanation = ($normalizedLang === 'it' && $rule->explanationIt !== null && trim($rule->explanationIt) !== '')
                    ? $rule->explanationIt
                    : $rule->explanation;

                $solutions = ($normalizedLang === 'it' && !empty($rule->solutionsIt))
                    ? $rule->solutionsIt
                    : $rule->solutions;

                $matches[] = new DiagnosisMatch(
                    ruleId: $rule->id,
                    category: $rule->category,
                    title: $this->interpolate($title, $capturedSymbol),
                    confidence: $rule->confidence,
                    explanation: $this->interpolate($explanation, $capturedSymbol),
                    solutions: array_map(
                        fn(string $step): string => $this->interpolate($step, $capturedSymbol),
                        $solutions,
                    ),
                    codeExample: $rule->codeExample !== null
                        ? $this->interpolate($rule->codeExample, $capturedSymbol)
                        : null,
                    tags: $rule->tags,
                    language: $normalizedLang,
                );
            }
        }

        usort(
            $matches,
            static fn(DiagnosisMatch $a, DiagnosisMatch $b): int => $b->confidence <=> $a->confidence,
        );

        return new AdviceReport(
            rawInput: $rawError,
            normalizedInput: $normalized,
            context: $context,
            matches: $matches,
            analyzedAt: (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        );
    }

    private function matchesCategoryFilter(ErrorRule $rule, string $filter): bool
    {
        $categoryLower = strtolower($rule->category);
        $idLower = strtolower($rule->id);

        if (str_contains($categoryLower, $filter) || str_contains($idLower, $filter)) {
            return true;
        }

        foreach ($rule->tags as $tag) {
            $tagLower = strtolower($tag);
            if ($tagLower === $filter || str_contains($tagLower, $filter) || str_contains($filter, $tagLower)) {
                return true;
            }
        }

        // Handle common ecosystem aliases
        return match ($filter) {
            'symfony', 'sf' => str_contains($categoryLower, 'symfony')
                || str_starts_with($idLower, 'symfony.'),
            'mysql', 'mariadb', 'sql', 'db' => str_contains($categoryLower, 'mysql')
                || str_contains($categoryLower, 'mariadb')
                || str_contains($categoryLower, 'database')
                || str_starts_with($idLower, 'mysql.')
                || str_starts_with($idLower, 'db.'),
            'js', 'javascript', 'node' => str_contains($categoryLower, 'javascript')
                || str_contains($categoryLower, 'node')
                || str_starts_with($idLower, 'js.'),
            'py', 'python' => str_contains($categoryLower, 'python')
                || str_starts_with($idLower, 'py.'),
            'php' => str_contains($categoryLower, 'php')
                || str_starts_with($idLower, 'php.'),
            'wp', 'wordpress' => str_contains($categoryLower, 'wordpress')
                || str_starts_with($idLower, 'wp.'),
            'http', 'curl', 'network' => str_contains($categoryLower, 'http')
                || str_contains($categoryLower, 'curl')
                || str_contains($categoryLower, 'network')
                || str_starts_with($idLower, 'http.'),
            'composer' => str_contains($categoryLower, 'composer')
                || str_starts_with($idLower, 'composer.'),
            'git' => str_contains($categoryLower, 'git')
                || str_starts_with($idLower, 'git.'),
            default => false,
        };
    }

    private function interpolate(string $template, string $matchedValue): string
    {
        return str_replace('{match}', $matchedValue, $template);
    }
}
