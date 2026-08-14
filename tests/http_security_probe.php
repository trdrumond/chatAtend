<?php
$urls = [
    'http://127.0.0.1/solvetask/piloto_2.0/view/cnf/conexao.php',
    'http://127.0.0.1/solvetask/piloto_2.0/view/staff/reset_senha.php',
    'http://127.0.0.1/solvetask/piloto_2.0/view/staff/cron_ia_analise_diaria.php',
    'http://127.0.0.1/solvetask/piloto_2.0/api_monitora/index.php',
    'http://127.0.0.1/solvetask/piloto_2.0/painel/index.php',
    'http://127.0.0.1/solvetask/piloto_2.0/access/conn_config.php',
    'http://127.0.0.1/solvetask/piloto_2.0/view/staff/phpmailer/test_script/index.php',
];
foreach ($urls as $u) {
    $ch = curl_init($u);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $b = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $loc = '';
    if (preg_match('/^Location:\s*(.+)$/mi', (string) $b, $m)) {
        $loc = ' -> ' . trim($m[1]);
    }
    echo $code . ' ' . $u . $loc . PHP_EOL;
}
