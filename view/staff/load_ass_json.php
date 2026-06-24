<?php
include('../cnf/session.php');

/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

$filaId = (int) ($_POST['fila'] ?? $_GET['fila'] ?? 0);

if ($filaId <= 0) {
    echo json_encode(['ok' => false, 'assuntos' => [], 'msg' => 'Fila inválida.']);
    exit;
}

$stm = $PDO->prepare('SELECT assuntos_id FROM tbl_config_fila WHERE id_fila = ? AND ativo = 1 LIMIT 1');
$stm->execute([$filaId]);
$ass = $stm->fetch(PDO::FETCH_ASSOC);

if (!$ass || trim((string) ($ass['assuntos_id'] ?? '')) === '') {
    echo json_encode(['ok' => true, 'assuntos' => []]);
    exit;
}

$assuntosRaw = array_filter(array_map('intval', explode(',', (string) $ass['assuntos_id'])));
if (count($assuntosRaw) === 0) {
    echo json_encode(['ok' => true, 'assuntos' => []]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($assuntosRaw), '?'));
$sql = "SELECT id_assunto, titulo_assunto FROM tbl_assunto WHERE ativo = 1 AND id_assunto IN ($placeholders) ORDER BY titulo_assunto ASC";
$stmt = $PDO->prepare($sql);
$stmt->execute(array_values($assuntosRaw));
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'ok' => true,
    'assuntos' => array_map(static function ($row) {
        return [
            'id' => (int) $row['id_assunto'],
            'nome' => (string) $row['titulo_assunto'],
        ];
    }, $dados),
], JSON_UNESCAPED_UNICODE);
