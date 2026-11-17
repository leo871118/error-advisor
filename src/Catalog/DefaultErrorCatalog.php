<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Catalog;

use LeandroPerez\ErrorAdvisor\Dto\ErrorRule;

/**
 * Built-in aggregated catalog combining PHP, Symfony, Python, JavaScript, WordPress, MySQL,
 * HTTP/cURL, Composer, and Git diagnostic rules.
 */
final class DefaultErrorCatalog
{
    /**
     * @return list<ErrorRule>
     */
    public static function all(): array
    {
        return [
            ...PhpErrorCatalog::all(),
            ...SymfonyErrorCatalog::all(),
            ...PythonErrorCatalog::all(),
            ...JavaScriptErrorCatalog::all(),
            ...WordPressErrorCatalog::all(),
            ...MySqlErrorCatalog::all(),
            ...self::httpRules(),
            ...self::composerAndGitRules(),
        ];
    }

    /**
     * @return list<ErrorRule>
     */
    public static function httpRules(): array
    {
        return [
            new ErrorRule(
                id: 'http.curl_ssl_cert',
                category: 'HTTP & SSL',
                title: 'cURL SSL Certificate Verification Failed (Error 60)',
                pattern: '/cURL error 60:|SSL certificate problem:/i',
                explanation: 'cURL could not verify the remote TLS certificate against a trusted CA bundle (frequent on Windows local development environments).',
                solutions: [
                    'Download the latest `cacert.pem` from curl.se and configure `curl.cainfo` and `openssl.cafile` in `php.ini`.',
                    'Verify the target server certificate is not expired or missing an intermediate chain.',
                ],
                codeExample: "; In php.ini:\ncurl.cainfo = \"C:\\xampp\\php\\extras\\ssl\\cacert.pem\"\nopenssl.cafile = \"C:\\xampp\\php\\extras\\ssl\\cacert.pem\"",
                tags: ['curl', 'ssl', 'tls', 'windows', 'http'],
                confidence: 0.99,
                titleIt: 'Verifica Certificato SSL cURL Fallita (Errore 60)',
                explanationIt: 'cURL non riesce a validare il certificato TLS remoto rispetto al bundle di CA fidate (frequente negli ambienti di sviluppo locali Windows).',
                solutionsIt: [
                    'Scarica il file `cacert.pem` aggiornato da curl.se e configuralo in `curl.cainfo` e `openssl.cafile` in `php.ini`.',
                    'Verifica che il certificato dell\'host remoto non sia scaduto o privo della catena di certificati intermedi.',
                ],
            ),
            new ErrorRule(
                id: 'http.curl_timeout',
                category: 'HTTP & Network',
                title: 'cURL Operation Timed Out (Error 28)',
                pattern: '/cURL error 28:|Operation timed out after \d+ milliseconds/i',
                explanation: 'The remote endpoint did not respond within the configured connection or transfer timeout window.',
                solutions: [
                    'Force IPv4 (`CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4`) if broken IPv6 routing stalls connections.',
                    'Check firewall and outbound network routing towards the target host and port.',
                    'Increase `CURLOPT_TIMEOUT` for slow third-party APIs and implement retries with backoff.',
                ],
                codeExample: "curl_setopt(\$ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);\ncurl_setopt(\$ch, CURLOPT_CONNECTTIMEOUT, 10);\ncurl_setopt(\$ch, CURLOPT_TIMEOUT, 30);",
                tags: ['curl', 'timeout', 'network', 'http'],
                confidence: 0.98,
                titleIt: 'Timeout Operazione cURL Superato (Errore 28)',
                explanationIt: 'L\'endpoint remoto non ha risposto entro la finestra temporale di timeout di connessione o trasferimento configurata.',
                solutionsIt: [
                    'Forza l\'uso di IPv4 (`CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4`) per evitare rallentamenti su reti con IPv6 non instradato.',
                    'Verifica firewall e connettività in uscita verso l\'host e la porta di destinazione.',
                    'Aumenta `CURLOPT_TIMEOUT` per API lente e predisponi un meccanismo di retry con backoff.',
                ],
            ),
            new ErrorRule(
                id: 'http.curl_resolve_host',
                category: 'HTTP & DNS',
                title: 'Could Not Resolve Host "{match}" (cURL Error 6)',
                pattern: '/cURL error 6:|Could not resolve host:\s*([^\s]+)/i',
                explanation: 'DNS resolution failed for hostname `{match}`.',
                solutions: [
                    'Check the target URL for typographical errors, leading spaces, or invalid scheme.',
                    'Verify DNS resolution from the server terminal (`nslookup {match}` or `dig {match}`).',
                ],
                tags: ['curl', 'dns', 'network', 'http'],
                confidence: 0.98,
                titleIt: 'Impossibile Risolvere l\'Host "{match}" (cURL Errore 6)',
                explanationIt: 'La risoluzione DNS per il nome di dominio `{match}` è fallita.',
                solutionsIt: [
                    'Controlla l\'URL dell\'endpoint per individuare refusi, spazi vuoti o protocollo mancante.',
                    'Verifica la risoluzione DNS dalla riga di comando del server (`nslookup {match}` o `dig {match}`).',
                ],
            ),
            new ErrorRule(
                id: 'http.cors_blocked',
                category: 'HTTP & Browser Security',
                title: 'Browser Blocked Request Due to Missing CORS Headers',
                pattern: '/blocked by CORS policy|No \'Access-Control-Allow-Origin\' header/i',
                explanation: 'The browser blocked a cross-origin HTTP request because the API response lacked valid `Access-Control-Allow-*` headers or failed preflight.',
                solutions: [
                    'Configure your backend or reverse proxy to return `Access-Control-Allow-Origin`, `Access-Control-Allow-Methods`, and `Access-Control-Allow-Headers`.',
                    'Ensure `OPTIONS` preflight requests return HTTP 200 or 204 without authentication redirects.',
                ],
                tags: ['http', 'cors', 'security', 'api'],
                confidence: 0.98,
                titleIt: 'Richiesta Bloccata dal Browser per Policy CORS Mancanti',
                explanationIt: 'Il browser ha bloccato la richiesta cross-origin poiché la risposta dell\'API non contiene gli header `Access-Control-Allow-*` richiesti.',
                solutionsIt: [
                    'Configura il backend o reverse proxy affinché restituisca gli header `Access-Control-Allow-Origin`, `Access-Control-Allow-Methods` e `Access-Control-Allow-Headers`.',
                    'Assicurati che le richieste preflight `OPTIONS` rispondano con HTTP 200 o 204 senza redirect di autenticazione.',
                ],
            ),
            new ErrorRule(
                id: 'http.csrf_419',
                category: 'HTTP & Security',
                title: 'CSRF Token Mismatch / Session Expired (419)',
                pattern: '/\b419\b.*(?:Page Expired|CSRF)|CSRF token mismatch/i',
                explanation: 'The submitted form or AJAX request lacked a valid CSRF token, or the user session expired.',
                solutions: [
                    'Include the CSRF token in a hidden form input or in the `X-CSRF-TOKEN` request header.',
                    'Verify `SESSION_DOMAIN`, `SameSite`, and `Secure` cookie settings across HTTP and HTTPS environments.',
                ],
                tags: ['http', 'csrf', 'session', 'security'],
                confidence: 0.96,
                titleIt: 'Token CSRF Non Valido o Sessione Scaduta (HTTP 419)',
                explanationIt: 'Il form o la richiesta AJAX sottomessa è priva di token CSRF valido, oppure la sessione utente è scaduta.',
                solutionsIt: [
                    'Includi il token CSRF nel campo nascosto del modulo o nell\'header di richiesta `X-CSRF-TOKEN`.',
                    'Verifica la configurazione dei cookie di sessione (`SameSite`, `Secure`, `SESSION_DOMAIN`).',
                ],
            ),
            new ErrorRule(
                id: 'http.rate_limit_429',
                category: 'HTTP & APIs',
                title: 'API Rate Limit / Quota Exceeded (429)',
                pattern: '/\b429\b.*Too Many Requests|rate limit exceeded|RESOURCE_EXHAUSTED/i',
                explanation: 'Too many requests were sent to the target API within its allowed rate limit window.',
                solutions: [
                    'Respect the `Retry-After` response header and implement exponential backoff.',
                    'Cache API responses locally using Redis or filesystem cache to reduce redundant traffic.',
                ],
                tags: ['http', 'api', 'rate-limit'],
                confidence: 0.97,
                titleIt: 'Limite di Chiamate API Superato (HTTP 429 Too Many Requests)',
                explanationIt: 'È stato superato il numero massimo di richieste consentite dall\'API nella finestra temporale corrente.',
                solutionsIt: [
                    'Rispetta il valore dell\'header `Retry-After` e applica un algoritmo di retry con backoff esponenziale.',
                    'Memorizza in cache le risposte identiche per ridurre le chiamate ridondanti verso l\'API.',
                ],
            ),
            new ErrorRule(
                id: 'http.bad_gateway_502_504',
                category: 'HTTP & Infrastructure',
                title: 'Upstream Gateway Error / Timeout (502 / 504)',
                pattern: '/\b(?:502 Bad Gateway|504 Gateway Time-?out)\b/i',
                explanation: 'The reverse proxy (Nginx, Apache, Cloudflare) received an invalid response or timed out waiting for the upstream PHP-FPM / application backend.',
                solutions: [
                    'Check PHP-FPM logs for crashes, segmentation faults, or pool worker starvation (`pm.max_children`).',
                    'Align `fastcgi_read_timeout` or `proxy_read_timeout` with PHP\'s `max_execution_time`.',
                ],
                tags: ['http', 'nginx', 'php-fpm', 'gateway'],
                confidence: 0.96,
                titleIt: 'Errore Gateway o Timeout Upstream (HTTP 502 / 504)',
                explanationIt: 'Il reverse proxy (Nginx, Apache, Cloudflare) ha ricevuto una risposta non valida o è andato in timeout in attesa del backend PHP-FPM o applicativo.',
                solutionsIt: [
                    'Esamina i log di PHP-FPM per individuare crash dei worker o esaurimento del pool (`pm.max_children`).',
                    'Allinea i timeout del proxy (`fastcgi_read_timeout`, `proxy_read_timeout`) con `max_execution_time` di PHP.',
                ],
            ),
        ];
    }

