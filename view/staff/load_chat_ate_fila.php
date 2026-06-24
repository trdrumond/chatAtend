<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

$idFila = isset($_POST['idFila']) ? (int)$_POST['idFila'] : 0;

if ($idFila > 0 && isset($_SESSION['fila_abandono_pendente'][$idFila])) {
    unset($_SESSION['fila_abandono_pendente'][$idFila]);
    if (empty($_SESSION['fila_abandono_pendente'])) {
        unset($_SESSION['fila_abandono_pendente']);
    }
}

$retorno = [
    'ok' => true,
    'redirect' => '',
    'aguardando' => true
];

$userId = (int)$infoUser['id_user'];

$stm = $PDO->prepare(
    'SELECT id_fila_chat, protocolo, data_hora, fila_id, bko_resp, status_fila, te,'
    .' timediff(now(), data_hora) AS te_diff'
    .' FROM tbl_chat_fila WHERE id_fila_chat=? AND ate_resp=? LIMIT 1'
);
$stm->execute([$idFila, $userId]);
$infoFila = $stm->fetch(PDO::FETCH_ASSOC) ?: [];

$bkoAtribuido = !empty($infoFila['bko_resp']) && (int)$infoFila['bko_resp'] > 0;
$statusFila = isset($infoFila['status_fila']) ? (int)$infoFila['status_fila'] : 0;

if ($bkoAtribuido && $statusFila === ST_FILA_NA_FILA) {
    stFilaEnsureSituacaoAguardando($PDO);
    $teVal = !empty($infoFila['te']) ? $infoFila['te'] : ($infoFila['te_diff'] ?? '');
    $upd = $PDO->prepare(
        'UPDATE tbl_chat_fila SET status_fila=?, te=COALESCE(NULLIF(te, \'\'), ?)'
        .' WHERE id_fila_chat=? AND ate_resp=? AND status_fila=?'
    );
    $upd->execute([ST_FILA_AGUARDANDO_ATENDIMENTO, $teVal, $idFila, $userId, ST_FILA_NA_FILA]);
    if ($upd->rowCount() > 0) {
        $statusFila = ST_FILA_AGUARDANDO_ATENDIMENTO;
        $infoFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
    }
}

if (stFilaAtendimentoEncerrado($statusFila)) {
    $retorno['redirect'] = 'dash-cha';
    $retorno['aguardando'] = false;
    echo json_encode($retorno);
    exit;
}

if (stFilaDeveChamarSolicitante($statusFila) || ($bkoAtribuido && $statusFila === ST_FILA_NA_FILA)) {
    $retorno['redirect'] = 'chat-ate';
    $retorno['aguardando'] = false;
    $retorno['status_fila'] = $statusFila > 0 ? $statusFila : ST_FILA_AGUARDANDO_ATENDIMENTO;
}

if(empty($infoFila['id_fila_chat'])){
    $retorno['redirect'] = 'dash-cha';
    $retorno['aguardando'] = false;
}

echo json_encode($retorno);
