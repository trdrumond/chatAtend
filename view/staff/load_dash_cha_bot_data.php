<?php
include('../cnf/session.php');

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');
$contratoId = (int) ($infoUser['contrato_id'] ?? 0);
$nomeUsuario = trim((string) ($infoUser['nome'] ?? ''));
if ($nomeUsuario === '') {
    $nomeUsuario = trim((string) ($infoUser['nome_usuario'] ?? 'visitante'));
}

$nomeRobo = 'Robô Logos';
if ($contratoId > 0) {
    $stmtRobo = $PDO->prepare('SELECT nome_robo FROM tbl_contrato WHERE id_contrato = ? LIMIT 1');
    $stmtRobo->execute([$contratoId]);
    $rowRobo = $stmtRobo->fetch(PDO::FETCH_ASSOC);
    if ($rowRobo && trim((string) ($rowRobo['nome_robo'] ?? '')) !== '') {
        $nomeRobo = trim((string) $rowRobo['nome_robo']);
    }
}

$filas = [];
$sql = 'SELECT id_fila, nome_fila FROM tbl_config_fila WHERE ativo = 1 AND contrato_id IN (' . (int) $infoUser['contrato_id'] . ') ORDER BY nome_fila ASC';$stmt = $PDO->prepare($sql);
$stmt->execute();
$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filaPadrao = (int) ($infoUser['fila_id'] ?? 0);

echo json_encode([
    'ok' => true,
    'nome_robo' => $nomeRobo,
    'nome_usuario' => $nomeUsuario,
    'fila_padrao' => $filaPadrao,
    'filas' => array_map(static function ($row) {
        return [
            'id' => (int) $row['id_fila'],
            'nome' => (string) $row['nome_fila'],
        ];
    }, $filas),
], JSON_UNESCAPED_UNICODE);
