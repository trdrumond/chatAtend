<?php
include("../cnf/session.php");

echo '<option value="">Contrato</option>';

$params = [];
$sql = "SELECT id_contrato, nome_contrato from tbl_contrato where ativo=1";

$idEstado = (int) ($_POST['uf'] ?? 0);
if ($idEstado > 0) {
    $stmtUf = $PDO->prepare("SELECT uf from tbl_estado where id_estado=?");
    $stmtUf->execute([$idEstado]);
    $uf = $stmtUf->fetch(PDO::FETCH_ASSOC);
    if (!is_array($uf) || ($uf['uf'] ?? '') === '') {
        return;
    }
    $sql .= " and uf=?";
    $params[] = (string) $uf['uf'];
}

if ((int) ($infoUser['nivel_id'] ?? 0) !== 0) {
    $cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql .= " and id_contrato IN (" . $cttIn['ph'] . ")";
    $params = array_merge($params, $cttIn['params']);
}

$sql .= " order by nome_contrato";
$stmt = $PDO->prepare($sql);
$stmt->execute($params);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($x = 0; $x < count($dados); $x++) {
    echo '<option value="' . (int) $dados[$x]['id_contrato'] . '">' . stHtml($dados[$x]['nome_contrato']) . '</option>';
}
?>
