<?php
declare(strict_types=1);
/**
 * Tests de sécurité — Conesa Rénovation
 *
 * Usage :
 *   php tests/security/run.php
 *   php tests/security/run.php --url=http://conesa.local
 *   php tests/security/run.php --url=https://conesa.roofline.fr --auth=conesa:motdepasse
 *   php tests/security/run.php --help
 *
 * Exigences : PHP 8.0+, extension cURL activée.
 * Ces tests n'envoient aucune donnée réelle et ne sont pas destructifs.
 */

// ── Aide ──────────────────────────────────────────────────────────────────────
if (in_array('--help', $argv ?? [], true) || in_array('-h', $argv ?? [], true)) {
    echo <<<HELP
Tests de sécurité — Conesa Rénovation

Usage :
  php tests/security/run.php [options]

Options :
  --url=URL         URL de base du site  (défaut : http://conesa.local)
  --auth=USER:PASS  Identifiants Basic Auth pour la préprod
  --no-color        Désactiver les couleurs ANSI
  --help            Afficher cette aide

Exemples :
  php tests/security/run.php
  php tests/security/run.php --url=http://conesa.local
  php tests/security/run.php --url=https://conesa.roofline.fr --auth=conesa:motdepasse

Aucune donnée réelle n'est envoyée. Les tests ne sont pas destructifs.
HELP;
    exit(0);
}

// ── Pré-requis ────────────────────────────────────────────────────────────────
if (!function_exists('curl_init')) {
    fwrite(STDERR, "Erreur : l'extension PHP cURL est requise (activez php_curl dans php.ini).\n");
    exit(2);
}

// ── Configuration ─────────────────────────────────────────────────────────────
$opts     = getopt('h', ['url:', 'auth:', 'no-color', 'help']);
$BASE_URL = rtrim($opts['url'] ?? 'http://conesa.local', '/');
$AUTH     = $opts['auth'] ?? '';
$NO_COLOR = isset($opts['no-color']) || getenv('NO_COLOR') !== false;

// Couleurs ANSI
$C = $NO_COLOR ? [] : [
    'green'  => "\033[32m",
    'red'    => "\033[31m",
    'yellow' => "\033[33m",
    'reset'  => "\033[0m",
    'bold'   => "\033[1m",
    'dim'    => "\033[2m",
];
$C += ['green' => '', 'red' => '', 'yellow' => '', 'reset' => '', 'bold' => '', 'dim' => ''];

// ── Données de test (jamais de données réelles client) ────────────────────────
const TEST_NAME  = 'Test Sécurité';
const TEST_PHONE = '06 00 00 00 00';
const TEST_EMAIL = 'test-securite@example.com';
const TEST_VILLE = 'Albi';
const TEST_SERV  = 'Isolation Thermique Extérieure (ITE)';
const TEST_MSG   = 'Ceci est un test de sécurité automatisé — ne pas traiter.';

// ── Compteurs ─────────────────────────────────────────────────────────────────
$passed  = 0;
$failed  = 0;
$results = [];

// ── Fonctions utilitaires ─────────────────────────────────────────────────────
function pass(string $name): void {
    global $passed, $results;
    $passed++;
    $results[] = ['status' => 'PASS', 'name' => $name, 'detail' => ''];
}

function fail(string $name, string $detail = ''): void {
    global $failed, $results;
    $failed++;
    $results[] = ['status' => 'FAIL', 'name' => $name, 'detail' => $detail];
}

function csrfToken(): string {
    return bin2hex(random_bytes(24)); // 48 chars hex, identique à la génération JS
}

function validData(): array {
    return [
        'nom'        => TEST_NAME,
        'telephone'  => TEST_PHONE,
        'email'      => TEST_EMAIL,
        'ville'      => TEST_VILLE,
        'prestation' => TEST_SERV,
        'message'    => TEST_MSG,
        'page_source'=> '/test-securite',
    ];
}

/**
 * Exécute une requête HTTP via cURL.
 * Retourne code HTTP, headers parsés, cookies Set-Cookie, body, JSON décodé.
 */
function httpRequest(string $url, array $post = [], array $cookies = [], string $auth = '', string $method = 'GET'): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false, // Acceptable pour les tests (staging auto-signé possible)
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Conesa-SecurityTest/1.0',
    ]);

    if ($method === 'POST' || !empty($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    if (!empty($cookies)) {
        $cookieStr = implode('; ', array_map(
            fn($k, $v) => "$k=$v",
            array_keys($cookies),
            array_values($cookies)
        ));
        curl_setopt($ch, CURLOPT_COOKIE, $cookieStr);
    }

    if (!empty($auth)) {
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
    }

    $raw        = (string) curl_exec($ch);
    $code       = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $rawHeaders = substr($raw, 0, $headerSize);
    $body       = substr($raw, $headerSize);

    // Parse des headers (last value wins pour les doublons)
    $headers = [];
    foreach (explode("\r\n", $rawHeaders) as $line) {
        if (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $headers[strtolower(trim($k))] = trim($v);
        }
    }

    // Parse des Set-Cookie (nom=valeur uniquement, sans attributs)
    $setCookies = [];
    preg_match_all('/^Set-Cookie:\s*([^;\r\n]+)/mi', $rawHeaders, $m);
    foreach ($m[1] as $c) {
        [$n, $v] = explode('=', $c, 2) + ['', ''];
        $setCookies[trim($n)] = trim($v);
    }

    return [
        'code'    => $code,
        'headers' => $headers,
        'cookies' => $setCookies,
        'body'    => $body,
        'json'    => json_decode($body, true),
    ];
}

/**
 * Envoie un POST à contact.php avec un token CSRF valide (cookie + champ POST correspondants).
 * $extra écrase les champs de validData().
 * $sessionCookies permet de passer un PHPSESSID pour maintenir la session (rate limiting).
 */
function postWithCsrf(string $url, array $extra = [], string $auth = '', array $sessionCookies = []): array {
    $token   = csrfToken();
    $cookies = array_merge(['cf_token' => $token], $sessionCookies);
    $data    = array_merge(validData(), ['csrf_token' => $token], $extra);
    return httpRequest($url, $data, $cookies, $auth, 'POST');
}

// ─────────────────────────────────────────────────────────────────────────────
// En-tête du rapport
// ─────────────────────────────────────────────────────────────────────────────
$sep = str_repeat('═', 62);
echo "\n{$C['bold']}{$sep}{$C['reset']}\n";
echo "{$C['bold']}  Tests de sécurité — Conesa Rénovation{$C['reset']}\n";
echo "  Cible : {$BASE_URL}\n";
if (!empty($AUTH)) {
    [$authUser] = explode(':', $AUTH, 2);
    echo "  Auth  : {$authUser}:***\n";
}
echo "{$C['bold']}{$sep}{$C['reset']}\n";

$CONTACT = "$BASE_URL/contact.php";

// ═════════════════════════════════════════════════════════════════════════════
// [1] CONTRÔLE D'ACCÈS AUX FICHIERS SENSIBLES
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [1] Contrôle d'accès ──────────────────────────────────{$C['reset']}\n";

// 1a. .env
$r = httpRequest("$BASE_URL/.env", [], [], $AUTH);
if (in_array($r['code'], [403, 404], true)) {
    pass(".env inaccessible (HTTP {$r['code']})");
} else {
    fail('.env accessible !', "HTTP {$r['code']} — fichier .env exposé sur le web (credentials SMTP/DB lisibles)");
}

// 1b. storage/leads.sqlite
$r = httpRequest("$BASE_URL/storage/leads.sqlite", [], [], $AUTH);
if (in_array($r['code'], [403, 404], true)) {
    pass("storage/leads.sqlite inaccessible (HTTP {$r['code']})");
} else {
    fail('storage/leads.sqlite accessible !', "HTTP {$r['code']} — base SQLite exposée (données personnelles lisibles)");
}

// ═════════════════════════════════════════════════════════════════════════════
// [2] EN-TÊTES DE SÉCURITÉ HTTP
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [2] En-têtes de sécurité ──────────────────────────────{$C['reset']}\n";

$r = httpRequest("$BASE_URL/", [], [], $AUTH);

// Headers obligatoires avec valeur exacte attendue
foreach ([
    'x-content-type-options' => 'nosniff',
    'x-frame-options'        => 'DENY',
    'referrer-policy'        => 'strict-origin-when-cross-origin',
] as $header => $expected) {
    $actual = $r['headers'][$header] ?? null;
    if ($actual === null) {
        fail("Header $header présent", 'Header absent — vérifier mod_headers et le .htaccess déployé');
    } elseif (strcasecmp(trim($actual), $expected) !== 0) {
        fail("Header $header correct", "Attendu : «$expected» — Obtenu : «$actual»");
    } else {
        pass("Header $header : $expected");
    }
}

// Headers dont on vérifie seulement la présence
foreach (['permissions-policy', 'content-security-policy'] as $header) {
    if (isset($r['headers'][$header])) {
        pass("Header $header présent");
    } else {
        fail("Header $header présent", 'Header absent — vérifier mod_headers et le .htaccess déployé');
    }
}

// Directives CSP critiques
$csp = $r['headers']['content-security-policy'] ?? '';
foreach ([
    "frame-ancestors 'none'" => 'Protection clickjacking (iframe)',
    "form-action 'self'"     => 'Soumission formulaire restreinte',
    "connect-src 'self'"     => 'Fetch/AJAX restreint au même domaine',
] as $directive => $role) {
    if (str_contains($csp, $directive)) {
        pass("CSP : $directive");
    } else {
        fail("CSP : $directive", "$role — directive absente de la Content-Security-Policy");
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// [3] PROTECTION CSRF
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [3] Protection CSRF ───────────────────────────────────{$C['reset']}\n";

// 3a. Aucun token CSRF (ni cookie, ni champ POST)
$r = httpRequest($CONTACT, array_merge(validData(), ['csrf_token' => '']), [], $AUTH, 'POST');
if ($r['code'] === 403) {
    pass('CSRF absent → 403');
} else {
    fail('CSRF absent → 403', "Obtenu : HTTP {$r['code']} — formulaire accessible sans token CSRF");
}

// 3b. Cookie présent mais token POST différent
$token = csrfToken();
$r = httpRequest(
    $CONTACT,
    array_merge(validData(), ['csrf_token' => 'token-falsifie-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx']),
    ['cf_token' => $token],
    $AUTH,
    'POST'
);
if ($r['code'] === 403) {
    pass('CSRF invalide (cookie ≠ POST) → 403');
} else {
    fail('CSRF invalide (cookie ≠ POST) → 403', "Obtenu : HTTP {$r['code']} — protection CSRF contournable");
}

// 3c. Token POST présent mais aucun cookie
$token = csrfToken();
$r = httpRequest(
    $CONTACT,
    array_merge(validData(), ['csrf_token' => $token]),
    [],
    $AUTH,
    'POST'
);
if ($r['code'] === 403) {
    pass('CSRF sans cookie → 403');
} else {
    fail('CSRF sans cookie → 403', "Obtenu : HTTP {$r['code']} — Double Submit Cookie non vérifié");
}

// ═════════════════════════════════════════════════════════════════════════════
// [4] HONEYPOT ANTI-SPAM
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [4] Honeypot anti-spam ────────────────────────────────{$C['reset']}\n";

// Champ honeypot rempli → réponse silencieuse success:true sans traitement
$r = postWithCsrf($CONTACT, ['website' => 'http://spam.example.com'], $AUTH);
if ($r['code'] === 200 && ($r['json']['success'] ?? false) === true) {
    pass('Honeypot rempli → réponse silencieuse (success:true)');
} else {
    fail('Honeypot rempli → réponse silencieuse', "HTTP {$r['code']} — corps : " . substr($r['body'], 0, 100));
}

// ═════════════════════════════════════════════════════════════════════════════
// [5] VALIDATION DES CHAMPS
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [5] Validation des champs ─────────────────────────────{$C['reset']}\n";

$validationCases = [
    // [champ envoyé, valeur, clé erreur attendue, libellé]
    ['email',      'pas-un-email',         'email',      'Email invalide (format incorrect)'],
    ['email',      'test@',                'email',      'Email invalide (domaine manquant)'],
    ['telephone',  '12345',                'telephone',  'Téléphone invalide (trop court)'],
    ['telephone',  'abcdefghij',           'telephone',  'Téléphone invalide (non numérique)'],
    ['prestation', 'Prestation Inconnue',  'prestation', 'Prestation hors whitelist'],
    ['nom',        'X',                    'nom',        'Nom invalide (1 caractère)'],
    ['nom',        '',                     'nom',        'Nom vide'],
];

foreach ($validationCases as [$field, $value, $errKey, $label]) {
    $r = postWithCsrf($CONTACT, [$field => $value], $AUTH);
    if ($r['code'] === 200 && isset($r['json']['errors'][$errKey])) {
        pass("$label → erreur validation");
    } elseif ($r['code'] === 200 && isset($r['json']['errors'])) {
        pass("$label → erreur (autre champ)");
    } else {
        fail("$label → erreur validation", "HTTP {$r['code']} — " . substr($r['body'], 0, 120));
    }
}

// Message trop long — téléphone invalide pour bloquer avant envoi mail
$r = postWithCsrf($CONTACT, ['message' => str_repeat('A', 3000), 'telephone' => 'invalide'], $AUTH);
if ($r['code'] === 500 || $r['code'] === 0) {
    fail('Message 3000 chars → pas de crash', "HTTP {$r['code']} — crash serveur");
} elseif (($r['json']['success'] ?? false) === true) {
    fail('Message 3000 chars → aucun mail envoyé', 'success:true reçu — mail envoyé pendant le test');
} else {
    pass("Message 3000 chars → pas de crash, aucun mail (HTTP {$r['code']})");
}

// ═════════════════════════════════════════════════════════════════════════════
// [6] INJECTIONS ET XSS
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [6] Injections et XSS ─────────────────────────────────{$C['reset']}\n";

// XSS dans nom — strip_tags retire <script>, reste "alert()" → fail regex nom
$r = postWithCsrf($CONTACT, ['nom' => '<script>alert("xss")</script>'], $AUTH);
if ($r['code'] === 200 && isset($r['json']['errors']['nom'])) {
    pass('XSS nom <script> → bloqué par validation regex');
} elseif ($r['code'] === 200 && !str_contains($r['body'], '<script>')) {
    pass('XSS nom <script> → tag non réfléchi dans la réponse');
} elseif ($r['code'] === 403) {
    pass('XSS nom <script> → bloqué en amont (403)');
} elseif (($r['json']['success'] ?? false) === true) {
    fail('XSS nom <script> → bloqué', 'success:true reçu — mail envoyé pendant le test');
} else {
    fail('XSS nom <script> → tag dans la réponse !', substr($r['body'], 0, 150));
}

// XSS attribut dans le message — téléphone invalide pour bloquer avant envoi mail
$r = postWithCsrf($CONTACT, ['message' => '<img src=x onerror=alert(1)>test XSS', 'telephone' => 'invalide'], $AUTH);
if ($r['code'] === 500) {
    fail('XSS message <img onerror> → pas de crash', "HTTP 500");
} elseif (($r['json']['success'] ?? false) === true) {
    fail('XSS message <img onerror> → aucun mail envoyé', 'success:true reçu — mail envoyé pendant le test');
} elseif (str_contains($r['body'], '<img') || str_contains($r['body'], 'onerror')) {
    fail('XSS message <img onerror> → tag HTML dans la réponse !', substr($r['body'], 0, 150));
} else {
    pass('XSS message <img onerror> → tag HTML non réfléchi, aucun mail');
}

// Injection SQL dans le message — téléphone invalide pour bloquer avant envoi mail
$sqlPayload = "'; DROP TABLE leads; -- \" OR '1'='1";
$r = postWithCsrf($CONTACT, ['message' => $sqlPayload, 'telephone' => 'invalide'], $AUTH);
if ($r['code'] === 500) {
    fail('Injection SQL message → pas de crash (500)', 'HTTP 500 — vérifier les requêtes PDO');
} elseif (($r['json']['success'] ?? false) === true) {
    fail('Injection SQL message → aucun mail envoyé', 'success:true reçu — mail envoyé pendant le test');
} else {
    pass("Injection SQL message → pas de crash, aucun mail (HTTP {$r['code']})");
}

// Injection SQL dans le nom — doit échouer la validation (regex stricte ^[\p{L}...])
$r = postWithCsrf($CONTACT, ['nom' => "'; DROP TABLE leads; --"], $AUTH);
if (($r['json']['success'] ?? false) === true) {
    fail('Injection SQL nom → bloqué', 'success:true reçu — mail envoyé pendant le test');
} elseif ($r['code'] === 200 && isset($r['json']['errors']['nom'])) {
    pass('Injection SQL nom → bloqué par validation (regex)');
} elseif ($r['code'] !== 500) {
    pass("Injection SQL nom → pas de crash (HTTP {$r['code']})");
} else {
    fail('Injection SQL nom → crash 500', 'HTTP 500');
}

// ═════════════════════════════════════════════════════════════════════════════
// [7] RATE LIMITING (max 5 tentatives/heure par session PHP)
// ═════════════════════════════════════════════════════════════════════════════
echo "\n{$C['bold']}── [7] Rate limiting (5 tentatives/h) ───────────────────{$C['reset']}\n";

// Envoyer 5 requêtes CSRF valides mais email invalide
// → chaque requête passe CSRF + honeypot, est comptabilisée, échoue à la validation
// → aucun mail envoyé, aucune donnée réelle stockée
$sessionId  = '';
$earlyBlock = false;

echo "  Envoi de 5 requêtes test pour saturer la fenêtre…\n";

for ($i = 1; $i <= 5; $i++) {
    $sessionCookies = $sessionId !== '' ? ['PHPSESSID' => $sessionId] : [];
    $r = postWithCsrf($CONTACT, ['email' => 'invalide-securite-test'], $AUTH, $sessionCookies);

    // Capturer le PHPSESSID pour maintenir la même session entre requêtes
    if (isset($r['cookies']['PHPSESSID'])) {
        $sessionId = $r['cookies']['PHPSESSID'];
    }

    if ($r['code'] === 429) {
        fail("Rate limit — requête {$i}/5 bloquée prématurément", '429 déclenché avant la 6ème requête');
        $earlyBlock = true;
        break;
    }
}

if (!$earlyBlock) {
    $sessionCookies = $sessionId !== '' ? ['PHPSESSID' => $sessionId] : [];
    $r = postWithCsrf($CONTACT, ['email' => 'invalide-securite-test'], $AUTH, $sessionCookies);

    if ($r['code'] === 429) {
        pass('Rate limiting → HTTP 429 après 5 tentatives');
    } else {
        fail(
            'Rate limiting → HTTP 429 après 5 tentatives',
            "Obtenu : HTTP {$r['code']} — rate limit inactif ou session non maintenue entre requêtes"
        );
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// RAPPORT FINAL
// ═════════════════════════════════════════════════════════════════════════════
$total = $passed + $failed;
echo "\n{$C['bold']}{$sep}{$C['reset']}\n";
echo "{$C['bold']}  Rapport final{$C['reset']}\n";
echo "{$C['bold']}{$sep}{$C['reset']}\n\n";

foreach ($results as $item) {
    $isPass  = $item['status'] === 'PASS';
    $icon    = $isPass ? '✓' : '✗';
    $color   = $isPass ? $C['green'] : $C['red'];
    printf("  %s%s{$C['reset']} %-56s", $color, $icon, $item['name']);
    if (!empty($item['detail'])) {
        echo "{$C['dim']}{$item['detail']}{$C['reset']}";
    }
    echo "\n";
}

echo "\n";
if ($failed === 0) {
    echo "  {$C['green']}{$C['bold']}Tous les tests réussis — {$passed}/{$total}{$C['reset']}\n\n";
} else {
    $score = $total > 0 ? round(($passed / $total) * 100) : 0;
    echo "  {$passed}/{$total} tests réussis ({$score}%) — {$C['red']}{$C['bold']}{$failed} échec(s){$C['reset']}\n\n";
}

exit($failed > 0 ? 1 : 0);
