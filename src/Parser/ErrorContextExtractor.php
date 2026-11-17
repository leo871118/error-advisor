<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Parser;

use LeandroPerez\ErrorAdvisor\Dto\ExtractedContext;

/**
 * Normalizes raw error strings and extracts file paths, line numbers, error codes, and symbols.
 */
final class ErrorContextExtractor
{
    public function normalize(string $raw): string
    {
        $clean = trim($raw);
        // Strip leading log timestamps such as [2026-11-17 16:30:12] or [17-Nov-2026 16:30:12 UTC]
        $clean = (string) preg_replace('/^\[[^\]]+\]\s*/', '', $clean);
        // Collapse excessive horizontal whitespace while keeping newlines intact
        $clean = (string) preg_replace('/[ \t]+/', ' ', $clean);

        return trim($clean);
    }

    public function extract(string $raw): ExtractedContext
    {
        $exceptionClass = null;
        if (preg_match('/\b(StopIteration|KeyboardInterrupt|GeneratorExit)\b/', $raw, $mPySpecial) === 1) {
            $exceptionClass = $mPySpecial[1];
        } elseif (preg_match('/(?:Uncaught\s+|exception\s+[\'"]?)([A-Z][a-zA-Z0-9_\\\\]*(?:Exception|Error))\b/i', $raw, $mEx) === 1) {
            $exceptionClass = $mEx[1];
        } elseif (preg_match('/\b(TypeError|ReferenceError|SyntaxError|RangeError|URIError|EvalError|AggregateError|AxiosError|DOMException|SecurityError|QuotaExceededError)\b/i', $raw, $mJsEx) === 1) {
            $exceptionClass = $mJsEx[1];
        } elseif (preg_match('/^([a-zA-Z0-9_.]*(?:Exception|Error))\s*:/m', $raw, $mEx) === 1) {
            $exceptionClass = $mEx[1];
        } elseif (preg_match('/\b([A-Z][a-zA-Z0-9_]*(?:Exception|Error))\b/', $raw, $mEx) === 1) {
            $exceptionClass = $mEx[1];
        }

        $errorCode = null;
        if (preg_match('/SQLSTATE\[([0-9A-Z]{5})\](?:\s*\[(\d+)\])?(?::.*?\b(\d{4})\b)?/i', $raw, $mSql) === 1) {
            $codeNum = !empty($mSql[2]) ? $mSql[2] : (!empty($mSql[3]) ? $mSql[3] : null);
            $errorCode = $codeNum !== null
                ? sprintf('SQLSTATE[%s] (%s)', strtoupper($mSql[1]), $codeNum)
                : sprintf('SQLSTATE[%s]', strtoupper($mSql[1]));
        } elseif (preg_match('/\[(?:WinError|winerror)\s+(\d+)\]/i', $raw, $mWinErr) === 1) {
            $errorCode = 'WINERROR_' . $mWinErr[1];
        } elseif (preg_match('/\[(?:Errno|errno)\s+(-?\d+)\]/i', $raw, $mErrno) === 1) {
            $errorCode = 'ERRNO_' . $mErrno[1];
        } elseif (preg_match('/\b(?:ERROR|Error)\s+(\d{3,5})\s*\(([0-9A-Z]{5})\)/i', $raw, $mMy) === 1) {
            $errorCode = sprintf('MYSQL_%s (%s)', $mMy[1], strtoupper($mMy[2]));
        } elseif (preg_match('/\b(?:ERROR|Error)\s+(\d{3,5})\b/i', $raw, $mMyNum) === 1) {
            $errorCode = sprintf('MYSQL_%s', $mMyNum[1]);
        } elseif (preg_match('/\berrno:?\s*(-?\d+)\b/i', $raw, $mErrno) === 1) {
            $errorCode = 'ERRNO_' . $mErrno[1];
        } elseif (preg_match('/MySQL server has gone away/i', $raw) === 1) {
            $errorCode = 'MYSQL_2006';
        } elseif (preg_match('/cURL error (\d+)/i', $raw, $mCurl) === 1) {
            $errorCode = 'CURL_' . $mCurl[1];
        } elseif (preg_match('/\bHTTP(?:\/[12](?:\.\d)?)?\s+([45]\d{2})\b/i', $raw, $mHttp) === 1) {
            $errorCode = 'HTTP_' . $mHttp[1];
        } elseif (preg_match('/\[(ERR_[A-Z0-9_]+)\]/i', $raw, $mErr) === 1) {
            $errorCode = strtoupper($mErr[1]);
        } elseif (preg_match('/\bcode:\s*[\'"]?([A-Z0-9_]+)[\'"]?/i', $raw, $mCode) === 1) {
            $errorCode = strtoupper($mCode[1]);
        } elseif (preg_match('/\b(ENOENT|EACCES|ECONNREFUSED|ECONNRESET|ETIMEDOUT|ENOTFOUND|EADDRINUSE|EEXIST|EISDIR|ENOTDIR|EMFILE|EPIPE|EPERM|ERESOLVE|ELIFECYCLE|ERR_[A-Z0-9_]+)\b/i', $raw, $mNode) === 1) {
            $errorCode = strtoupper($mNode[1]);
        } elseif (preg_match('/\b(?!ERROR\b)(E[A-Z0-9_]{4,}|ERR_[A-Z0-9_]+)\b/', $raw, $mNodeGeneric) === 1) {
            $errorCode = strtoupper($mNodeGeneric[1]);
        }

        $file = null;
        $line = null;
        if (preg_match('/File\s+["\']([^"\'\r\n]+)["\'],\s+line\s+(\d+)/i', $raw, $mPyLoc) === 1) {
            $file = $mPyLoc[1];
            $line = (int) $mPyLoc[2];
        } elseif (preg_match('/\bin\s+([a-zA-Z]:\\\\[^\s:()]+|\/[^\s:()]+)\s*(?:on line|:)\s*(\d+)/i', $raw, $mLoc) === 1) {
            $file = $mLoc[1];
            $line = (int) $mLoc[2];
        } elseif (preg_match('/\bat\s+(?:.*?\s+\()?([a-zA-Z]:\\\\[^\s:()]+\.[a-zA-Z0-9]+|\/[^\s:()]+\.[a-zA-Z0-9]+):(\d+)(?::\d+)?\)?/', $raw, $mJsLoc) === 1) {
            $file = $mJsLoc[1];
            $line = (int) $mJsLoc[2];
        } elseif (preg_match('/#\d+\s+([a-zA-Z]:\\\\[^\s:(]+|\/[^\s:(]+)\((\d+)\)/', $raw, $mTrace) === 1) {
            $file = $mTrace[1];
            $line = (int) $mTrace[2];
        }

        $symbol = null;
        $symbolPatterns = [
            '/Call to undefined function\s+([a-zA-Z0-9_\\\\]+)\(\)/i',
            '/Class\s+["\']?([a-zA-Z0-9_\\\\]+)["\']?\s+not found/i',
            '/Undefined (?:array key|index|offset)\s+["\']?([^"\'\r\n]+)["\']?/i',
            '/No module named\s+["\']([^"\'\r\n]+)["\']/i',
            '/cannot import name\s+["\']([^"\'\r\n]+)["\']/i',
            '/coroutine\s+["\']([a-zA-Z0-9_.]+)["\']\s+was never awaited/i',
            '/object has no attribute\s+["\']?([a-zA-Z0-9_]+)["\']?/i',
            '/KeyError:\s*["\']?([^"\'\r\n]+)["\']?/i',
            '/([a-zA-Z0-9_$]+)\s+is not defined/i',
            '/Cannot access\s+["\']([^"\'\r\n]+)["\']\s+before initialization/i',
            '/([a-zA-Z0-9_$.]+)\s+is not a function/i',
            '/reading\s+["\']?([a-zA-Z0-9_$]+)["\']?/i',
            '/Unknown column\s+["\']?([^"\']+)["\']?/i',
            '/Table\s+["\']?([^"\']+)["\']?\s+doesn\'t exist/i',
            '/Duplicate entry\s+["\']?([^"\'\r\n]+)["\']?\s+for key/i',
            '/Could not resolve host:\s*([^\s]+)/i',
        ];

        foreach ($symbolPatterns as $pattern) {
            if (preg_match($pattern, $raw, $mSym) === 1) {
                $candidate = trim($mSym[1]);
                if ($candidate !== '') {
                    $symbol = $candidate;
                    break;
                }
            }
        }

        return new ExtractedContext(
            exceptionClass: $exceptionClass,
            errorCode: $errorCode,
            file: $file,
            line: $line,
            symbol: $symbol,
        );
    }
}
