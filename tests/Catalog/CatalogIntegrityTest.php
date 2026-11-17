<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Tests\Catalog;

use LeandroPerez\ErrorAdvisor\Catalog\DefaultErrorCatalog;
use LeandroPerez\ErrorAdvisor\Catalog\JavaScriptErrorCatalog;
use LeandroPerez\ErrorAdvisor\Catalog\MySqlErrorCatalog;
use LeandroPerez\ErrorAdvisor\Catalog\PhpErrorCatalog;
use LeandroPerez\ErrorAdvisor\Catalog\PythonErrorCatalog;
use LeandroPerez\ErrorAdvisor\Catalog\SymfonyErrorCatalog;
use LeandroPerez\ErrorAdvisor\Catalog\WordPressErrorCatalog;
use LeandroPerez\ErrorAdvisor\Dto\ErrorRule;
use PHPUnit\Framework\TestCase;

final class CatalogIntegrityTest extends TestCase
{
    public function testDedicatedCatalogsHaveExpectedRuleCounts(): void
    {
        self::assertCount(100, PhpErrorCatalog::all(), 'PhpErrorCatalog must contain exactly 100 rules');
        self::assertCount(100, SymfonyErrorCatalog::all(), 'SymfonyErrorCatalog must contain exactly 100 rules');
        self::assertCount(100, PythonErrorCatalog::all(), 'PythonErrorCatalog must contain exactly 100 rules');
        self::assertCount(100, JavaScriptErrorCatalog::all(), 'JavaScriptErrorCatalog must contain exactly 100 rules');
        self::assertCount(100, WordPressErrorCatalog::all(), 'WordPressErrorCatalog must contain exactly 100 rules');
        self::assertCount(100, MySqlErrorCatalog::all(), 'MySqlErrorCatalog must contain exactly 100 rules');
    }

    public function testPythonErrorCatalogIntegrityAndBilingualSupport(): void
    {
        $pyRules = PythonErrorCatalog::all();
        self::assertCount(100, $pyRules);

        $seenIds = [];
        $seenPatterns = [];

        foreach ($pyRules as $rule) {
            self::assertInstanceOf(ErrorRule::class, $rule);
            self::assertTrue(
                str_starts_with($rule->id, 'py.') || str_starts_with($rule->id, 'python.'),
                "Rule '{$rule->id}' must start with 'py.' or 'python.'",
            );
            self::assertNotEmpty($rule->category);
            self::assertNotEmpty($rule->title);
            self::assertNotEmpty($rule->explanation);
            self::assertNotEmpty($rule->solutions);

            // Bilingual Italian support
            self::assertNotNull($rule->titleIt, "Rule '{$rule->id}' must have a titleIt");
            self::assertNotEmpty($rule->titleIt, "Rule '{$rule->id}' titleIt must not be empty");
            self::assertNotNull($rule->explanationIt, "Rule '{$rule->id}' must have an explanationIt");
            self::assertNotEmpty($rule->explanationIt, "Rule '{$rule->id}' explanationIt must not be empty");
            self::assertNotEmpty($rule->solutionsIt, "Rule '{$rule->id}' solutionsIt must not be empty");

            // Valid regex pattern
            self::assertNotFalse(@preg_match($rule->pattern, ''), "Pattern '{$rule->pattern}' for rule '{$rule->id}' is invalid");

            self::assertArrayNotHasKey($rule->id, $seenIds, "Duplicate rule ID found in PythonErrorCatalog: {$rule->id}");
            $seenIds[$rule->id] = true;

            $normPattern = strtolower(trim($rule->pattern));
            if (isset($seenPatterns[$normPattern])) {
                self::fail("Duplicate regex pattern found in PythonErrorCatalog for '{$rule->id}' (matches '{$seenPatterns[$normPattern]}')");
            }
            $seenPatterns[$normPattern] = $rule->id;
        }
    }

