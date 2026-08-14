<?php
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
if ($ip !== '127.0.0.1' && $ip !== '::1') {
    http_response_code(403);
    exit;
}

$status = opcache_get_status();
if (!is_array($status) || empty($status['memory_usage'])) {
    echo 'OPcache indisponível.';
    exit;
}

function toGB($bytes) {
    return number_format($bytes / 1073741824, 2) . ' GB';
}

echo "<br>Memória usada: " . toGB($status['memory_usage']['used_memory']) . "\n";
echo "<br>Memória livre: " . toGB($status['memory_usage']['free_memory']) . "\n";
echo "<br>Memória desperdiçada: " . toGB($status['memory_usage']['wasted_memory']) . "\n";
echo "<br>Porcentagem de desperdício: " . $status['memory_usage']['current_wasted_percentage'] . "%\n";
