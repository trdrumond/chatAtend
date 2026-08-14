<?php
include_once("conexao.php");
include_once("func.php");

if (date('H:i:s') < '08:00:00') {
    $sql1 = "SELECT id_msg, data_hora, chat_id, contrato_id, rem_id, dest_id, msg, flag from tbl_chat_msg";
    $stmt = $PDO->prepare($sql1);
    $info1 = [];
    if ($stmt->execute()) {
        $info1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmtMsgSec = $PDO->prepare(
        'REPLACE INTO tbl_chat_msg_secondary (id_msg, data_hora, chat_id, contrato_id, rem_id, dest_id, msg, flag)'
        .' VALUES (?,?,?,?,?,?,?,?)'
    );
    foreach ($info1 as $ls) {
        $stmtMsgSec->execute([
            stReplaceNullable($ls['id_msg'] ?? null),
            stReplaceNullable($ls['data_hora'] ?? null),
            stReplaceNullable($ls['chat_id'] ?? null),
            stReplaceNullable($ls['contrato_id'] ?? null),
            stReplaceNullable($ls['rem_id'] ?? null),
            stReplaceNullable($ls['dest_id'] ?? null),
            stReplaceNullable($ls['msg'] ?? null),
            stReplaceNullable($ls['flag'] ?? null),
        ]);
    }
}

?>
