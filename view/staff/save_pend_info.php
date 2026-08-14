<?php
include("../cnf/session.php");

$idFilaChat = (int) ($_POST['id_fila_chat'] ?? 0);
$idChat = (int) ($_POST['id_chat'] ?? 0);
$txtPend = (string) ($_POST['txt_pend'] ?? '');

if ($idFilaChat <= 0) {
    return;
}

$stmt = $PDO->prepare("SELECT ate_resp, bko_resp, contrato_id from tbl_chat_fila where id_fila_chat=?");
$stmt->execute([$idFilaChat]);
$infoChat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($infoChat) || count($infoChat) === 0) {
    $stmt = $PDO->prepare("SELECT ate_resp, bko_resp, contrato_id from tbl_chat_fila_secondary where id_fila_chat=?");
    $stmt->execute([$idFilaChat]);
    $infoChat = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!is_array($infoChat) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($infoChat['contrato_id'] ?? 0))) {
    return;
}

$stmt = $PDO->prepare(
    "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([
    $idChat,
    $infoUser['contrato_id'],
    $infoChat['bko_resp'],
    $infoChat['ate_resp'],
    $txtPend,
]);

$stmt = $PDO->prepare("UPDATE tbl_pend_info SET info_fim=?, data_hora_fim=now() where chat_id=?");
$stmt->execute([$txtPend, $idFilaChat]);

$stmt = $PDO->prepare("UPDATE tbl_chat_fila SET status_fila=4 where id_fila_chat=?");
$stmt->execute([$idFilaChat]);

$stmt = $PDO->prepare("UPDATE tbl_chat_fila_secondary SET status_fila=4 where id_fila_chat=?");
$stmt->execute([$idFilaChat]);

$stmt = $PDO->prepare("UPDATE tbl_chat_info SET status_chat=4 where fila_chat_id=?");
$stmt->execute([$idFilaChat]);

$stmt = $PDO->prepare("UPDATE tbl_chat_info_secondary SET status_chat=4 where fila_chat_id=?");
$stmt->execute([$idFilaChat]);

?>
