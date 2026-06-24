<?php
$status = opcache_get_status();

function toGB($bytes) {
    return number_format($bytes / 1073741824, 2) . ' GB';
}

echo "<br>Memória usada: " . toGB($status['memory_usage']['used_memory']) . "\n";
echo "<br>Memória livre: " . toGB($status['memory_usage']['free_memory']) . "\n";
echo "<br>Memória desperdiçada: " . toGB($status['memory_usage']['wasted_memory']) . "\n";
echo "<br>Porcentagem de desperdício: " . $status['memory_usage']['current_wasted_percentage'] . "%\n";
