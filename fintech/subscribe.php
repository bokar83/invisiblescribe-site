<?php
/**
 * Invisible Scribe -- EEC opt-in handler.
 * Receives first_name + email from the landing form, then via the Kit v4 API:
 *   1. creates/upserts the subscriber (with first_name for email personalization)
 *   2. adds them to the EEC sequence (triggers Day 0/Day 1 sends)
 *   3. tags them fintech-eec
 * Returns JSON {ok:true} on success, {ok:false,error:"..."} otherwise.
 *
 * The Kit API key is NEVER in git. It is read from KIT_API_KEY (env) or a .env
 * file ABOVE the web root (not web-servable). See config.sample.php.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405); echo json_encode(['ok'=>false,'error'=>'method_not_allowed']); exit;
}

// Key comes ONLY from env or a .env above the web root (not in git, not web-accessible).
$key = getenv('KIT_API_KEY');
if (!$key) {
  foreach (['/.env', '/../.env', '/../../.env', '/../../../.env'] as $rel) {
    $f = __DIR__ . $rel;
    if (is_file($f)) {
      foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, 'KIT_API_KEY=') === 0) { $key = trim(substr($line, 12), " \"'"); break 2; }
      }
    }
  }
}

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$firstName = trim($_POST['first_name'] ?? '');
$firstName = mb_substr($firstName, 0, 80);

if (!$email) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'invalid_email']); exit; }
if (!$key)   { error_log('invisiblescribe/subscribe.php: KIT_API_KEY not configured'); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'no_config']); exit; }

const KIT_SEQUENCE_ID = 2796374;   // "The Fintech Founder's Authority Playbook (EEC)"
const KIT_TAG_ID      = 20415268;  // fintech-eec

function kit($url, $key, $payload) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Accept: application/json','X-Kit-Api-Key: '.$key],
    CURLOPT_POSTFIELDS     => json_encode($payload),
  ]);
  $r = curl_exec($ch);
  $c = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$c, $r];
}

// 1. upsert subscriber (include first_name so EEC emails can personalize)
$sub = ['email_address' => $email, 'state' => 'active'];
if ($firstName !== '') { $sub['first_name'] = $firstName; }
kit('https://api.kit.com/v4/subscribers', $key, $sub);

// 2. add to the EEC sequence (this is what delivers the course)
list($seqCode) = kit('https://api.kit.com/v4/sequences/'.KIT_SEQUENCE_ID.'/subscribers', $key, ['email_address' => $email]);

// 3. tag fintech-eec (non-fatal if it fails; the sequence is what matters)
list($tagCode) = kit('https://api.kit.com/v4/tags/'.KIT_TAG_ID.'/subscribers', $key, ['email_address' => $email]);
if (!($tagCode >= 200 && $tagCode < 300)) { error_log('invisiblescribe/subscribe.php tag failed: HTTP '.$tagCode.' for '.$email); }

if ($seqCode >= 200 && $seqCode < 300) {
  echo json_encode(['ok' => true]);
} else {
  error_log('invisiblescribe/subscribe.php sequence add failed: HTTP '.$seqCode.' for '.$email);
  http_response_code(502);
  echo json_encode(['ok' => false, 'error' => 'kit_'.$seqCode]);
}
