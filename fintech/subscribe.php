<?php
/**
 * Invisible Scribe -- EEC opt-in handler.
 * Receives first_name + email from the landing form, then via the Kit v4 API:
 *   1. creates/upserts the subscriber
 *   2. adds them to the EEC sequence (The Fintech Founder's Authority Playbook)
 *   3. tags them fintech-eec
 * Returns JSON {ok:true} on success, {ok:false,error:"..."} otherwise.
 *
 * The Kit API key is NEVER stored in this file or in git. It is read from
 * config.php (gitignored) or the KIT_API_KEY environment variable set in
 * the Hostinger panel. See config.sample.php.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ---- only accept POST ----
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

// ---- Kit targets (verified live 2026-06-16) ----
const KIT_SEQUENCE_ID = 2796374;        // "The Fintech Founder's Authority Playbook (EEC)"
const KIT_TAG_ID      = 20415268;       // fintech-eec
const KIT_BASE        = 'https://api.kit.com/v4';

// ---- resolve API key: config.php first, then environment ----
$kitKey = '';
$cfgPath = __DIR__ . '/config.php';
if (is_readable($cfgPath)) {
    $cfg = require $cfgPath;
    if (is_array($cfg) && !empty($cfg['KIT_API_KEY'])) {
        $kitKey = trim($cfg['KIT_API_KEY']);
    }
}
if ($kitKey === '') {
    $envKey = getenv('KIT_API_KEY');
    if ($envKey !== false && trim($envKey) !== '') {
        $kitKey = trim($envKey);
    }
}
if ($kitKey === '') {
    // Misconfiguration: never expose detail to the client, but log it server-side.
    error_log('invisiblescribe/subscribe.php: KIT_API_KEY not configured');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'not_configured']);
    exit;
}

// ---- read + validate input ----
$firstName = trim($_POST['first_name'] ?? '');
$email     = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'invalid_email']);
    exit;
}
// Cap field lengths defensively.
$firstName = mb_substr($firstName, 0, 80);
$email     = mb_substr($email, 0, 200);

/**
 * Minimal Kit v4 request helper.
 * Returns [int httpStatus, array decodedBody].
 */
function kit_request(string $method, string $url, string $apiKey, ?array $payload = null): array {
    $ch = curl_init($url);
    $headers = [
        'X-Kit-Api-Key: ' . $apiKey,
        'Accept: application/json',
    ];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 8,
    ];
    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $opts);
    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        error_log('invisiblescribe/subscribe.php curl error: ' . $err);
        return [0, []];
    }
    $decoded = json_decode($raw, true);
    return [$status, is_array($decoded) ? $decoded : []];
}

// ---- 1. create / upsert subscriber ----
// Kit v4: POST /subscribers upserts by email and returns the subscriber.
$subPayload = ['email_address' => $email, 'state' => 'active'];
if ($firstName !== '') {
    $subPayload['first_name'] = $firstName;
}
[$st, $body] = kit_request('POST', KIT_BASE . '/subscribers', $kitKey, $subPayload);

if ($st < 200 || $st >= 300) {
    error_log('invisiblescribe/subscribe.php subscriber create failed: HTTP ' . $st);
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'subscribe_failed']);
    exit;
}

// ---- 2. add to the EEC sequence (this triggers Day 0/Day 1 sends) ----
// Kit v4: POST /sequences/{id}/subscribers  { email_address }
[$st2, ] = kit_request(
    'POST',
    KIT_BASE . '/sequences/' . KIT_SEQUENCE_ID . '/subscribers',
    $kitKey,
    ['email_address' => $email]
);
$seqOk = ($st2 >= 200 && $st2 < 300);

// ---- 3. tag fintech-eec ----
// Kit v4: POST /tags/{id}/subscribers  { email_address }
[$st3, ] = kit_request(
    'POST',
    KIT_BASE . '/tags/' . KIT_TAG_ID . '/subscribers',
    $kitKey,
    ['email_address' => $email]
);
// Tag failure is non-fatal for the user's experience; log it but still succeed
// if the sequence enrollment landed (the sequence is what delivers the course).
if (!($st3 >= 200 && $st3 < 300)) {
    error_log('invisiblescribe/subscribe.php tag failed: HTTP ' . $st3 . ' for ' . $email);
}

if (!$seqOk) {
    error_log('invisiblescribe/subscribe.php sequence add failed: HTTP ' . $st2 . ' for ' . $email);
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'sequence_failed']);
    exit;
}

echo json_encode(['ok' => true]);
