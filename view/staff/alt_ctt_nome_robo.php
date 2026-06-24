<?php
include('../cnf/session.php');
require_once __DIR__ . '/../cnf/cache_layout.php';

/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);
$nomeRobo = trim((string) ($_POST['nome_robo'] ?? ''));

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Contrato inválido.']);
    exit;
}

if (mb_strlen($nomeRobo) > 80) {
    $nomeRobo = mb_substr($nomeRobo, 0, 80);
}

$stmt = $PDO->prepare('UPDATE tbl_contrato SET nome_robo = :nome WHERE id_contrato = :id');
$ok = $stmt->execute([
    ':nome' => $nomeRobo !== '' ? $nomeRobo : null,
    ':id' => $id,
]);

if ($ok) {
    clearLayoutCacheByContrato($PDO, $id);
}

echo json_encode([
    'ok' => (bool) $ok,
    'nome_robo' => $nomeRobo !== '' ? $nomeRobo : 'Robô Logos',
]);