    public function testJavaScriptErrorCatalogIntegrityAndBilingualSupport(): void
    {
        $jsRules = JavaScriptErrorCatalog::all();
        self::assertCount(100, $jsRules);

        $seenIds = [];
        $seenPatterns = [];

        foreach ($jsRules as $rule) {
            self::assertInstanceOf(ErrorRule::class, $rule);
            self::assertStringStartsWith('js.', $rule->id, "Rule '{$rule->id}' must start with 'js.'");
            self::assertNotEmpty($rule->category);
            self::assertNotEmpty($rule->title);
            self::assertNotEmpty($rule->explanation);
            self::assertNotEmpty($rule->solutions);

            // Bilingual Italian support
            self::assertNotNull($rule->titleIt, "Rule '{$rule->id}' must have a titleIt");
            self::assertNotEmpty($rule->titleIt, "Rule '{$rule->id}' titleIt must not be empty");
            self::assertNotNull($rule->explanationIt, "Rule '{$rule->id}' must have an explanationIt");
            self::assertNotEmpty($rule->explanationIt, "Rule '{$rule->id}' explanationIt must not be empty");
            self::assertNotEmpty($rule->solutionsIt, "Rule '{$rule->id}' solutionsIt must not be empty");

            // Valid regex pattern
            self::assertNotFalse(@preg_match($rule->pattern, ''), "Pattern '{$rule->pattern}' for rule '{$rule->id}' is invalid");

            self::assertArrayNotHasKey($rule->id, $seenIds, "Duplicate rule ID found in JavaScriptErrorCatalog: {$rule->id}");
            $seenIds[$rule->id] = true;

            $normPattern = strtolower(trim($rule->pattern));
            if (isset($seenPatterns[$normPattern])) {
                self::fail("Duplicate regex pattern found in JavaScriptErrorCatalog for '{$rule->id}' (matches '{$seenPatterns[$normPattern]}')");
            }
            $seenPatterns[$normPattern] = $rule->id;
        }
    }

    public function testSymfonyErrorCatalogIntegrityAndBilingualSupport(): void
    {
        $sfRules = SymfonyErrorCatalog::all();
        self::assertCount(100, $sfRules);

        $seenIds = [];
        $seenPatterns = [];

        foreach ($sfRules as $rule) {
            self::assertInstanceOf(ErrorRule::class, $rule);
            self::assertStringStartsWith('symfony.', $rule->id, "Rule '{$rule->id}' must start with 'symfony.'");
            self::assertNotEmpty($rule->category);
            self::assertNotEmpty($rule->title);
            self::assertNotEmpty($rule->explanation);
            self::assertNotEmpty($rule->solutions);

            // Bilingual Italian support
            self::assertNotNull($rule->titleIt, "Rule '{$rule->id}' must have a titleIt");
            self::assertNotEmpty($rule->titleIt, "Rule '{$rule->id}' titleIt must not be empty");
            self::assertNotNull($rule->explanationIt, "Rule '{$rule->id}' must have an explanationIt");
            self::assertNotEmpty($rule->explanationIt, "Rule '{$rule->id}' explanationIt must not be empty");
            self::assertNotEmpty($rule->solutionsIt, "Rule '{$rule->id}' solutionsIt must not be empty");

            // Valid regex pattern
            self::assertNotFalse(@preg_match($rule->pattern, ''), "Pattern '{$rule->pattern}' for rule '{$rule->id}' is invalid");

            self::assertArrayNotHasKey($rule->id, $seenIds, "Duplicate rule ID found in SymfonyErrorCatalog: {$rule->id}");
            $seenIds[$rule->id] = true;

            $normPattern = strtolower(trim($rule->pattern));
            if (isset($seenPatterns[$normPattern])) {
                self::fail("Duplicate regex pattern found in SymfonyErrorCatalog for '{$rule->id}' (matches '{$seenPatterns[$normPattern]}')");
            }
            $seenPatterns[$normPattern] = $rule->id;
        }
    }

    public function testMySqlErrorCatalogIntegrityAndBilingualSupport(): void
    {
        $mysqlRules = MySqlErrorCatalog::all();
        self::assertCount(100, $mysqlRules);

        $seenIds = [];
        $seenPatterns = [];

        foreach ($mysqlRules as $rule) {
            self::assertInstanceOf(ErrorRule::class, $rule);
            self::assertStringStartsWith('mysql.', $rule->id, "Rule '{$rule->id}' must start with 'mysql.'");
            self::assertNotEmpty($rule->category);
            self::assertNotEmpty($rule->title);
            self::assertNotEmpty($rule->explanation);
            self::assertNotEmpty($rule->solutions);

            // Bilingual Italian support
            self::assertNotNull($rule->titleIt, "Rule '{$rule->id}' must have a titleIt");
            self::assertNotEmpty($rule->titleIt, "Rule '{$rule->id}' titleIt must not be empty");
            self::assertNotNull($rule->explanationIt, "Rule '{$rule->id}' must have an explanationIt");
            self::assertNotEmpty($rule->explanationIt, "Rule '{$rule->id}' explanationIt must not be empty");
            self::assertNotEmpty($rule->solutionsIt, "Rule '{$rule->id}' solutionsIt must not be empty");

            // Valid regex pattern
            self::assertNotFalse(@preg_match($rule->pattern, ''), "Pattern '{$rule->pattern}' for rule '{$rule->id}' is invalid");

            self::assertArrayNotHasKey($rule->id, $seenIds, "Duplicate rule ID found in MySqlErrorCatalog: {$rule->id}");
            $seenIds[$rule->id] = true;

            $normPattern = strtolower(trim($rule->pattern));
            if (isset($seenPatterns[$normPattern])) {
                self::fail("Duplicate regex pattern found in MySqlErrorCatalog for '{$rule->id}' (matches '{$seenPatterns[$normPattern]}')");
            }
            $seenPatterns[$normPattern] = $rule->id;
        }
    }

