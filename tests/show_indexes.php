<?php
require __DIR__ . '/../view/cnf/conexao.php';
$tables = ['tbl_user', 'tbl_chat_fila', 'tbl_chat_msg', 'tbl_log_diario', 'tbl_chat_info', 'tbl_log_atendimento'];
foreach ($tables as $t) {
    echo "==== $t ====\n";
    $st = $PDO->query('SHOW INDEX FROM `' . str_replace('`', '', $t) . '`');
    if (!$st) {
        echo "ERR\n";
        continue;
    }
    $seen = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $k = $r['Key_name'] . ' | ' . $r['Column_name'] . ' | unique=' . $r['Non_unique'];
        if (!isset($seen[$k])) {
            echo $k . "\n";
            $seen[$k] = 1;
        }
    }
}
