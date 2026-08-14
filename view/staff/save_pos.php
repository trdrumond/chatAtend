<?php
include("../cnf/session.php");

$filaIdPost = (int) ($_POST['fila_id'] ?? 0);
$situacaoDem = (int) ($_POST['situacao_dem'] ?? 0);
$motivoSituacao = (string) ($_POST['motivo_situacao'] ?? '');
$tokenChat = (string) ($_POST['tokenChat'] ?? '');
$pausa = (int) ($_POST['pausa'] ?? 0);
$indice = (int) ($_POST['indice'] ?? 0);
$assunto = (int) ($_POST['assunto'] ?? 0);
$confirmaAssunto = (int) ($_POST['confirma_assunto'] ?? 0);

if ($pausa === 1) {
    $stmt = $PDO->prepare("INSERT INTO tbl_pause (user_id, hora_in, pause_id) VALUES (?, now(), 1)");
    $stmt->execute([(int) $_SESSION['dados']['id_user']]);
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Pausa');
}

if ($tokenChat === '') {
    return;
}

$stmt = $PDO->prepare(
    "SELECT id_chat, fila_chat_id, rem_chat, dest_chat, contrato_id from tbl_chat_info a where token_chat=?"
);
$stmt->execute([$tokenChat]);
$infoChat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($infoChat) || empty($infoChat['fila_chat_id'])) {
    return;
}

$filaChatId = (int) $infoChat['fila_chat_id'];
$idChat = (int) $infoChat['id_chat'];
$bkoId = (int) $_SESSION['dados']['id_user'];

$stmt = $PDO->prepare("SELECT timediff(now(), hora_fim) as tp from tbl_chat_fila where id_fila_chat=?");
$stmt->execute([$filaChatId]);
$tp = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['tp' => '00:00:00'];

if ($situacaoDem === 3 || $situacaoDem === 7) {
    $sql = 'UPDATE tbl_chat_fila SET'
        .' status_fila=?,'
        .' hora_fim = IF(hora_fim IS NULL OR hora_fim = \'\' OR hora_fim = \'0000-00-00 00:00:00\', NOW(), hora_fim),'
        .' hora_inicio = IF(hora_inicio IS NULL OR hora_inicio = \'\' OR hora_inicio = \'0000-00-00 00:00:00\', NOW(), hora_inicio),'
        .' ta = IF(ta IS NULL OR ta = \'\' OR ta = \'00:00:00\', TIMEDIFF(NOW(), IF(hora_inicio IS NULL OR hora_inicio = \'\' OR hora_inicio = \'0000-00-00 00:00:00\', NOW(), hora_inicio)), ta),'
        .' te = IF(te IS NULL OR te = \'\' OR te = \'00:00:00\', TIMEDIFF(IF(hora_inicio IS NULL OR hora_inicio = \'\' OR hora_inicio = \'0000-00-00 00:00:00\', NOW(), hora_inicio), data_hora), te),'
        .' bko_resp = IF(bko_resp IS NULL OR bko_resp = 0 OR bko_resp = \'\', ?, bko_resp),'
        .' motivo = ?'
        .' WHERE id_fila_chat=?';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$situacaoDem, $bkoId, $motivoSituacao, $filaChatId]);

    $stmt = $PDO->prepare("UPDATE tbl_chat_info SET status_chat=? where fila_chat_id=?");
    $stmt->execute([$situacaoDem, $filaChatId]);

    $stmt = $PDO->prepare("SELECT nome_situacao from tbl_situacao_chat where id_situacao=?");
    $stmt->execute([$situacaoDem]);
    $sit = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['nome_situacao' => ''];

    $msg = $sit['nome_situacao'] . ' - ' . $motivoSituacao;

    $stmt = $PDO->prepare(
        "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, 0, 0, ?)"
    );
    $stmt->execute([$idChat, $infoChat['contrato_id'], $msg]);
}

if ($situacaoDem === 3) {
    $stmt = $PDO->prepare(
        "INSERT INTO tbl_pend_info (fila_id, chat_id, ate_resp, bko_resp, situacao_id, motivo) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $filaIdPost,
        $filaChatId,
        $infoChat['dest_chat'],
        $bkoId,
        $situacaoDem,
        $motivoSituacao,
    ]);
}

$stmt = $PDO->prepare("SELECT count(*) as qtd from tbl_forms_pos_input_campo where fila_id=?");
$stmt->execute([$filaIdPost]);
$fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['qtd' => 0];

$contratoId = (int) ($infoUser['contrato_id'] ?? 0);

if ((int) $fila['qtd'] < 1) {
    $stmt = $PDO->prepare("UPDATE tbl_chat_fila SET assunto_id=? where id_fila_chat=?");
    $stmt->execute([$assunto, $filaChatId]);

    $tablePos = 'tbl_in_pos_' . $filaIdPost . '_' . $contratoId;
    if (!preg_match('/^tbl_in_pos_\d+_\d+$/', $tablePos)) {
        return;
    }

    $sqlAlterDados = "INSERT INTO {$tablePos} (data_hora, situacao_id, fila_id, chat_id, tp) VALUES (now(), 4, ?, ?, ?)";
    $stmt = $PDO->prepare($sqlAlterDados);
    $resultDados = $stmt->execute([$filaIdPost, $filaChatId, $tp['tp']]);

    if ($pausa !== 1) {
        logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Disponivel');
    }

    if ($resultDados) {
        ?>
<script>
clearInterval(time_<?= $idChat ?>);
setTimeout(function() {
    fechaAba(<?= $indice ?>);
}, 100);
</script>
<?php
    }
} else {
    $stmt = $PDO->prepare("SELECT nome_campo FROM tbl_forms_pos_input_campo where fila_id=?");
    $stmt->execute([$filaIdPost]);
    $campo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $PDO->prepare("UPDATE tbl_chat_fila SET assunto_id=? where id_fila_chat=?");
    $stmt->execute([$confirmaAssunto, $filaChatId]);

    $stmt = $PDO->prepare("UPDATE tbl_chat_info SET assunto_id=? where fila_chat_id=?");
    $stmt->execute([$confirmaAssunto, $filaChatId]);

    $tablePos = 'tbl_in_pos_' . $filaIdPost . '_' . $contratoId;
    if (!preg_match('/^tbl_in_pos_\d+_\d+$/', $tablePos)) {
        return;
    }

    $colNames = [];
    $placeholders = [];
    $params = [$filaIdPost, $filaChatId, $tp['tp']];

    foreach ($campo as $c) {
        $nome = (string) ($c['nome_campo'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $nome)) {
            continue;
        }
        $colNames[] = $nome;
        $placeholders[] = '?';
        $params[] = (string) ($_POST[$nome] ?? '');
    }

    if ($colNames !== []) {
        $sqlAlterDados = 'INSERT INTO ' . $tablePos
            . ' (data_hora, situacao_id, fila_id, chat_id, tp, ' . implode(', ', $colNames) . ')'
            . ' VALUES (now(), 4, ?, ?, ?, ' . implode(', ', $placeholders) . ')';
        $stmt = $PDO->prepare($sqlAlterDados);
        $resultDados = $stmt->execute($params);

        if ($resultDados == 1) {
            ?>
<script>
clearInterval(time_<?= $idChat ?>);
setTimeout(function() {
    fechaAba(<?= $indice ?>);
}, 100);
</script>
<?php
        }
    }
}

?>
