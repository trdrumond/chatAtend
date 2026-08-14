<?php
include_once("conn.php");
include_once("func.php");
//echo "<br>Replace Inicio";

//if(date('H:i:s') < '09:00:00'){

$sql1 = "SELECT * from tbl_chat_fila order by data_hora desc";
$stmt1 = $PDO->prepare($sql1);
$stmt1->execute();
$info1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmtFilaSec = $PDO->prepare(
    'REPLACE INTO tbl_chat_fila_secondary (id_fila_chat,protocolo,data_hora,contrato_id,fila_id,assunto_id,ate_resp,bko_resp,status_fila,hora_inicio,hora_fim,ta,te,motivo_cancela,motivo)'
    .' VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
);
foreach ($info1 as $ls) {
    $stmtFilaSec->execute([
        stReplaceNullable($ls['id_fila_chat'] ?? null),
        stReplaceNullable($ls['protocolo'] ?? null),
        stReplaceNullable($ls['data_hora'] ?? null),
        stReplaceNullable($ls['contrato_id'] ?? null),
        stReplaceNullable($ls['fila_id'] ?? null),
        stReplaceNullable($ls['assunto_id'] ?? null),
        stReplaceNullable($ls['ate_resp'] ?? null),
        stReplaceNullable($ls['bko_resp'] ?? null),
        stReplaceNullable($ls['status_fila'] ?? null),
        stReplaceNullable($ls['hora_inicio'] ?? null),
        stReplaceNullable($ls['hora_fim'] ?? null),
        stReplaceNullable($ls['ta'] ?? null),
        stReplaceNullable($ls['te'] ?? null),
        stReplaceNullable($ls['motivo_cancela'] ?? null),
        stReplaceNullable($ls['motivo'] ?? null),
    ]);
}

$sql2 = "SELECT * from tbl_chat_info order by data_hora desc";
$stmt3 = $PDO->prepare($sql2);
$stmt3->execute();
$info2 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$stmtChatInfoSec = $PDO->prepare(
    'REPLACE INTO tbl_chat_info_secondary (id_chat, contrato_id, assunto_id, fila_id, token_chat, data_hora, rem_chat, dest_chat, fila_chat_id, indice)'
    .' VALUES (?,?,?,?,?,?,?,?,?,?)'
);
foreach ($info2 as $ls) {
    $stmtChatInfoSec->execute([
        stReplaceNullable($ls['id_chat'] ?? null),
        stReplaceNullable($ls['contrato_id'] ?? null),
        stReplaceNullable($ls['assunto_id'] ?? null),
        stReplaceNullable($ls['fila_id'] ?? null),
        stReplaceNullable($ls['token_chat'] ?? null),
        stReplaceNullable($ls['data_hora'] ?? null),
        stReplaceNullable($ls['rem_chat'] ?? null),
        stReplaceNullable($ls['dest_chat'] ?? null),
        stReplaceNullable($ls['fila_chat_id'] ?? null),
        stReplaceNullable($ls['indice'] ?? null),
    ]);
}

$sql7 = "SELECT user_id, contrato_id, agencia_id, fila_id, data_hora, acao from tbl_log_atendimento order by data_hora desc";
$stmt7 = $PDO->prepare($sql7);
$stmt7->execute();
$info7 = $stmt7->fetchAll(PDO::FETCH_ASSOC);

$stmtLogSec = $PDO->prepare(
    'REPLACE INTO tbl_log_atendimento_secondary (user_id, contrato_id, agencia_id, fila_id, data_hora, acao) VALUES (?,?,?,?,?,?)'
);
foreach ($info7 as $ls) {
    $stmtLogSec->execute([
        stReplaceNullable($ls['user_id'] ?? null),
        stReplaceNullable($ls['contrato_id'] ?? null),
        stReplaceNullable($ls['agencia_id'] ?? null),
        stReplaceNullable($ls['fila_id'] ?? null),
        stReplaceNullable($ls['data_hora'] ?? null),
        stReplaceNullable($ls['acao'] ?? null),
    ]);
}

$sqlTma = "SELECT id, resp_id, contrato_id, date_disp, fila_id, fila_chat_id, chat_id, date_in, date_out, sla from tbl_tma_atend order by date_disp desc";
$stmt = $PDO->prepare($sqlTma);
$stmt->execute();
$infoTma = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtTmaSec = $PDO->prepare(
    'REPLACE INTO tbl_tma_atend_secondary (id, resp_id, contrato_id, date_disp, fila_id, fila_chat_id, chat_id, date_in, date_out, sla) VALUES (?,?,?,?,?,?,?,?,?,?)'
);
foreach ($infoTma as $ls) {
    $stmtTmaSec->execute([
        stReplaceNullable($ls['id'] ?? null),
        stReplaceNullable($ls['resp_id'] ?? null),
        stReplaceNullable($ls['contrato_id'] ?? null),
        stReplaceNullable($ls['date_disp'] ?? null),
        stReplaceNullable($ls['fila_id'] ?? null),
        stReplaceNullable($ls['fila_chat_id'] ?? null),
        stReplaceNullable($ls['chat_id'] ?? null),
        stReplaceNullable($ls['date_in'] ?? null),
        stReplaceNullable($ls['date_out'] ?? null),
        stReplaceNullable($ls['sla'] ?? null),
    ]);
}

//}

//include_once("replace_msg.php");

//echo "<br>Fim de replace";
?>
