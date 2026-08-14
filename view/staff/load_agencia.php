<?php
include("../cnf/session.php");

echo '<option value="">Agência</option>';

$regionalId = (int) ($_POST['regional'] ?? 0);
if ($regionalId < 1) {
    return;
}

$stmtReg = $PDO->prepare("SELECT contrato_id from tbl_regional where id_regional=?");
$stmtReg->execute([$regionalId]);
$reg = $stmtReg->fetch(PDO::FETCH_ASSOC);
if (!is_array($reg) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($reg['contrato_id'] ?? 0))) {
    return;
}

$sql = "SELECT id_agencia, nome_agencia from tbl_agencia where ativo=1 and regional_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$regionalId]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dados); $x++) {
    echo '<option value="' . (int) $dados[$x]['id_agencia'] . '">' . stHtml($dados[$x]['nome_agencia']) . '</option>';
}
?>
