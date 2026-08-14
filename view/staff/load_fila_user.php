<?php
include("../cnf/session.php");

echo '<option value="">Fila</option>';
$contrato = (int) ($_POST['contrato'] ?? 0);
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
    return;
}
$stmt = $PDO->prepare("SELECT id_fila, nome_fila from tbl_config_fila where contrato_id=? order by nome_fila asc");
$stmt->execute([$contrato]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dados); $x++) {
    echo '<option value="' . htmlspecialchars((string) $dados[$x]['id_fila'], ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars((string) $dados[$x]['nome_fila'], ENT_QUOTES, 'UTF-8') . '</option>';
}
?>
