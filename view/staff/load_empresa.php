<?php
include("../cnf/session.php");

echo '<option value="">Empresa</option>';

$contratoId = (int) ($_POST['contrato'] ?? 0);
if ($contratoId < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    return;
}

$sql = "SELECT id_empresa, nome_empresa from tbl_empresa where ativo=1 and contrato_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$contratoId]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dados); $x++) {
    echo '<option value="' . (int) $dados[$x]['id_empresa'] . '">' . stHtml($dados[$x]['nome_empresa']) . '</option>';
}
?>
