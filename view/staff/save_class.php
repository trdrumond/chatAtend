<?php
include(__DIR__ . '/../cnf/session.php');

/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

$tokenChat = isset($_POST['tokenChat']) ? trim((string)$_POST['tokenChat']) : '';
$star = isset($_POST['star']) ? (int)$_POST['star'] : 0;

if ($tokenChat === '') {
    echo json_encode(['ok' => false, 'msg' => 'Sessão do chat inválida.']);
    exit;
}

if ($star < 1 || $star > 5) {
    echo json_encode(['ok' => false, 'msg' => 'Selecione de 1 a 5 estrelas para classificar o atendimento.']);
    exit;
}

$stmt = $PDO->prepare(
    'SELECT id_chat, fila_chat_id, rem_chat, dest_chat FROM tbl_chat_info WHERE token_chat=? LIMIT 1'
);
$stmt->execute([$tokenChat]);
$infoChat = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($infoChat['id_chat'])) {
    echo json_encode(['ok' => false, 'msg' => 'Chat não encontrado para registrar a classificação.']);
    exit;
}

$solId = (int)$infoChat['dest_chat'];
$bkoUserId = (int)$infoChat['rem_chat'];
$chatId = (int)$infoChat['id_chat'];
$filaChatId = (int)$infoChat['fila_chat_id'];

$stmt = $PDO->prepare(
    'SELECT id_class FROM tbl_classificacao WHERE chat_fila_id=? ORDER BY id_class DESC LIMIT 1'
);
$stmt->execute([(string)$filaChatId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!empty($existing['id_class'])) {
    $stmt = $PDO->prepare(
        'UPDATE tbl_classificacao SET chat_id=?, bko=?, ate=?, star=?, data_hora=NOW() WHERE id_class=?'
    );
    $ok = $stmt->execute([(string)$chatId, (string)$solId, (string)$bkoUserId, (string)$star, (int)$existing['id_class']]);
} else {
    $stmt = $PDO->prepare(
        'INSERT INTO tbl_classificacao (chat_id, chat_fila_id, bko, ate, star) VALUES (?, ?, ?, ?, ?)'
    );
    $ok = $stmt->execute([(string)$chatId, (string)$filaChatId, (string)$solId, (string)$bkoUserId, (string)$star]);
}

if (!$ok) {
    echo json_encode(['ok' => false, 'msg' => 'Não foi possível gravar a classificação. Tente novamente.']);
    exit;
}

echo json_encode(['ok' => true, 'msg' => 'Classificação registrada com sucesso.']);