    public function testPhpErrorCatalogIntegrityAndBilingualSupport(): void
    {
        $phpRules = PhpErrorCatalog::all();
        self::assertCount(100, $phpRules);

        $seenIds = [];
        $seenPatterns = [];

        foreach ($phpRules as $rule) {
            self::assertInstanceOf(ErrorRule::class, $rule);
            self::assertStringStartsWith('php.', $rule->id, "Rule '{$rule->id}' must start with 'php.'");
            self::assertNotEmpty($rule->category);
            self::assertNotEmpty($rule->title);
            self::assertNotEmpty($rule->explanation);
            self::assertNotEmpty($rule->solutions);

            // Bilingual Italian support
            self::assertNotNull($rule->titleIt, "Rule '{$rule->id}' must have a titleIt");
            self::assertNotEmpty($rule->titleIt, "Rule '{$rule->id}' titleIt must not be empty");
            self::assertNotNull($rule->explanationIt, "Rule '{$rule->id}' must have an explanationIt");
            self::assertNotEmpty($rule->explanationIt, "Rule '{$rule->id}' explanationIt must not be empty");
            self::assertNotEmpty($rule->solutionsIt, "Rule '{$rule->id}' solutionsIt must not be empty");

            // Valid regex pattern
            self::assertNotFalse(@preg_match($rule->pattern, ''), "Pattern '{$rule->pattern}' for rule '{$rule->id}' is invalid");

            self::assertArrayNotHasKey($rule->id, $seenIds, "Duplicate rule ID found in PhpErrorCatalog: {$rule->id}");
            $seenIds[$rule->id] = true;

            $normPattern = strtolower(trim($rule->pattern));
            if (isset($seenPatterns[$normPattern])) {
                self::fail("Duplicate regex pattern found in PhpErrorCatalog for '{$rule->id}' (matches '{$seenPatterns[$normPattern]}')");
            }
            $seenPatterns[$normPattern] = $rule->id;
        }
    }

    public function testDefaultErrorCatalogAggregatesAllCatalogsAndRules(): void
    {
        $all = DefaultErrorCatalog::all();
        // 100 PHP + 100 Symfony + 100 Python + 100 JS + 100 WordPress + 100 MySQL + 7 HTTP + 2 Composer/Git = 609 rules
        self::assertCount(609, $all);

        $ruleIds = [];
        $patterns = [];

        foreach ($all as $rule) {
            self::assertInstanceOf(ErrorRule::class, $rule);
            self::assertNotEmpty($rule->id);
            self::assertNotEmpty($rule->category);
            self::assertNotEmpty($rule->title);
            self::assertNotEmpty($rule->pattern);
            self::assertNotEmpty($rule->explanation);
            self::assertNotEmpty($rule->solutions);

            // Verify bilingual Italian support
            self::assertNotNull($rule->titleIt, "Rule '{$rule->id}' must have a titleIt");
            self::assertNotEmpty($rule->titleIt, "Rule '{$rule->id}' titleIt must not be empty");
            self::assertNotNull($rule->explanationIt, "Rule '{$rule->id}' must have an explanationIt");
            self::assertNotEmpty($rule->explanationIt, "Rule '{$rule->id}' explanationIt must not be empty");
            self::assertNotEmpty($rule->solutionsIt, "Rule '{$rule->id}' solutionsIt must not be empty");

            // Verify regex pattern is valid
            self::assertNotFalse(@preg_match($rule->pattern, ''), "Pattern '{$rule->pattern}' for rule '{$rule->id}' is invalid");

            // Global anti-duplicate checks: unique ID and unique normalized regex pattern
            self::assertArrayNotHasKey($rule->id, $ruleIds, "Duplicate rule ID found in catalog: {$rule->id}");
            $ruleIds[$rule->id] = true;

            $normalizedPattern = strtolower(trim($rule->pattern));
            if (isset($patterns[$normalizedPattern])) {
                self::fail("Duplicate regex pattern found: rule '{$rule->id}' has the same pattern as '{$patterns[$normalizedPattern]}'");
            }
            $patterns[$normalizedPattern] = $rule->id;
        }
    }
}
