# ErrorAdvisor PHP

Framework-agnostic PHP 8.3+ error and stack-trace analyzer that diagnoses common PHP, Symfony, Python, JavaScript / Node, WordPress, MySQL / MariaDB, HTTP/cURL/SSL, Composer, and Git errors with instant root-cause explanations, actionable fix steps, and optional Google Gemini AI fallback.

Features full bilingual support (**English `en` and Italian `it`**) and 600+ structured diagnostic rules (609 rules total).

---

## Features

- **Full Bilingual Support (`it` / `en`)**: Localized diagnosis titles, root-cause explanations, and step-by-step remediation in Italian (`it`, default) and English (`en`), with automatic English fallback for custom rules.
- **600+ Dedicated Rules Across 6 Catalogs (Plus Core HTTP & Git)**:
  - **PHP Runtime, Types, OOP, Filesystem, Crypto & Deprecations (8.1–8.4)** (`PhpErrorCatalog`, 100 rules)
  - **Symfony 6/7/8 DI, Routing, Twig, Security, Doctrine ORM/DBAL & Messenger** (`SymfonyErrorCatalog`, 100 rules)
  - **WordPress CMS, Multisite, REST API, Gutenberg, Media, Permalinks & Woo** (`WordPressErrorCatalog`, 100 rules)
  - **MySQL / MariaDB / PDO Errors, Replication & SQLSTATE (MySQL 8.x / MariaDB 10/11)** (`MySqlErrorCatalog`, 100 rules)
  - **JavaScript Browser, DOM, Fetch/CORS, Node.js, React, Vue & Vite** (`JavaScriptErrorCatalog`, 100 rules)
  - **Python Exceptions, OOP, Filesystem/OS, Asyncio, Pip, Django, FastAPI & Data Science (Pandas/NumPy)** (`PythonErrorCatalog`, 100 rules)
  - Plus core **HTTP / cURL / SSL**, **Composer**, and **Git** rules in `DefaultErrorCatalog` (9 rules, total **609 rules**).
- **Ecosystem Category Filtering**: Optional `$categoryFilter` (e.g. `'php'`, `'symfony'`, `'python'`, `'javascript'`, `'wordpress'`, `'mysql'`, `'mariadb'`, `'http'`, `'composer'`, `'git'`).
- **Deep Technical Context Extraction**: Automatically parses file paths, line numbers, error codes (`SQLSTATE`, MySQL numeric `1062/1451/1064`, Python `[Errno 2/13/21/111]`, `[WinError 10061]`, Node `EADDRINUSE/ERR_*`, cURL codes, HTTP statuses), exception classes, and missing symbols across PHP, Python, JS, and SQL stack traces.
- **Extensible & Retrocompatible**: Add custom domain rules with `$advisor->addRule(new ErrorRule(...))`. Custom rules written only with English parameters cleanly fall back to English when queried in Italian.
- **Optional AI Fallback**: Decoupled `GeminiErrorExplainer` adapter to diagnose unknown or complex multi-layer errors with Google Gemini.

---

## Installation

```bash
composer require leandroperez/error-advisor
```

---

## Quick Start

### 1. Bilingual Diagnosis

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use LeandroPerez\ErrorAdvisor\ErrorAdvisor;

$advisor = new ErrorAdvisor();

// Italian (default)
$reportIt = $advisor->analyze(
    'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'admin@example.com\' for key \'users.email\'',
    categoryFilter: 'mysql',
    language: 'it'
);

$matchIt = $reportIt->primaryMatch();
echo "Titolo: " . $matchIt->title . "\n";
echo "Spiegazione: " . $matchIt->explanation . "\n";
foreach ($matchIt->solutions as $step) {
    echo "  - " . $step . "\n";
}

// English
$reportEn = $advisor->analyze(
    'ModuleNotFoundError: No module named \'requests\'',
    categoryFilter: 'python',
    language: 'en'
);

$matchEn = $reportEn->primaryMatch();
echo "Title: " . $matchEn->title . "\n";
```

### 2. Multi-Ecosystem Support

```php
// Symfony
$reportSf = $advisor->analyze(
    'You have requested a non-existent service "App\\Service\\PaymentProcessor".',
    categoryFilter: 'symfony',
    language: 'it'
);

// JavaScript / Node.js
$reportJs = $advisor->analyze(
    'TypeError: Cannot read properties of undefined (reading \'map\') at UserList (/app/src/UserList.js:24:18)',
    categoryFilter: 'javascript'
);

// WordPress
$reportWp = $advisor->analyze(
    'Updating failed. The response is not a valid JSON response.',
    categoryFilter: 'wordpress'
);

// Extracted Context
echo "File: " . $reportJs->context->file . ":" . $reportJs->context->line . "\n";
echo "Symbol: " . $reportJs->context->symbol . "\n";
```

---

## Catalogs Overview

| Catalog Class | Ecosystem | Rules Count | Highlights |
| :--- | :--- | :--- | :--- |
| `PhpErrorCatalog` | PHP 8.1–8.4 | 100 | Memory/timeouts, OOP/types, readonly/enum, variables/match, JSON/PCRE, streams/files, crypto/OpenSSL, and PHP 8.4 deprecations. |
| `SymfonyErrorCatalog` | Symfony 6/7/8 | 100 | DI container, autowiring, routes, Twig templates, Security/Firewall, Doctrine ORM/DBAL, Messenger queue, and AssetMapper. |
| `WordPressErrorCatalog` | WordPress CMS | 100 | Database connection/repair, Multisite, `_doing_it_wrong`, Gutenberg REST JSON, uploads/media, permalinks/nonce, WooCommerce & Elementor. |
| `MySqlErrorCatalog` | MySQL 8.x / MariaDB 10/11 | 100 | Access denied, duplicate keys, foreign keys, syntax errors, deadlocks, Galera/WSREP, replication, JSON, and fulltext. |
| `JavaScriptErrorCatalog` | Browser, Node, React, Vue, Vite | 100 | DOM manipulation, Fetch/CORS, Event loop/Promises, Node POSIX errors, ESM vs CJS, React hooks/hydration, Vue reactivity, Vite/Webpack bundling. |
| `PythonErrorCatalog` | Python 3.x, Django, FastAPI, Pandas | 100 | Syntax/TabError, OOP/super/dataclass, iterator/StopIteration, filesystem/OS Errno/WinError, asyncio/requests/httpx, pip/venv, Django/FastAPI/Flask, SQLAlchemy, Pandas/NumPy. |
| `DefaultErrorCatalog` | All Above + Net/Git | 609 | Combines all 6 catalogs (100 rules each) plus cURL SSL 60/28, HTTP 419/429/502, Composer conflicts, and Git SSH auth. |

---

## Local Web Demo

Launch the interactive troubleshooter with ecosystem filters, bilingual switch, quick presets, and Gemini AI integration:

```bash
php -S localhost:8083 -t examples
```

Open [http://localhost:8083](http://localhost:8083) in your browser.

---

## Quality & Testing

```bash
composer test      # Run PHPUnit test suite (49 tests, 15200+ assertions)
composer stan      # Run PHPStan static analysis (Level 8, 0 errors)
composer cs-check  # Verify @PER-CS coding standard compliance
composer cs-fix    # Auto-fix code formatting
```

---

## License

This project is open-source software licensed under the [MIT License](LICENSE).
