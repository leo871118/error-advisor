<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use LeandroPerez\ErrorAdvisor\Ai\GeminiErrorExplainer;
use LeandroPerez\ErrorAdvisor\Dto\AdviceReport;
use LeandroPerez\ErrorAdvisor\ErrorAdvisor;

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$readConfigVar = static function (string $key, string $default = ''): string {
    $runtimeVal = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if (is_string($runtimeVal) && trim($runtimeVal) !== '') {
        return trim($runtimeVal);
    }

    foreach ([dirname(__DIR__) . '/.env.local', dirname(__DIR__) . '/.env'] as $envFile) {
        if (!is_file($envFile)) {
            continue;
        }
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            if (str_starts_with($trimmed, $key . '=')) {
                $raw = trim(substr($trimmed, strlen($key . '=')));
                $unquoted = trim($raw, " \t\n\r\0\x0B\"'");
                if ($unquoted !== '') {
                    return $unquoted;
                }
            }
        }
    }

    return $default;
};

$i18n = [
    'it' => [
        'page_title' => 'ErrorAdvisor PHP — Risoluzione Errori & Stack Trace Bilingue',
        'subtitle' => 'Motore diagnostico bilingue (IT / EN) e guida alla risoluzione per PHP, Symfony, Python, JS, WordPress, MySQL e Rete',
        'rules_badge' => 'Regole Integrate',
        'gemini_configured' => 'Configurata',
        'gemini_not_set' => 'Non configurata',
        'textarea_placeholder' => 'Incolla qualsiasi messaggio di errore o stack trace PHP, Symfony, Python, JavaScript, WordPress, MySQL...',
        'diagnose_btn' => 'Diagnostica Errore',
        'ask_ai_btn' => 'Chiedi a Gemini AI',
        'analyzing_btn' => 'Analisi dello stack trace in corso...',
        'lang_it' => '🇮🇹 Italiano (IT)',
        'lang_en' => '🇬🇧 English (EN)',
        'category_title' => 'Filtro Ecosistema',
        'categories' => [
            'all' => 'Tutti gli Ecosistemi',
            'php' => 'PHP',
            'symfony' => 'Symfony',
            'python' => 'Python',
            'javascript' => 'JavaScript / Node',
            'wordpress' => 'WordPress',
            'mysql' => 'MySQL / MariaDB',
            'http' => 'Rete e HTTP',
            'composer' => 'Composer & Git',
        ],
        'quick_examples' => 'Esempi rapidi:',
        'ai_card_title' => 'Guida Risolutiva AI (Google Gemini)',
        'ai_loading' => 'Analisi dello stack trace con Google Gemini in corso...',
        'ai_missing_key' => 'GEMINI_API_KEY non configurata. Imposta la variabile d\'ambiente GEMINI_API_KEY oppure crea un file .env nella root con GEMINI_API_KEY=la_tua_chiave.',
        'ai_missing_error' => 'Testo dell\'errore mancante.',
        'ai_request_error' => 'Errore durante la richiesta di diagnosi AI.',
        'context_lang' => 'Lingua:',
        'context_filter' => 'Filtro:',
        'context_code' => 'Codice:',
        'context_class' => 'Eccezione:',
        'context_symbol' => 'Simbolo:',
        'context_file' => 'File:',
        'no_match_title' => 'Nessuna regola nel catalogo corrisponde a questo errore.',
        'no_match_desc' => 'Usa il pulsante <em>Chiedi a Gemini AI</em> sopra per un\'analisi guidata con l\'intelligenza artificiale o registra una <code>ErrorRule</code> personalizzata.',
        'match_badge' => '% corrispondenza',
        'json_summary' => 'Visualizza Payload Diagnostico JSON',
        'error_label' => 'Errore:',
    ],
    'en' => [
        'page_title' => 'ErrorAdvisor PHP — Bilingual Error & Stack-Trace Troubleshooter',
        'subtitle' => 'Bilingual (IT / EN) Diagnostic Engine & Troubleshooting Guide for PHP, Symfony, Python, JS, WordPress, MySQL & Network',
        'rules_badge' => 'Built-in Rules',
        'gemini_configured' => 'Configured',
        'gemini_not_set' => 'Not Set',
        'textarea_placeholder' => 'Paste any PHP, Symfony, Python, JavaScript, WordPress, MySQL error message or stack trace...',
        'diagnose_btn' => 'Diagnose Error',
        'ask_ai_btn' => 'Ask Gemini AI',
        'analyzing_btn' => 'Analyzing stack trace...',
        'lang_it' => '🇮🇹 Italiano (IT)',
        'lang_en' => '🇬🇧 English (EN)',
        'category_title' => 'Ecosystem Filter',
        'categories' => [
            'all' => 'All Ecosystems',
            'php' => 'PHP',
            'symfony' => 'Symfony',
            'python' => 'Python',
            'javascript' => 'JavaScript / Node',
            'wordpress' => 'WordPress',
            'mysql' => 'MySQL / MariaDB',
            'http' => 'HTTP & Network',
            'composer' => 'Composer & Git',
        ],
        'quick_examples' => 'Quick examples:',
        'ai_card_title' => 'AI Troubleshooting Guide (Google Gemini)',
        'ai_loading' => 'Analyzing stack trace with Google Gemini...',
        'ai_missing_key' => 'GEMINI_API_KEY not configured. Set the GEMINI_API_KEY environment variable or create a .env file in the root with GEMINI_API_KEY=your_key.',
        'ai_missing_error' => 'Error input text is missing.',
        'ai_request_error' => 'Error requesting AI diagnosis.',
        'context_lang' => 'Lang:',
        'context_filter' => 'Filter:',
        'context_code' => 'Code:',
        'context_class' => 'Exception:',
        'context_symbol' => 'Symbol:',
        'context_file' => 'File:',
        'no_match_title' => 'No built-in rule matched this exact error.',
        'no_match_desc' => 'Click <em>Ask Gemini AI</em> above for an AI-assisted analysis, or register a custom <code>ErrorRule</code>.',
        'match_badge' => '% match',
        'json_summary' => 'View JSON Diagnosis Payload',
        'error_label' => 'Error:',
    ],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'ai_explain') {
    header('Content-Type: application/json; charset=utf-8');

    $lang = in_array($_POST['lang'] ?? 'it', ['it', 'en'], true) ? (string) $_POST['lang'] : 'it';
    $t = $i18n[$lang];

    $apiKey = $readConfigVar('GEMINI_API_KEY');
    if ($apiKey === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $t['ai_missing_key'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $rawError = trim((string) ($_POST['error_text'] ?? ''));
    if ($rawError === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $t['ai_missing_error'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $report = (new ErrorAdvisor())->analyze($rawError, null, $lang);
        $model = $readConfigVar('GEMINI_MODEL', 'gemini-2.5-flash');
        $explanation = (new GeminiErrorExplainer(apiKey: $apiKey, model: $model))->explain($report, $lang);

        echo json_encode([
            'success' => true,
            'model' => $model,
            'explanation' => $explanation,
        ], JSON_UNESCAPED_UNICODE);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$rawInput = trim((string) ($_POST['error'] ?? $_GET['error'] ?? ''));
$lang = in_array($_POST['lang'] ?? $_GET['lang'] ?? 'it', ['it', 'en'], true) ? (string) ($_POST['lang'] ?? $_GET['lang'] ?? 'it') : 'it';
$category = trim((string) ($_POST['category'] ?? $_GET['category'] ?? 'all'));
$activeCategoryFilter = ($category !== '' && $category !== 'all') ? $category : null;

$t = $i18n[$lang];

/** @var AdviceReport|null $report */
$report = null;
$reportJson = null;
$error = null;
$hasGeminiKey = $readConfigVar('GEMINI_API_KEY') !== '';
$advisor = new ErrorAdvisor();

if ($rawInput !== '') {
    try {
        $report = $advisor->analyze($rawInput, $activeCategoryFilter, $lang);
        $reportJson = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }
}

$presets = [
    'PHP: Memory Limit' => [
        'category' => 'php',
        'text' => 'PHP Fatal error: Allowed memory size of 134217728 bytes exhausted (tried to allocate 33554432 bytes) in /var/www/html/src/ImportService.php on line 88',
    ],
    'PHP: Headers Sent' => [
        'category' => 'php',
        'text' => 'Warning: Cannot modify header information - headers already sent by (output started at C:\\xampp\\htdocs\\index.php:1) in C:\\xampp\\htdocs\\auth.php on line 14',
    ],
    'Symfony: ServiceNotFound' => [
        'category' => 'symfony',
        'text' => 'You have requested a non-existent service "App\\Service\\PaymentProcessor". Did you mean this: "App\\Service\\StripePaymentProcessor"?',
    ],
    'Symfony: RouteNotFound' => [
        'category' => 'symfony',
        'text' => 'No route found for "GET https://example.com/api/v2/orders" (404 Not Found)',
    ],
    'Symfony: Doctrine TableNotFound' => [
        'category' => 'symfony',
        'text' => 'An exception occurred while executing a query: SQLSTATE[42S02]: Base table or view not found: 1146 Table \'app_db.orders\' doesn\'t exist in Doctrine\\DBAL\\Exception\\TableNotFoundException',
    ],
    'Python: ModuleNotFound' => [
        'category' => 'python',
        'text' => "Traceback (most recent call last):\n  File \"app/main.py\", line 3, in <module>\n    import requests\nModuleNotFoundError: No module named 'requests'",
    ],
    'Python: Circular Import' => [
        'category' => 'python',
        'text' => "ImportError: cannot import name 'user_service' from partially initialized module 'app.services' (most likely due to a circular import)",
    ],
    'Django: No Such Table' => [
        'category' => 'python',
        'text' => 'django.db.utils.OperationalError: no such table: auth_user',
    ],
    'Pandas: SettingWithCopy' => [
        'category' => 'python',
        'text' => 'SettingWithCopyWarning: A value is trying to be set on a copy of a slice from a DataFrame.',
    ],
    'Python: KeyError' => [
        'category' => 'python',
        'text' => "Traceback (most recent call last):\n  File \"services/processor.py\", line 42, in process_event\n    user_id = event[\"user_id\"]\nKeyError: 'user_id'",
    ],
    'JS: Cannot Read Undefined' => [
        'category' => 'javascript',
        'text' => "TypeError: Cannot read properties of undefined (reading 'map')\n    at UserList (/app/src/components/UserList.js:24:18)",
    ],
    'JS: EADDRINUSE' => [
        'category' => 'javascript',
        'text' => "Error: listen EADDRINUSE: address already in use :::3000\n    at Server.setupListenHandle [as _listen2] (node:net:1898:16)",
    ],
    'JS: SSR Window' => [
        'category' => 'javascript',
        'text' => "ReferenceError: window is not defined\n    at Counter (/app/src/Counter.jsx:15:3)",
    ],
    'React: Re-renders' => [
        'category' => 'javascript',
        'text' => 'Error: Too many re-renders. React limits the number of renders to prevent an infinite loop.',
    ],
    'WordPress: DB Connection' => [
        'category' => 'wordpress',
        'text' => 'Error establishing a database connection',
    ],
    'WordPress: Invalid JSON' => [
        'category' => 'wordpress',
        'text' => 'Updating failed. The response is not a valid JSON response.',
    ],
    'MySQL: 1045 Access Denied' => [
        'category' => 'mysql',
        'text' => "SQLSTATE[HY000] [1045] Access denied for user 'app_user'@'localhost' (using password: YES)",
    ],
    'MySQL: 1062 Duplicate' => [
        'category' => 'mysql',
        'text' => "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'admin@example.com' for key 'users.email_unique'",
    ],
    'HTTP: cURL SSL 60' => [
        'category' => 'http',
        'text' => 'cURL error 60: SSL certificate problem: unable to get local issuer certificate',
    ],
];

$esc = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html lang="<?= $esc($lang) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $esc($t['page_title']) ?></title>
    <style>
        :root {
            --bg: #0b1120;
            --surface: #111827;
            --surface-alt: #0f172a;
            --border: #1e293b;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --ai-accent: #6366f1;
            --ai-hover: #4f46e5;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }
        .container { max-width: 1080px; margin: 0 auto; padding: 2rem 1.25rem 4rem; }
        header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        header h1 { margin: 0; font-size: 1.5rem; display: flex; align-items: center; gap: .6rem; }
        header p { margin: .25rem 0 0; color: var(--muted); font-size: .9rem; }
        .search-card { background: var(--surface); border: 1px solid var(--border); border-radius: .85rem; padding: 1.25rem; margin-bottom: 1.5rem; }
        .error-textarea {
            width: 100%;
            min-height: 120px;
            padding: .85rem 1rem;
            border-radius: .55rem;
            border: 1px solid #334155;
            background: var(--surface-alt);
            color: var(--text);
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .86rem;
            resize: vertical;
        }
        .error-textarea:focus { outline: none; border-color: var(--accent); }
        .form-actions { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: .75rem; margin-top: .85rem; }
        .controls-group { display: flex; flex-wrap: wrap; gap: .55rem; align-items: center; }
        .btn-primary { padding: .65rem 1.35rem; border-radius: .55rem; border: 0; background: var(--accent); color: #fff; font-weight: 600; font-size: .92rem; cursor: pointer; }
        .btn-primary:hover:not(:disabled) { background: var(--accent-hover); }
        .btn-ai { padding: .6rem 1.05rem; border-radius: .55rem; border: 1px solid rgba(99, 102, 241, .45); background: var(--ai-accent); color: #fff; font-weight: 600; font-size: .85rem; cursor: pointer; }
        .btn-ai:hover:not(:disabled) { background: var(--ai-hover); }
        .btn-ai:disabled { opacity: .6; cursor: not-allowed; }
        .select-input { padding: .55rem .75rem; border-radius: .5rem; border: 1px solid #334155; background: var(--surface-alt); color: var(--text); font-size: .84rem; font-weight: 600; }
        .presets { margin-top: 1rem; display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; font-size: .8rem; color: var(--muted); }
        .preset-btn {
            color: #cbd5e1;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            padding: .28rem .65rem;
            border-radius: .4rem;
            font-size: .78rem;
            cursor: pointer;
            transition: all .15s ease;
        }
        .preset-btn:hover { border-color: var(--accent); color: #fff; background: #1e293b; }
        .alert-error { background: rgba(127, 29, 29, .35); border: 1px solid #991b1b; color: #fecaca; padding: 1rem 1.25rem; border-radius: .75rem; margin-bottom: 1.5rem; }
        .alert-warn { background: rgba(120, 53, 15, .35); border: 1px solid #92400e; color: #fde68a; padding: 1rem 1.25rem; border-radius: .75rem; margin-bottom: 1.5rem; }
        .context-bar { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.25rem; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: .85rem; padding: 1.25rem 1.4rem; margin-bottom: 1.25rem; }
        .card-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: .5rem; margin-bottom: .9rem; padding-bottom: .75rem; border-bottom: 1px solid var(--border); }
        .card-title { margin: 0; font-size: 1.05rem; font-weight: 700; }
        .badge { display: inline-flex; align-items: center; padding: .18rem .55rem; border-radius: .4rem; font-size: .75rem; font-weight: 700; }
        .badge-blue { background: rgba(59, 130, 246, .15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, .3); }
        .badge-green { background: rgba(16, 185, 129, .15); color: #34d399; border: 1px solid rgba(16, 185, 129, .3); }
        .badge-amber { background: rgba(245, 158, 11, .15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, .3); }
        .badge-purple { background: rgba(168, 85, 247, .15); color: #c084fc; border: 1px solid rgba(168, 85, 247, .3); }
        .badge-slate { background: #1e293b; color: #cbd5e1; border: 1px solid #334155; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .82rem; }
        .fix-list { margin: .75rem 0 0; padding-left: 1.25rem; color: #e2e8f0; font-size: .9rem; }
        .fix-list li { margin-bottom: .45rem; }
        pre { background: #020617; padding: .95rem 1rem; border-radius: .55rem; overflow-x: auto; font-size: .8rem; color: #e2e8f0; margin-top: .85rem; border: 1px solid var(--border); }
        .ai-card { background: linear-gradient(180deg, rgba(30, 27, 75, .45) 0%, var(--surface) 100%); border: 1px solid rgba(99, 102, 241, .4); border-radius: .85rem; padding: 1.25rem 1.4rem; margin-bottom: 1.5rem; display: none; }
        .ai-card.visible { display: block; }
        .ai-loader { display: none; align-items: center; gap: .75rem; color: #c7d2fe; font-size: .9rem; }
        .ai-loader.visible { display: flex; }
        .spinner-sm { width: 18px; height: 18px; border: 2.5px solid rgba(165, 180, 252, .3); border-top-color: #a5b4fc; border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        details.json-box { background: var(--surface); border: 1px solid var(--border); border-radius: .85rem; padding: 1rem 1.25rem; }
        details.json-box summary { cursor: pointer; font-weight: 700; font-size: .9rem; color: #cbd5e1; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div>
            <h1>ErrorAdvisor PHP <span class="badge badge-blue">v1.1</span></h1>
            <p><?= $esc($t['subtitle']) ?></p>
        </div>
        <div style="display:flex; gap:.6rem; align-items:center;">
            <span class="badge badge-purple"><?= count($advisor->getRules()) ?> <?= $esc($t['rules_badge']) ?></span>
            <span class="badge <?= $hasGeminiKey ? 'badge-green' : 'badge-amber' ?>">
                GEMINI_API_KEY: <?= $esc($hasGeminiKey ? $t['gemini_configured'] : $t['gemini_not_set']) ?>
            </span>
        </div>
    </header>

    <div class="search-card">
        <form id="advisorForm" method="post">
            <textarea id="errorTextarea" name="error" class="error-textarea" placeholder="<?= $esc($t['textarea_placeholder']) ?>" required><?= $esc($rawInput) ?></textarea>
            
            <div class="form-actions">
                <div class="controls-group">
                    <button type="submit" class="btn-primary"><?= $esc($t['diagnose_btn']) ?></button>
                    
                    <select name="lang" id="langSelect" class="select-input" title="Output Language">
                        <option value="it" <?= $lang === 'it' ? 'selected' : '' ?>><?= $esc($t['lang_it']) ?></option>
                        <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>><?= $esc($t['lang_en']) ?></option>
                    </select>

                    <select name="category" id="categorySelect" class="select-input" title="<?= $esc($t['category_title']) ?>">
                        <?php foreach ($t['categories'] as $catKey => $catLabel): ?>
                            <option value="<?= $esc($catKey) ?>" <?= $category === $catKey ? 'selected' : '' ?>><?= $esc($catLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($rawInput !== ''): ?>
                    <div class="controls-group">
                        <button type="button" id="aiExplainBtn" class="btn-ai"><?= $esc($t['ask_ai_btn']) ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <div class="presets">
            <span><?= $esc($t['quick_examples']) ?></span>
            <?php foreach ($presets as $label => $data): ?>
                <button type="button" class="preset-btn" data-category="<?= $esc($data['category']) ?>" data-sample="<?= $esc($data['text']) ?>"><?= $esc($label) ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="aiCard" class="ai-card">
        <div class="card-header">
            <h2 class="card-title"><?= $esc($t['ai_card_title']) ?></h2>
            <span id="aiModelBadge" class="badge badge-blue">Gemini</span>
        </div>
        <div id="aiLoader" class="ai-loader">
            <div class="spinner-sm"></div>
            <span><?= $esc($t['ai_loading']) ?></span>
        </div>
        <div id="aiErrorBox" class="alert-error" style="display:none; margin-bottom:0;"></div>
        <div id="aiOutput" style="font-size:.9rem; line-height:1.65;"></div>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert-error"><strong><?= $esc($t['error_label']) ?></strong> <?= $esc($error) ?></div>
    <?php elseif ($report !== null): ?>
        <div class="context-bar">
            <span class="badge badge-blue mono"><?= $esc($t['context_lang']) ?> <?= strtoupper($esc($report->primaryMatch()?->language ?? $lang)) ?></span>
            <?php if ($activeCategoryFilter !== null): ?>
                <span class="badge badge-purple mono"><?= $esc($t['context_filter']) ?> <?= $esc($activeCategoryFilter) ?></span>
            <?php endif; ?>
            <?php if ($report->context->errorCode !== null): ?>
                <span class="badge badge-blue mono"><?= $esc($t['context_code']) ?> <?= $esc($report->context->errorCode) ?></span>
            <?php endif; ?>
            <?php if ($report->context->exceptionClass !== null): ?>
                <span class="badge badge-slate mono"><?= $esc($t['context_class']) ?> <?= $esc($report->context->exceptionClass) ?></span>
            <?php endif; ?>
            <?php if ($report->context->symbol !== null): ?>
                <span class="badge badge-amber mono"><?= $esc($t['context_symbol']) ?> <?= $esc($report->context->symbol) ?></span>
            <?php endif; ?>
            <?php if ($report->context->file !== null): ?>
                <span class="badge badge-slate mono">
                    <?= $esc($t['context_file']) ?> <?= $esc($report->context->file) ?><?= $report->context->line !== null ? ':' . $esc($report->context->line) : '' ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$report->hasMatch()): ?>
            <div class="alert-warn">
                <strong><?= $esc($t['no_match_title']) ?></strong> <?= $t['no_match_desc'] ?>
            </div>
        <?php else: ?>
            <?php foreach ($report->matches as $match): ?>
                <div class="card">
                    <div class="card-header">
                        <div style="display:flex; align-items:center; gap:.55rem; flex-wrap:wrap;">
                            <span class="badge badge-blue"><?= $esc($match->category) ?></span>
                            <h2 class="card-title"><?= $esc($match->title) ?></h2>
                        </div>
                        <span class="badge badge-green mono"><?= $esc((int) round($match->confidence * 100)) ?><?= $esc($t['match_badge']) ?></span>
                    </div>
                    <p style="margin:0; color:#cbd5e1; font-size:.92rem;"><?= $esc($match->explanation) ?></p>
                    <ol class="fix-list">
                        <?php foreach ($match->solutions as $step): ?>
                            <li><?= $esc($step) ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php if ($match->codeExample !== null): ?>
                        <pre><?= $esc($match->codeExample) ?></pre>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <details class="json-box">
            <summary><?= $esc($t['json_summary']) ?></summary>
            <pre><?= $esc($reportJson) ?></pre>
        </details>
    <?php endif; ?>
</div>

<script>
(() => {
    const form = document.getElementById('advisorForm');
    const textarea = document.getElementById('errorTextarea');
    const langSelect = document.getElementById('langSelect');
    const categorySelect = document.getElementById('categorySelect');

    if (langSelect) {
        langSelect.addEventListener('change', () => {
            const errorText = (textarea?.value || '').trim();
            const newLang = langSelect.value;
            const categoryVal = categorySelect ? categorySelect.value : 'all';

            if (errorText !== '') {
                if (form) {
                    form.submit();
                }
            } else {
                const params = new URLSearchParams();
                params.set('lang', newLang);
                if (categoryVal && categoryVal !== 'all') {
                    params.set('category', categoryVal);
                }
                window.location.href = window.location.pathname + '?' + params.toString();
            }
        });
    }

    document.querySelectorAll('.preset-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (textarea && form) {
                textarea.value = btn.getAttribute('data-sample') || '';
                const presetCat = btn.getAttribute('data-category');
                if (presetCat && categorySelect) {
                    categorySelect.value = presetCat;
                }
                form.submit();
            }
        });
    });

    const aiBtn = document.getElementById('aiExplainBtn');
    const aiCard = document.getElementById('aiCard');
    const aiLoader = document.getElementById('aiLoader');
    const aiErrorBox = document.getElementById('aiErrorBox');
    const aiOutput = document.getElementById('aiOutput');
    const aiModelBadge = document.getElementById('aiModelBadge');

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };

    const renderMarkdown = (text) => {
        let html = escapeHtml(text || '');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/`([^`]+)`/g, '<code class="mono">$1</code>');
        html = html.replace(/\n{2,}/g, '<br><br>');
        html = html.replace(/\n/g, '<br>');
        return html;
    };

    const analyzingText = <?= json_encode($t['analyzing_btn']) ?>;
    const defaultRequestError = <?= json_encode($t['ai_request_error']) ?>;

    if (aiBtn) {
        aiBtn.addEventListener('click', async () => {
            const errorText = (textarea?.value || '').trim();
            const lang = langSelect ? langSelect.value : 'it';

            if (aiCard) aiCard.classList.add('visible');
            if (aiLoader) aiLoader.classList.add('visible');
            if (aiErrorBox) { aiErrorBox.style.display = 'none'; aiErrorBox.textContent = ''; }
            if (aiOutput) aiOutput.innerHTML = '';

            aiBtn.disabled = true;
            const orig = aiBtn.textContent;
            aiBtn.textContent = analyzingText;

            try {
                const res = await fetch(window.location.pathname, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                    body: new URLSearchParams({ action: 'ai_explain', error_text: errorText, lang }).toString(),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    if (aiErrorBox) {
                        aiErrorBox.textContent = data.error || `HTTP ${res.status} error`;
                        aiErrorBox.style.display = 'block';
                    }
                    return;
                }
                if (aiModelBadge && data.model) aiModelBadge.textContent = data.model;
                if (aiOutput) aiOutput.innerHTML = renderMarkdown(data.explanation || '');
            } catch (err) {
                if (aiErrorBox) {
                    aiErrorBox.textContent = err?.message || defaultRequestError;
                    aiErrorBox.style.display = 'block';
                }
            } finally {
                if (aiLoader) aiLoader.classList.remove('visible');
                aiBtn.disabled = false;
                aiBtn.textContent = orig;
            }
        });
    }
})();
</script>
</body>
</html>
