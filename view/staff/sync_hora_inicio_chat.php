<?php

include('../cnf/session.php');



header('Content-Type: application/json; charset=utf-8');



/** @var PDO $PDO */



$tokenChat = isset($_POST['tokenChat']) ? (string)$_POST['tokenChat'] : '';

$chatId = isset($_POST['chatId']) ? (int)$_POST['chatId'] : 0;



$retorno = [

    'ok' => false,

    'started' => false,

    'both_joined' => false,

    'sol_joined' => false,

    'hora_inicio' => null,

    'ta_elapsed' => 0,

    'te' => null,

    'te_updated' => false,

];



if ($tokenChat === '' && $chatId <= 0) {

    echo json_encode($retorno);

    exit;

}



if ($chatId > 0) {

    $stmt = $PDO->prepare(

        'SELECT ci.id_chat, ci.fila_chat_id, ci.token_chat, cf.ate_resp, cf.bko_resp, cf.hora_inicio, cf.id_fila_chat, cf.fila_id, cf.te, cf.data_hora'

        .' FROM tbl_chat_info ci'

        .' INNER JOIN tbl_chat_fila cf ON cf.id_fila_chat = ci.fila_chat_id'

        .' WHERE ci.id_chat = ? AND ci.status_chat = 1 LIMIT 1'

    );

    $stmt->execute([$chatId]);

} else {

    $stmt = $PDO->prepare(

        'SELECT ci.id_chat, ci.fila_chat_id, ci.token_chat, cf.ate_resp, cf.bko_resp, cf.hora_inicio, cf.id_fila_chat, cf.fila_id, cf.te, cf.data_hora'

        .' FROM tbl_chat_info ci'

        .' INNER JOIN tbl_chat_fila cf ON cf.id_fila_chat = ci.fila_chat_id'

        .' WHERE ci.token_chat = ? AND ci.status_chat = 1 LIMIT 1'

    );

    $stmt->execute([$tokenChat]);

}



$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($row['id_chat'])) {

    echo json_encode($retorno);

    exit;

}



$chatId = (int)$row['id_chat'];

$ateResp = (int)$row['ate_resp'];

$bkoResp = (int)$row['bko_resp'];

$idFilaChat = (int)$row['id_fila_chat'];

$teAnterior = isset($row['te']) ? (string)$row['te'] : '';



if ($ateResp <= 0 || $bkoResp <= 0) {

    echo json_encode($retorno);

    exit;

}



$stmt = $PDO->prepare(

    'SELECT COUNT(DISTINCT participant_id) AS cnt FROM ('

    .' SELECT rem_id AS participant_id FROM tbl_chat_msg'

    .' WHERE chat_id = ? AND rem_id IN (?, ?)'

    ." AND (msg LIKE '%entrou no chat%' OR msg LIKE '%voltou para o chat%')"

    .' UNION'

    .' SELECT flag AS participant_id FROM tbl_chat_msg'

    .' WHERE chat_id = ? AND rem_id = 0 AND flag IN (?, ?)'

    ." AND (msg LIKE '%entrou no chat%' OR msg LIKE '%voltou para o chat%')"

    .') AS joins'

);

$stmt->execute([$chatId, $ateResp, $bkoResp, $chatId, $ateResp, $bkoResp]);

$joinInfo = $stmt->fetch(PDO::FETCH_ASSOC);

$joinCount = isset($joinInfo['cnt']) ? (int)$joinInfo['cnt'] : 0;



$stmtSol = $PDO->prepare(

    'SELECT MIN(data_hora) AS sol_join_at FROM tbl_chat_msg WHERE chat_id = ?'

    .' AND ((rem_id = ?) OR (rem_id = 0 AND flag = ?))'

    ." AND (msg LIKE '%entrou no chat%' OR msg LIKE '%voltou para o chat%')"

);

$stmtSol->execute([$chatId, $ateResp, $ateResp]);

$solJoinRow = $stmtSol->fetch(PDO::FETCH_ASSOC);

$solJoinAt = !empty($solJoinRow['sol_join_at']) ? (string)$solJoinRow['sol_join_at'] : '';

$solJoined = $solJoinAt !== '';



$dataHora = isset($row['data_hora']) ? (string)$row['data_hora'] : '';

$dataHoraValida = $dataHora !== '' && $dataHora !== '0000-00-00 00:00:00';



$horaInicioVazia = empty($row['hora_inicio'])

    || $row['hora_inicio'] === '0000-00-00 00:00:00';



$retorno['ok'] = true;

$retorno['both_joined'] = $joinCount >= 2;

$retorno['sol_joined'] = $solJoined;



/**

 * TE fixo: tempo entre entrada na fila e momento em que o solicitante entrou no chat.

 * Usa o timestamp da mensagem de entrada (não NOW()), para não virar contador a cada poll.

 */

