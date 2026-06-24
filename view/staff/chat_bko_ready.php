<?php
include('../cnf/session.php');

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

$bkoId = (int)$infoUser['id_user'];
$contratoId = (int)($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);
$protocolo = isset($_POST['protocolo']) ? trim((string)$_POST['protocolo']) : '';
$indiceTab = isset($_POST['indice']) ? (int)$_POST['indice'] : 0;
$idFilaChatReq = isset($_POST['id_fila_chat']) ? (int)$_POST['id_fila_chat'] : 0;
$filaIdPref = isset($_POST['idFila']) ? (int)$_POST['idFila'] : (int)($infoUser['fila_id'] ?? 0);

$filasIn = '';
if ($protocolo === '' && $filaIdPref <= 0) {
    $stmt = $PDO->prepare('SELECT filas FROM tbl_user_filas WHERE user_id=? LIMIT 1');
    $stmt->execute([$bkoId]);
    $cnfFilas = $stmt->fetch(PDO::FETCH_ASSOC);
    $filasIn = !empty($cnfFilas['filas']) ? (string)$cnfFilas['filas'] : '';
}

$ctx = stChatBkoBootstrap($PDO, $bkoId, $protocolo, $contratoId, $filaIdPref, $filasIn, $indiceTab, $idFilaChatReq);

echo json_encode([
    'ok' => true,
    'ready' => $ctx['state'] === 'ready',
    'state' => $ctx['state'],
    'message' => $ctx['message'] ?? '',
    'id_fila_chat' => (int)($ctx['infFila']['id_fila_chat'] ?? 0),
    'id_chat' => (int)$ctx['chatId'],
    'protocolo' => (string)($ctx['infFila']['protocolo'] ?? ''),
    'status_fila' => (int)($ctx['infFila']['status_fila'] ?? 0),
]);
