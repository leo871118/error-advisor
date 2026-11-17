<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Dto;

/**
 * Defines a pattern-matching diagnostic rule and its corresponding remediation steps.
 */
final readonly class ErrorRule
{
    /**
     * @param list<string> $solutions
     * @param list<string> $tags
     * @param list<string> $solutionsIt
     */
    public function __construct(
        public string $id,
        public string $category,
        public string $title,
        public string $pattern,
        public string $explanation,
        public array $solutions,
        public ?string $codeExample = null,
        public array $tags = [],
        public float $confidence = 0.95,
        public ?string $titleIt = null,
        public ?string $explanationIt = null,
        public array $solutionsIt = [],
    ) {}
}