if ($solJoined && $horaInicioVazia && $dataHoraValida) {

    $stmtTe = $PDO->prepare(

        'UPDATE tbl_chat_fila SET te = TIMEDIFF(?, data_hora)'

        .' WHERE id_fila_chat = ?'

        ." AND (hora_inicio IS NULL OR hora_inicio = '' OR hora_inicio = '0000-00-00 00:00:00')"

        .' AND (te IS NULL OR te = \'\' OR te <> TIMEDIFF(?, data_hora))'

    );

    $stmtTe->execute([$solJoinAt, $idFilaChat, $solJoinAt]);

    if ($stmtTe->rowCount() > 0) {

        $retorno['te_updated'] = true;

    }

}



$stmtTeAtual = $PDO->prepare('SELECT te FROM tbl_chat_fila WHERE id_fila_chat = ? LIMIT 1');

$stmtTeAtual->execute([$idFilaChat]);

$teRow = $stmtTeAtual->fetch(PDO::FETCH_ASSOC);

if (!empty($teRow['te'])) {

    $retorno['te'] = (string)$teRow['te'];

}



if (!$horaInicioVazia) {

    $stmtStatus = $PDO->prepare('SELECT status_fila FROM tbl_chat_fila WHERE id_fila_chat = ? LIMIT 1');

    $stmtStatus->execute([$idFilaChat]);

    $statusRow = $stmtStatus->fetch(PDO::FETCH_ASSOC);

    if (!empty($statusRow) && (int)$statusRow['status_fila'] === ST_FILA_AGUARDANDO_ATENDIMENTO) {

        $PDO->prepare('UPDATE tbl_chat_fila SET status_fila = ? WHERE id_fila_chat = ?')

            ->execute([ST_FILA_EM_ATENDIMENTO, $idFilaChat]);

    }

    $retorno['started'] = true;

    $retorno['both_joined'] = true;

    $retorno['hora_inicio'] = $row['hora_inicio'];

    $retorno['ta_elapsed'] = max(0, time() - strtotime((string)$row['hora_inicio']));

    echo json_encode($retorno);

    exit;

}



if ($joinCount < 2) {

    echo json_encode($retorno);

    exit;

}



$retorno['both_joined'] = true;



if ($solJoined && $dataHoraValida) {

    $stmt = $PDO->prepare(

        'UPDATE tbl_chat_fila SET hora_inicio = NOW(), te = TIMEDIFF(?, data_hora), status_fila = ?'

        .' WHERE id_fila_chat = ?'

        ." AND (hora_inicio IS NULL OR hora_inicio = '' OR hora_inicio = '0000-00-00 00:00:00')"

    );

    $stmt->execute([$solJoinAt, ST_FILA_EM_ATENDIMENTO, $idFilaChat]);

} elseif ($dataHoraValida) {

    $stmt = $PDO->prepare(

        'UPDATE tbl_chat_fila SET hora_inicio = NOW(), te = TIMEDIFF(NOW(), data_hora), status_fila = ?'

        .' WHERE id_fila_chat = ?'

        ." AND (hora_inicio IS NULL OR hora_inicio = '' OR hora_inicio = '0000-00-00 00:00:00')"

    );

    $stmt->execute([ST_FILA_EM_ATENDIMENTO, $idFilaChat]);

} else {

    $te = $teAnterior !== '' ? $teAnterior : '00:00:00';

    $stmt = $PDO->prepare(

        'UPDATE tbl_chat_fila SET hora_inicio = NOW(), te = ?, status_fila = ?'

        .' WHERE id_fila_chat = ?'

        ." AND (hora_inicio IS NULL OR hora_inicio = '' OR hora_inicio = '0000-00-00 00:00:00')"

    );

    $stmt->execute([$te, ST_FILA_EM_ATENDIMENTO, $idFilaChat]);

}



$stmt = $PDO->prepare('SELECT hora_inicio, te FROM tbl_chat_fila WHERE id_fila_chat = ? LIMIT 1');

$stmt->execute([$idFilaChat]);

$filaRow = $stmt->fetch(PDO::FETCH_ASSOC);



if (empty($filaRow['hora_inicio']) || $filaRow['hora_inicio'] === '0000-00-00 00:00:00') {

    echo json_encode($retorno);

    exit;

}



$horaInicio = $filaRow['hora_inicio'];

$teFinal = isset($filaRow['te']) ? (string)$filaRow['te'] : '';



$stmt = $PDO->prepare(

    'UPDATE tbl_tma_atend SET fila_chat_id = ?, chat_id = ?, fila_id = ?, date_in = NOW()'

    .' WHERE resp_id = ? AND date_out IS NULL'

    ." AND (date_in IS NULL OR date_in = '' OR date_in = '0000-00-00 00:00:00')"

    .' AND (fila_chat_id IS NULL OR fila_chat_id = 0 OR fila_chat_id = ?)'

);

$stmt->execute([$idFilaChat, $chatId, (int)$row['fila_id'], $bkoResp, $idFilaChat]);



$retorno['started'] = true;

$retorno['both_joined'] = true;

$retorno['hora_inicio'] = $horaInicio;

$retorno['ta_elapsed'] = max(0, time() - strtotime((string)$horaInicio));

if ($teFinal !== '' && $teFinal !== $teAnterior) {

    $retorno['te_updated'] = true;

}

$retorno['te'] = $teFinal !== '' ? $teFinal : $retorno['te'];



echo json_encode($retorno);

