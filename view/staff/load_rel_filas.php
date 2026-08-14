<?php

include __DIR__ . '/../cnf/session.php';

header('Content-Type: text/html; charset=utf-8');

$contratoId = (int) ($_POST['contrato'] ?? $_GET['contrato'] ?? 0);

echo '<option value="">Selecione a fila...</option>';

if ($contratoId <= 0) {
    exit;
}

if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    exit;
}

$sql = 'SELECT id_fila, nome_fila FROM tbl_config_fila WHERE contrato_id = ? ORDER BY nome_fila ASC';
$stmt = $PDO->prepare($sql);
$stmt->execute([$contratoId]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($dados as $row) {
    echo '<option value="' . (int) $row['id_fila'] . '">'
        . htmlspecialchars((string) $row['nome_fila'], ENT_QUOTES, 'UTF-8')
        . '</option>';
}
