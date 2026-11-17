<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Dto;

use JsonSerializable;

/**
 * Complete diagnostic report produced for a given error log or exception message.
 */
final readonly class AdviceReport implements JsonSerializable
{
    /**
     * @param list<DiagnosisMatch> $matches
     */
    public function __construct(
        public string $rawInput,
        public string $normalizedInput,
        public ExtractedContext $context,
        public array $matches,
        public string $analyzedAt,
    ) {}

    public function hasMatch(): bool
    {
        return $this->matches !== [];
    }

    public function primaryMatch(): ?DiagnosisMatch
    {
        return $this->matches[0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'has_match' => $this->hasMatch(),
            'matches_count' => count($this->matches),
            'context' => $this->context->jsonSerialize(),
            'matches' => array_map(
                static fn(DiagnosisMatch $m): array => $m->jsonSerialize(),
                $this->matches,
            ),
            'normalized_input' => $this->normalizedInput,
            'raw_input' => $this->rawInput,
            'analyzed_at' => $this->analyzedAt,
        ];
    }
}
