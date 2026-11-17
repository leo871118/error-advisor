<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Tests\Parser;

use LeandroPerez\ErrorAdvisor\Dto\ErrorRule;
use LeandroPerez\ErrorAdvisor\ErrorAdvisor;
use LeandroPerez\ErrorAdvisor\Exception\ErrorAdvisorException;
use PHPUnit\Framework\TestCase;

final class ErrorAdvisorTest extends TestCase
{
    private ErrorAdvisor $advisor;

    protected function setUp(): void
    {
        $this->advisor = new ErrorAdvisor();
    }

    public function testDiagnosesPhpUndefinedFunctionInItalianByDefault(): void
    {
        $log = '[17-Nov-2026 15:20:00 UTC] PHP Fatal error: Uncaught Error: Call to undefined function curl_init() in /var/www/app/src/Client.php:42';
        $report = $this->advisor->analyze($log, null, 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('php.undefined_function', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('curl_init', $primary->title);
        self::assertStringContainsString('Chiamata a Funzione Non Definita', $primary->title);
        self::assertStringContainsString('estensione PHP', $primary->explanation);
        self::assertSame('/var/www/app/src/Client.php', $report->context->file);
        self::assertSame(42, $report->context->line);
        self::assertSame('curl_init', $report->context->symbol);
    }

    public function testDiagnosesPhpUndefinedFunctionInEnglish(): void
    {
        $log = 'PHP Fatal error: Uncaught Error: Call to undefined function curl_init() in /app/index.php on line 12';
        $report = $this->advisor->analyze($log, null, 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('en', $primary->language);
        self::assertStringContainsString('Call to Undefined Function', $primary->title);
        self::assertStringContainsString('PHP cannot find the function', $primary->explanation);
    }

    public function testDiagnosesPhpInstantiateAbstractOrInterface(): void
    {
        $error = 'Fatal error: Uncaught Error: Cannot instantiate interface App\\Contract\\PaymentGatewayInterface in /app/src/Controller/CheckoutController.php:24';
        $report = $this->advisor->analyze($error, 'php', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('php.instantiate_abstract_or_interface', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Impossibile Istanziare la Classe Astratta o Interfaccia', $primary->title);
        self::assertStringContainsString('App\\Contract\\PaymentGatewayInterface', $primary->title);
    }

    public function testDiagnosesPhpUndefinedVariable(): void
    {
        $error = 'Warning: Undefined variable $uninitializedUser in /var/www/html/profile.php on line 55';
        $report = $this->advisor->analyze($error, 'php', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('php.undefined_variable', $primary->ruleId);
        self::assertSame('en', $primary->language);
        self::assertStringContainsString('Undefined Variable $uninitializedUser', $primary->title);
    }

    public function testDiagnosesPhpJsonSyntaxError(): void
    {
        $error = 'Fatal error: Uncaught JsonException: Syntax error in /app/src/Api/Client.php:89';
        $report = $this->advisor->analyze($error, 'php', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('php.json_exception_syntax', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Errore di Sintassi JSON Durante la Decodifica', $primary->title);
    }

    public function testDiagnosesPythonModuleNotFoundAndExtractsContext(): void
    {
        $error = "Traceback (most recent call last):\n  File \"/app/backend/server.py\", line 15, in <module>\n    import pydantic\nModuleNotFoundError: No module named 'pydantic'";
        $report = $this->advisor->analyze($error, 'python', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('py.module_not_found', $primary->ruleId);
        self::assertStringContainsString('pydantic', $primary->title);
        self::assertSame('/app/backend/server.py', $report->context->file);
        self::assertSame(15, $report->context->line);
        self::assertSame('pydantic', $report->context->symbol);
        self::assertSame('ModuleNotFoundError', $report->context->exceptionClass);
    }

    public function testDiagnosesPythonErrnoContext(): void
    {
        $error = "FileNotFoundError: [Errno 2] No such file or directory: 'config.json'";
        $report = $this->advisor->analyze($error, 'python', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('py.file_not_found_errno_2', $primary->ruleId);
        self::assertSame('ERRNO_2', $report->context->errorCode);
    }

    public function testDiagnosesPythonCircularImport(): void
    {
        $error = "ImportError: cannot import name 'user_service' from partially initialized module 'app.services' (most likely due to a circular import)";
        $report = $this->advisor->analyze($error, 'python', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('python.circular_import', $primary->ruleId);
        self::assertSame('ImportError', $report->context->exceptionClass);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Dipendenza Circolare', $primary->title);
    }

    public function testDiagnosesPythonStopIterationExceptionClass(): void
    {
        $error = "Traceback (most recent call last):\n  File \"script.py\", line 10, in <module>\n    item = next(it)\nStopIteration";
        $report = $this->advisor->analyze($error, 'python', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('python.stop_iteration', $primary->ruleId);
        self::assertSame('StopIteration', $report->context->exceptionClass);
        self::assertSame('script.py', $report->context->file);
        self::assertSame(10, $report->context->line);
    }

    public function testDiagnosesPythonWinErrorConnectionRefused(): void
    {
        $error = 'ConnectionRefusedError: [WinError 10061] No connection could be made because the target machine actively refused it';
        $report = $this->advisor->analyze($error, 'python', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('python.connection_refused_error', $primary->ruleId);
        self::assertSame('WINERROR_10061', $report->context->errorCode);
        self::assertSame('ConnectionRefusedError', $report->context->exceptionClass);
    }

    public function testDiagnosesDjangoOperationalErrorNoSuchTable(): void
    {
        $error = 'django.db.utils.OperationalError: no such table: auth_user';
        $report = $this->advisor->analyze($error, 'python', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('python.django_operational_error_no_such_table', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Tabella', $primary->title);
    }

    public function testDiagnosesPandasSettingWithCopyWarning(): void
    {
        $error = 'SettingWithCopyWarning: A value is trying to be set on a copy of a slice from a DataFrame.';
        $report = $this->advisor->analyze($error, 'python', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('python.pandas_setting_with_copy_warning', $primary->ruleId);
        self::assertStringContainsString('SettingWithCopyWarning', $primary->title);
    }

    public function testDiagnosesAsyncioCoroutineNeverAwaited(): void
    {
        $error = "RuntimeWarning: coroutine 'fetch_orders' was never awaited";
        $report = $this->advisor->analyze($error, 'python', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('python.asyncio_coroutine_never_awaited', $primary->ruleId);
        self::assertSame('fetch_orders', $report->context->symbol);
    }

    public function testDiagnosesJavaScriptErrorAndExtractsContext(): void
    {
        $error = "TypeError: Cannot read properties of undefined (reading 'length')\n    at validateInput (/app/dist/bundle.js:142:15)";
        $report = $this->advisor->analyze($error, 'javascript', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.cannot_read_undefined', $primary->ruleId);
        self::assertSame('/app/dist/bundle.js', $report->context->file);
        self::assertSame(142, $report->context->line);
        self::assertSame('length', $report->context->symbol);
    }

    public function testDiagnosesNodeEaddrinuseContext(): void
    {
        $error = 'Error: listen EADDRINUSE: address already in use :::8080';
        $report = $this->advisor->analyze($error, 'js', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.listen_eaddrinuse', $primary->ruleId);
        self::assertSame('EADDRINUSE', $report->context->errorCode);
    }

    public function testDiagnosesNodeEnoentAndExtractsCode(): void
    {
        $error = "Error: ENOENT: no such file or directory, open '/app/config.json'";
        $report = $this->advisor->analyze($error, 'javascript', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.enoent_no_such_file_node', $primary->ruleId);
        self::assertSame('ENOENT', $report->context->errorCode);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('File o Directory Non Trovata', $primary->title);
    }

    public function testDiagnosesNodeErrRequireEsmAndExtractsCode(): void
    {
        $error = 'Error [ERR_REQUIRE_ESM]: require() of ES Module /app/node_modules/chalk/source/index.js not supported.';
        $report = $this->advisor->analyze($error, 'js', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.require_of_es_module', $primary->ruleId);
        self::assertSame('ERR_REQUIRE_ESM', $report->context->errorCode);
    }

    public function testDiagnosesJsAppendChildNotANode(): void
    {
        $error = "TypeError: Failed to execute 'appendChild' on 'Node': parameter 1 is not of type 'Node'.";
        $report = $this->advisor->analyze($error, 'javascript', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.append_child_not_a_node', $primary->ruleId);
        self::assertSame('TypeError', $report->context->exceptionClass);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Il Parametro Non è un Nodo DOM', $primary->title);
    }

    public function testDiagnosesReactTooManyReRenders(): void
    {
        $error = 'Error: Too many re-renders. React limits the number of renders to prevent an infinite loop.';
        $report = $this->advisor->analyze($error, 'javascript', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.react_too_many_rerenders', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Troppi Re-Render', $primary->title);
    }

    public function testDiagnosesSsrWindowNotDefined(): void
    {
        $error = "ReferenceError: window is not defined\n    at Counter (/app/src/Counter.jsx:15:3)";
        $report = $this->advisor->analyze($error, 'js', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.ssr_window_or_document_not_defined', $primary->ruleId);
        self::assertSame('ReferenceError', $report->context->exceptionClass);
        self::assertSame('/app/src/Counter.jsx', $report->context->file);
        self::assertSame(15, $report->context->line);
    }

    public function testDiagnosesNodeEaddrinuse(): void
    {
        $error = "Error: listen EADDRINUSE: address already in use :::3000\n    at Server.setupListenHandle [as _listen2] (node:net:1898:21)";
        $report = $this->advisor->analyze($error, 'javascript', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.listen_eaddrinuse', $primary->ruleId);
        self::assertSame('EADDRINUSE', $report->context->errorCode);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Porta di Rete Già in Uso', $primary->title);
    }

    public function testDiagnosesViteFailedToResolveImport(): void
    {
        $error = "[vite] Internal server error: Failed to resolve import \"@components/Button\" from \"src/App.vue\". Does the file exist?";
        $report = $this->advisor->analyze($error, 'javascript', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('js.vite_failed_to_resolve_import', $primary->ruleId);
        self::assertSame('en', $primary->language);
        self::assertStringContainsString('Failed to Resolve Import', $primary->title);
    }

    public function testDiagnosesWordPressErrors(): void
    {
        $error = 'Error establishing a database connection';
        $report = $this->advisor->analyze($error, 'wordpress', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('wp.db_connection_error', $primary->ruleId);
        self::assertStringContainsString('wp-config.php', $primary->explanation);
    }

    public function testDiagnosesWordPressGutenbergInvalidJson(): void
    {
        $error = 'Updating failed. The response is not a valid JSON response.';
        $report = $this->advisor->analyze($error, 'wp', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('wp.gutenberg_invalid_json', $primary->ruleId);
    }

    public function testDiagnosesWordPressLoadTextdomainCalledIncorrectly(): void
    {
        $error = '_load_textdomain_just_in_time was called incorrectly. Translation loading for the my-plugin domain was triggered too early.';
        $report = $this->advisor->analyze($error, 'wordpress', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('wp.load_textdomain_called_incorrectly', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('_load_textdomain_just_in_time', $primary->title);
    }

    public function testDiagnosesSymfonyServiceNotFoundInItalian(): void
    {
        $error = 'You have requested a non-existent service "App\\Service\\PaymentProcessor". Did you mean this: "App\\Service\\StripePaymentProcessor"?';
        $report = $this->advisor->analyze($error, 'symfony', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('symfony.service_not_found', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Richiesto Servizio Inesistente', $primary->title);
        self::assertStringContainsString('App\\Service\\PaymentProcessor', $primary->title);
        self::assertStringContainsString('debug:container', $primary->solutions[0]);
        self::assertStringContainsString('services.yaml', $primary->solutions[2]);
    }

    public function testDiagnosesSymfonyRouteNotFoundInEnglish(): void
    {
        $error = 'No route found for "GET https://example.com/api/v2/orders" (404 Not Found)';
        $report = $this->advisor->analyze($error, 'sf', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('symfony.route_not_found', $primary->ruleId);
        self::assertSame('en', $primary->language);
        self::assertStringContainsString('No Route Found for GET', $primary->title);
        self::assertStringContainsString('debug:router', $primary->solutions[0]);
    }

    public function testDiagnosesSymfonyAutowireInterfaceNotAliased(): void
    {
        $error = 'Cannot autowire service "App\\Service\\OrderManager": argument "$gateway" of method "__construct()" references interface "App\\Contract\\PaymentGatewayInterface" but no such service exists.';
        $report = $this->advisor->analyze($error, 'symfony', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('symfony.autowire_interface_not_aliased', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Impossibile Eseguire l\'Autowiring del Servizio', $primary->title);
    }

    public function testDiagnosesSymfonyDoctrineDbalConnectionException(): void
    {
        $error = 'Doctrine\\DBAL\\Exception\\ConnectionException: An exception occurred in the driver: SQLSTATE[HY000] [2002] Connection refused';
        $report = $this->advisor->analyze($error, 'symfony', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('symfony.doctrine_dbal_connection_exception', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('ConnectionException: Connessione al Driver Fallita', $primary->title);
    }

    public function testDiagnosesSymfonyTwigPropertyNotFound(): void
    {
        $error = 'Neither the property "fullName" nor one of the methods "fullName()", "getFullName()"/"isFullName()"/"hasFullName()" exist and have public access in class "App\\Entity\\User"';
        $report = $this->advisor->analyze($error, 'symfony', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('symfony.twig_property_or_method_not_found', $primary->ruleId);
        self::assertSame('en', $primary->language);
        self::assertStringContainsString('Neither Property Nor Method', $primary->title);
    }

    public function testDiagnosesSymfonyMapRequestPayloadValidation(): void
    {
        $error = 'Validation failed for #[MapRequestPayload] with 2 violation(s)';
        $report = $this->advisor->analyze($error, 'symfony', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('symfony.map_request_payload_validation', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Validazione Fallita su #[MapRequestPayload]', $primary->title);
    }

    public function testDiagnosesMysqlAccessDeniedError(): void
    {
        $error = "SQLSTATE[HY000] [1045] Access denied for user 'awpg_user'@'localhost' (using password: YES)";
        $report = $this->advisor->analyze($error, 'mysql', 'it');

        self::assertTrue($report->hasMatch());
        self::assertSame('mysql.access_denied_1045', $report->primaryMatch()?->ruleId);
        self::assertSame('SQLSTATE[HY000] (1045)', $report->context->errorCode);
    }

    public function testDiagnosesMysqlDuplicateEntryError(): void
    {
        $error = "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'admin@example.com' for key 'users.email_unique'";
        $report = $this->advisor->analyze($error, 'mariadb', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('mysql.duplicate_entry_1062', $primary->ruleId);
        self::assertSame('SQLSTATE[23000] (1062)', $report->context->errorCode);
        self::assertSame('admin@example.com', $report->context->symbol);
    }

    public function testDiagnosesMysqlWithoutSqlstateExtractsMySqlErrorCode(): void
    {
        $error = "ERROR 1062 (23000): Duplicate entry 'john@example.com' for key 'users.email'";
        $report = $this->advisor->analyze($error, 'mysql', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('mysql.duplicate_entry_1062', $primary->ruleId);
        self::assertSame('MYSQL_1062 (23000)', $report->context->errorCode);
        self::assertSame('john@example.com', $report->context->symbol);
    }

    public function testDiagnosesMysqlPublicKeyRetrievalNotAllowed(): void
    {
        $error = 'java.sql.SQLNonTransientConnectionException: Public Key Retrieval is not allowed';
        $report = $this->advisor->analyze($error, 'mysql', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('mysql.public_key_retrieval_not_allowed', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Recupero della Chiave Pubblica Non Consentito', $primary->title);
    }

    public function testDiagnosesMysqlCannotAddForeignKeyConstraint1215(): void
    {
        $error = 'ERROR 1215 (HY000): Cannot add foreign key constraint (errno: 150 "Foreign key constraint is incorrectly formed")';
        $report = $this->advisor->analyze($error, 'mysql', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('mysql.cannot_add_foreign_key_1215', $primary->ruleId);
        self::assertSame('MYSQL_1215 (HY000)', $report->context->errorCode);
    }

    public function testDiagnosesMysqlServerHasGoneAwayErrorCode(): void
    {
        $error = 'Warning: PDOStatement::execute(): MySQL server has gone away in /var/www/html/app.php on line 88';
        $report = $this->advisor->analyze($error, 'mysql', 'en');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('mysql.server_gone_away_2006', $primary->ruleId);
        self::assertSame('MYSQL_2006', $report->context->errorCode);
        self::assertSame('/var/www/html/app.php', $report->context->file);
        self::assertSame(88, $report->context->line);
    }

    public function testDiagnosesMysqlWsrepDeadlockConflict(): void
    {
        $error = 'ERROR 1213 (40001): WSREP detected deadlock/conflict and aborted the transaction';
        $report = $this->advisor->analyze($error, 'mariadb', 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('mysql.wsrep_deadlock_conflict_1213', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertStringContainsString('Deadlock / Conflitto di Certificazione Cluster WSREP', $primary->title);
    }

    public function testDiagnosesCurlSslError(): void
    {
        $error = 'cURL error 60: SSL certificate problem: unable to get local issuer certificate';
        $report = $this->advisor->analyze($error, 'http', 'it');

        self::assertTrue($report->hasMatch());
        self::assertSame('http.curl_ssl_cert', $report->primaryMatch()?->ruleId);
        self::assertSame('CURL_60', $report->context->errorCode);
    }

    public function testCategoryFilterExcludesUnmatchedRules(): void
    {
        $phpError = 'PHP Fatal error: Allowed memory size of 134217728 bytes exhausted in /app/run.php:10';

        // Filtering by 'python' should return 0 matches for a PHP memory error
        $pythonReport = $this->advisor->analyze($phpError, 'python');
        self::assertFalse($pythonReport->hasMatch());

        // Filtering by 'symfony' should return 0 matches for a PHP memory error
        $symfonyMismatch = $this->advisor->analyze($phpError, 'symfony');
        self::assertFalse($symfonyMismatch->hasMatch());

        // Filtering by 'symfony' should match a Symfony service error
        $sfError = 'You have requested a non-existent service "mailer"';
        $sfReport = $this->advisor->analyze($sfError, 'symfony');
        self::assertTrue($sfReport->hasMatch());
        self::assertSame('symfony.service_not_found', $sfReport->primaryMatch()?->ruleId);

        // Filtering by 'php' should match
        $phpReport = $this->advisor->analyze($phpError, 'php');
        self::assertTrue($phpReport->hasMatch());
        self::assertSame('php.memory_exhausted', $phpReport->primaryMatch()?->ruleId);
    }

    public function testCustomRuleWithoutItalianFieldsFallsBackToEnglish(): void
    {
        $this->advisor->addRule(new ErrorRule(
            id: 'custom.token_error',
            category: 'Custom API',
            title: 'Invalid Auth Token: {match}',
            pattern: '/AUTH_TOKEN_INVALID:\s*([a-zA-Z0-9_-]+)/i',
            explanation: 'The provided authentication bearer token is invalid or expired.',
            solutions: ['Refresh the token or obtain a new session.'],
            confidence: 1.0,
            // titleIt, explanationIt, solutionsIt omitted
        ));

        // Requesting Italian should safely fall back to English
        $report = $this->advisor->analyze('AUTH_TOKEN_INVALID: abc123xyz', null, 'it');
        $primary = $report->primaryMatch();

        self::assertTrue($report->hasMatch());
        self::assertNotNull($primary);
        self::assertSame('custom.token_error', $primary->ruleId);
        self::assertSame('it', $primary->language);
        self::assertSame('Invalid Auth Token: abc123xyz', $primary->title);
        self::assertSame('The provided authentication bearer token is invalid or expired.', $primary->explanation);
        self::assertSame(['Refresh the token or obtain a new session.'], $primary->solutions);
    }

    public function testThrowsExceptionOnEmptyInput(): void
    {
        $this->expectException(ErrorAdvisorException::class);
        $this->advisor->analyze('   ');
    }
}
