<?php
if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
    opcache_reset();
    echo "OPcache limpo.";
} else {
    http_response_code(403);
}
