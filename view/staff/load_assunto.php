<?php
include("../cnf/session.php");

if (($_GET['op'] ?? '') === 'men') {
    echo '<option value="">Todos</option>';
}

$contratoId = (int) ($_POST['contrato'] ?? 0);
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    return;
}

$sql = "SELECT id_assunto, titulo_assunto from tbl_assunto where ativo=1 and contrato_id=? order by titulo_assunto asc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$contratoId]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dados); $x++) {
    echo '<option value="' . (int) $dados[$x]['id_assunto'] . '">' . stHtml($dados[$x]['titulo_assunto']) . '</option>';
}
?>

