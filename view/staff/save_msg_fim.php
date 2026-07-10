<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

$tokenChat = isset($_POST['tokenChat']) ? trim((string) $_POST['tokenChat']) : '';
if ($tokenChat === '') {
    return;
}

$sql = 'SELECT a.id_chat, a.fila_chat_id, a.status_chat, b.status_fila,'
    .' timediff(now(), b.hora_inicio) AS ta, b.bko_resp'
    .' FROM tbl_chat_info a'
    .' INNER JOIN tbl_chat_fila b ON a.fila_chat_id = b.id_fila_chat'
    .' WHERE a.token_chat = ? LIMIT 1';
$stmt = $PDO->prepare($sql);
$stmt->execute([$tokenChat]);
$infoChat = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($infoChat['id_chat'])) {
    return;
}

$idChat = (int) $infoChat['id_chat'];
$idFilaChat = (int) $infoChat['fila_chat_id'];
$statusFila = (int) $infoChat['status_fila'];
$statusChat = (int) $infoChat['status_chat'];
$filaEncerrada = ($statusFila >= 4);
$chatEncerrado = ($statusChat >= 4);

if ($filaEncerrada && $chatEncerrado) {
    return;
}

if (!empty($infoChat['bko_resp']) && !$filaEncerrada) {
    logAtendimento($PDO, $infoChat['bko_resp'], 'Pos');
}

$msg = isset($_POST['msg']) ? (string) $_POST['msg'] : '';
$contrato = isset($_POST['contrato']) ? (int) $_POST['contrato'] : 0;

if ($msg !== '' && !$chatEncerrado) {
    $sql = 'INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, 0, 0, ?)';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$idChat, $contrato, $msg]);
}

if (!$filaEncerrada) {
    $sql = 'UPDATE tbl_chat_fila SET status_fila=4, hora_fim=NOW(), ta=?'
        .' WHERE id_fila_chat=? AND status_fila IN (1, 2)';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$infoChat['ta'], $idFilaChat]);
}

$sql = 'UPDATE tbl_chat_info SET status_chat=4 WHERE id_chat=? AND status_chat=1';
$stmt = $PDO->prepare($sql);
$stmt->execute([$idChat]);

$sql = 'SELECT id, timediff(now(), date_in) AS sla FROM tbl_tma_atend'
    .' WHERE fila_chat_id=? AND date_out IS NULL ORDER BY id DESC LIMIT 1';
$stmt = $PDO->prepare($sql);
$stmt->execute([$idFilaChat]);
$infoAtend = $stmt->fetch(PDO::FETCH_ASSOC);

if (!empty($infoAtend['id'])) {
    $sql = 'UPDATE tbl_tma_atend SET date_out=NOW(), sla=?, chat_id=? WHERE id=?';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$infoAtend['sla'], $idChat, (int) $infoAtend['id']]);
}




?>
