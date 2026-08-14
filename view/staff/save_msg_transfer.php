<?php
include("../cnf/session.php");

$tokenChat = (string) ($_POST['tokenChat'] ?? '');
if ($tokenChat === '') {
    return;
}

$stmt = $PDO->prepare(
    "SELECT a.id_chat, a.fila_chat_id, a.contrato_id, timediff(now(), b.hora_inicio) as ta from tbl_chat_info a, tbl_chat_fila b where a.status_chat=1 and a.fila_chat_id=b.id_fila_chat and a.token_chat=?"
);
$stmt->execute([$tokenChat]);
$infoChat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($infoChat) || empty($infoChat['id_chat'])) {
    return;
}

$idChat = (int) $infoChat['id_chat'];
$idFila = (int) $infoChat['fila_chat_id'];
$contrato = (int) ($infoChat['contrato_id'] ?? 0);
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
    return;
}
$rem = (int) ($_POST['rem'] ?? 0);
$dest = (int) ($_POST['dest'] ?? 0);
$msg = (string) ($_POST['msg'] ?? '');

$stmt = $PDO->prepare(
    "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, ?, ?, ?)"
);
$result = $stmt->execute([$idChat, $contrato, $rem, $dest, $msg]);

if ($result) {
    $stmt = $PDO->prepare("UPDATE tbl_chat_fila SET status_fila=6, hora_fim=now(), ta=? where id_fila_chat=?");
    $result = $stmt->execute([$infoChat['ta'], $idFila]);

    if ($result) {
        $stmt = $PDO->prepare("UPDATE tbl_chat_info SET status_chat=6 where id_chat=?");
        $result = $stmt->execute([$idChat]);

        if ($result) {
            $stmt = $PDO->prepare(
                "SELECT id, timediff(now(), date_in) as sla from tbl_tma_atend where chat_id=? and fila_chat_id=?"
            );
            $stmt->execute([$idChat, $idFila]);
            $infoAtend = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($infoAtend['id'])) {
                $stmt = $PDO->prepare("UPDATE tbl_tma_atend SET date_out=now(), sla=? where id=?");
                $stmt->execute([$infoAtend['sla'], (int) $infoAtend['id']]);
            }

            $stmt = $PDO->prepare("SELECT protocolo, ate_resp, bko_resp from tbl_chat_fila where id_fila_chat=?");
            $stmt->execute([$idFila]);
            $infoFilaNew = $stmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($infoFilaNew)) {
                $fila = (string) ($_POST['fila'] ?? '');
                $assunto = (string) ($_POST['assunto'] ?? '');
                $stmt = $PDO->prepare(
                    "INSERT INTO tbl_chat_fila (protocolo, contrato_id, fila_id, assunto_id, ate_resp) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $infoFilaNew['protocolo'],
                    $contrato,
                    $fila,
                    $assunto,
                    $infoFilaNew['ate_resp'],
                ]);
            }
        }
    }
}

?>
