<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Fortaleza');

$local = __DIR__ . '/conexao.local.php';
$cfg = [];
if (is_file($local)) {
    $loaded = require $local;
    if (is_array($loaded)) {
        $cfg = $loaded;
    }
}
$host = (string) ($cfg['host'] ?? getenv('ST_DB_HOST') ?: '');
$usuario = (string) ($cfg['usuario'] ?? getenv('ST_DB_USER') ?: '');
$senha = (string) ($cfg['senha'] ?? getenv('ST_DB_PASS') ?: '');
$banco = (string) ($cfg['banco'] ?? getenv('ST_DB_NAME') ?: '');
$port = (int) ($cfg['port'] ?? (getenv('ST_DB_PORT') ?: 3306));

if ($host === '' || $usuario === '' || $banco === '') {
    error_log('piloto_2.0 conexao: copie conexao.local.example.php para conexao.local.php');
    die('Configuração de banco ausente.');
}

$dsn = "mysql:host={$host};port={$port};dbname={$banco};charset=utf8";

try {
    $PDO = new PDO($dsn, $usuario, $senha);
} catch (PDOException $e) {
    error_log('piloto_2.0 conexao PDO: ' . $e->getMessage());
    die('Falha ao conectar ao banco de dados.');
}

try {
    $PDO_LOAD = new PDO($dsn, $usuario, $senha);
} catch (PDOException $e) {
    error_log('piloto_2.0 conexao PDO_LOAD: ' . $e->getMessage());
    die('Falha ao conectar ao banco de dados.');
}

if (!function_exists('generateHash')) {
    function generateHash(string $string): string
    {
        return sha1($string);
    }
}