    /**
     * @return list<ErrorRule>
     */
    public static function composerAndGitRules(): array
    {
        return [
            new ErrorRule(
                id: 'composer.dependency_conflict',
                category: 'Composer',
                title: 'Composer Dependency Resolution Conflict',
                pattern: '/Your requirements could not be resolved to an installable set of packages/i',
                explanation: 'Package requirements conflict with the current PHP version, missing PHP extensions, or incompatible version constraints in `composer.json`.',
                solutions: [
                    'Inspect the exact error message to check which package requirement failed (e.g. required PHP version or extension).',
                    'Run `composer why-not vendor/package ^x.y` to pinpoint blocking constraints.',
                    'Update constraints in `composer.json` or upgrade conflicting dependencies.',
                ],
                codeExample: "composer why-not vendor/package ^2.0",
                tags: ['composer', 'dependencies', 'packaging'],
                confidence: 0.98,
                titleIt: 'Conflitto di Risoluzione Dipendenze Composer',
                explanationIt: 'I vincoli dei pacchetti richiesti non sono compatibili con la versione di PHP corrente, estensioni mancanti o altre librerie in `composer.json`.',
                solutionsIt: [
                    'Verifica nel report di Composer quale vincolo non viene soddisfatto (es. versione PHP o estensione).',
                    'Esegui `composer why-not fornitore/pacchetto ^x.y` per identificare la libreria bloccante.',
                    'Aggiorna i vincoli di versione in `composer.json` o aggiorna i pacchetti incompatibili.',
                ],
            ),
            new ErrorRule(
                id: 'git.ssh_permission_denied',
                category: 'Git',
                title: 'Git Remote SSH Authentication Failed (Permission Denied)',
                pattern: '/Permission denied \(publickey\)|Could not read from remote repository/i',
                explanation: 'Git failed to authenticate with the remote Git server using your local SSH keys.',
                solutions: [
                    'Verify the SSH agent is running (`ssh-add -l`) and add your private key (`ssh-add ~/.ssh/id_ed25519`).',
                    'Test connectivity with `ssh -T git@github.com` and ensure your public key is added to your Git account.',
                ],
                codeExample: "ssh -T git@github.com",
                tags: ['git', 'ssh', 'authentication'],
                confidence: 0.98,
                titleIt: 'Autenticazione SSH Git Remoto Fallita (Permesso Negato)',
                explanationIt: 'Git non riesce ad autenticarsi sul server remoto utilizzando la chiave SSH locale configurata.',
                solutionsIt: [
                    'Verifica che l\'agente SSH sia attivo (`ssh-add -l`) e aggiungi la chiave privata (`ssh-add ~/.ssh/id_ed25519`).',
                    'Verifica la connessione con `ssh -T git@github.com` e controlla che la chiave pubblica sia registrata nel tuo account Git.',
                ],
            ),
        ];
    }
}
