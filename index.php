<?php
// invisiblescribe.com apex A/B test: concept v1 vs v4 (Boubacar 2026-06-16).
// Sticky 50/50 split by first-party cookie. Variant exposed for analytics + CTA attribution.
$variants = ['v1', 'v4'];
$v = (isset($_COOKIE['isv']) && in_array($_COOKIE['isv'], $variants, true))
   ? $_COOKIE['isv']
   : $variants[random_int(0, 1)];
setcookie('isv', $v, [
  'expires'  => time() + 60 * 60 * 24 * 90,
  'path'     => '/',
  'secure'   => true,
  'httponly' => false,
  'samesite' => 'Lax',
]);
$file = __DIR__ . '/concepts/' . $v . '/index.html';
if (!is_file($file)) { http_response_code(500); echo 'variant unavailable'; exit; }
$html = file_get_contents($file);
$inject = '<script>window.__VARIANT__=' . json_encode($v)
        . ';document.documentElement.setAttribute("data-variant",' . json_encode($v) . ');</script>';
$html = preg_replace('/<\/head>/i', $inject . '</head>', $html, 1);
header('Cache-Control: no-store, must-revalidate, max-age=0');
echo $html;
