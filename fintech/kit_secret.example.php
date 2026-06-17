<?php
// Copy to kit_secret.php (gitignored) on the server with the real key.
// This file only RETURNs the array -- it never echoes, so a direct HTTP
// request executes it and outputs nothing. .htaccess also denies it (403).
return ['key' => 'kit_REPLACE_WITH_REAL_KEY'];
