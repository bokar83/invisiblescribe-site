<?php
/**
 * Invisible Scribe -- Kit API config (SAMPLE).
 *
 * Copy this file to config.php on the Hostinger server (NOT in git) and paste
 * the Kit v4 API key. subscribe.php reads config.php first, then falls back to
 * the KIT_API_KEY environment variable.
 *
 *   cp config.sample.php config.php
 *   # then edit config.php and set the real key
 *
 * config.php is gitignored. NEVER commit the real key.
 * Get the key in Kit: Settings -> Developer -> API Keys (v4 key).
 */

return [
    'KIT_API_KEY' => 'PASTE_KIT_V4_API_KEY_HERE',
];
