<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Fortaleza');

$local = __DIR__ . '/conn_config.local.php';
$cfg = [];
if (is_file($local)) {
    $loaded = require $local;
    if (is_array($loaded)) {
        $cfg = $loaded;
    }
}

$host_config = (string) ($cfg['host'] ?? getenv('ST_CFG_DB_HOST') ?: '');
$user_config = (string) ($cfg['usuario'] ?? getenv('ST_CFG_DB_USER') ?: '');
$pass_config = (string) ($cfg['senha'] ?? getenv('ST_CFG_DB_PASS') ?: '');
$db_config = (string) ($cfg['banco'] ?? getenv('ST_CFG_DB_NAME') ?: '');
$port = (int) ($cfg['port'] ?? 3306);

if ($host_config === '' || $user_config === '' || $db_config === '') {
    error_log('piloto_2.0 access conn_config: copie conn_config.local.example.php para conn_config.local.php');
    die('Configuração de banco ausente.');
}

$dns_config = "mysql:host={$host_config};port={$port};dbname={$db_config};charset=utf8";
try {
    $PDO_CONF = new PDO($dns_config, $user_config, $pass_config);
} catch (PDOException $e) {
    error_log('piloto_2.0 access conn_config: ' . $e->getMessage());
    die('Falha ao conectar ao banco de dados.');
}

if (!function_exists('generateHash')) {
    function generateHash($string)
    {
        return sha1($string);
    }
}
