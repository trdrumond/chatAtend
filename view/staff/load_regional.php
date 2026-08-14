<?php
include("../cnf/session.php");

echo '<option value="">Regional</option>';

$contratoId = (int) ($_POST['contrato'] ?? 0);
if ($contratoId < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    return;
}

$sql = "SELECT id_regional, nome_regional from tbl_regional where ativo=1 and contrato_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$contratoId]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dados); $x++) {
    echo '<option value="' . htmlspecialchars((string) $dados[$x]['id_regional'], ENT_QUOTES, 'UTF-8') . '">'
        . htmlspecialchars((string) $dados[$x]['nome_regional'], ENT_QUOTES, 'UTF-8') . '</option>';
}
?>
