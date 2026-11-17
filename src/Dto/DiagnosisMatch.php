<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Dto;

use JsonSerializable;

/**
 * Single matched root-cause diagnosis with contextualized explanation and fix steps.
 */
final readonly class DiagnosisMatch implements JsonSerializable
{
    /**
     * @param list<string> $solutions
     * @param list<string> $tags
     */
    public function __construct(
        public string $ruleId,
        public string $category,
        public string $title,
        public float $confidence,
        public string $explanation,
        public array $solutions,
        public ?string $codeExample = null,
        public array $tags = [],
        public string $language = 'en',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'rule_id' => $this->ruleId,
            'category' => $this->category,
            'title' => $this->title,
            'confidence' => $this->confidence,
            'explanation' => $this->explanation,
            'solutions' => $this->solutions,
            'code_example' => $this->codeExample,
            'tags' => $this->tags,
            'language' => $this->language,
        ];
    }
}
