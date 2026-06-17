<?php
header('Content-Type: application/json');
$cfg = @include __DIR__ . '/../kit_secret.php';   // ['key'=>'...'] above public_html, NOT in git
$key = (is_array($cfg) && !empty($cfg['key'])) ? $cfg['key'] : getenv('KIT_API_KEY');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'invalid_email']); exit; }
if (!$key)   { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'no_config']); exit; }
$SEQ = 2796374; $TAG = 20415268;
function kit($url,$key,$payload){
  $ch=curl_init($url);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_TIMEOUT=>20,
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json','X-Kit-Api-Key: '.$key],
    CURLOPT_POSTFIELDS=>json_encode($payload)]);
  $r=curl_exec($ch);$c=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);return [$c,$r];
}
kit('https://api.kit.com/v4/subscribers',$key,['email_address'=>$email,'state'=>'active']);
list($sc) = kit("https://api.kit.com/v4/sequences/$SEQ/subscribers",$key,['email_address'=>$email]);
kit("https://api.kit.com/v4/tags/$TAG/subscribers",$key,['email_address'=>$email]);
if ($sc>=200 && $sc<300) { echo json_encode(['ok'=>true]); }
else { http_response_code(502); echo json_encode(['ok'=>false,'error'=>'kit_'.$sc]); }
