<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Dto;

use JsonSerializable;

/**
 * Structured technical metadata extracted automatically from an error message or stack trace.
 */
final readonly class ExtractedContext implements JsonSerializable
{
    public function __construct(
        public ?string $exceptionClass = null,
        public ?string $errorCode = null,
        public ?string $file = null,
        public ?int $line = null,
        public ?string $symbol = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'exception_class' => $this->exceptionClass,
            'error_code' => $this->errorCode,
            'file' => $this->file,
            'line' => $this->line,
            'symbol' => $this->symbol,
        ];
    }
}
