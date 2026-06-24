<?php
include('../cnf/session.php');
require_once __DIR__ . '/../cnf/st_dash_acomp.php';

header('Content-Type: application/json; charset=utf-8');

$chatId = (int)($_POST['chat_id'] ?? 0);
$bkoId = (int)($_POST['user_id'] ?? 0);
$sinceId = (int)($_POST['since_id'] ?? 0);

if ($chatId <= 0 || $bkoId <= 0) {
    echo json_encode(['ok' => false, 'messages' => [], 'last_id' => $sinceId]);
    exit;
}

$stmt = $PDO->prepare(
    'SELECT ci.id_chat FROM tbl_chat_info ci'
    .' INNER JOIN tbl_chat_fila cf ON cf.id_fila_chat = ci.fila_chat_id'
    .' WHERE ci.id_chat = ? AND ci.status_chat = 1 AND cf.bko_resp = ?'
    .' LIMIT 1'
);
$stmt->execute([$chatId, $bkoId]);
if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['ok' => false, 'messages' => [], 'last_id' => $sinceId, 'closed' => true]);
    exit;
}

$sql = 'SELECT a.id_msg,'
    .' a.data_hora,'
    .' a.rem_id,'
    .' (SELECT CONCAT(nome, \' \', sobrenome) FROM tbl_user WHERE id_user = a.rem_id) AS nome_rem,'
    .' (SELECT img FROM tbl_user_img_perfil WHERE user_id = a.rem_id) AS img,'
    .' a.msg'
    .' FROM tbl_chat_msg a'
    .' WHERE a.chat_id = ?';

$params = [$chatId];
if ($sinceId > 0) {
    $sql .= ' AND a.id_msg > ?';
    $params[] = $sinceId;
}
$sql .= ' ORDER BY a.id_msg ASC';

$stmt = $PDO->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$messages = [];
$lastId = $sinceId;

foreach ($rows as $row) {
    $msgId = (int)$row['id_msg'];
    $lastId = max($lastId, $msgId);
    $remId = (int)$row['rem_id'];
    if ($remId === 0) {
        $how = 'sys';
        $name = '';
    } elseif ($remId === $bkoId) {
        $how = 'me';
        $name = $row['nome_rem'] ?? '';
    } else {
        $how = 'other';
        $name = $row['nome_rem'] ?? '';
    }
    $messages[] = [
        'id_msg' => $msgId,
        'how' => $how,
        'name' => $name,
        'img' => $row['img'] ?? '',
        'msg' => $row['msg'] ?? '',
        'hora_msg' => stDashAcompFmtHora($row['data_hora'] ?? ''),
    ];
}

echo json_encode([
    'ok' => true,
    'messages' => $messages,
    'last_id' => $lastId,
]);
