<?php
include('../cnf/session.php');

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

$userId = (int)$infoUser['id_user'];
$idFilaChatReq = isset($_POST['id_fila_chat']) ? (int)$_POST['id_fila_chat'] : 0;
if ($idFilaChatReq <= 0 && isset($_POST['idFila'])) {
    $idFilaChatReq = (int)$_POST['idFila'];
}

$contratoId = (int)($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);

$ctx = stChatAteSolBootstrap($PDO, $userId, $idFilaChatReq, $contratoId);

echo json_encode([
    'ok' => true,
    'ready' => $ctx['state'] === 'ready',
    'state' => $ctx['state'],
    'message' => $ctx['message'] ?? '',
    'id_fila_chat' => (int)($ctx['infFila']['id_fila_chat'] ?? 0),
    'id_chat' => (int)$ctx['chatId'],
    'bko_resp' => (int)$ctx['bkoResp'],
    'status_fila' => (int)($ctx['infFila']['status_fila'] ?? 0),
]);
