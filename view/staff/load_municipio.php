<?php
include("../cnf/session.php");

echo '<option value="">Município</option>';

$idEstado = (int) ($_POST['uf'] ?? 0);

$sqlUf="SELECT uf from tbl_estado where id_estado=?";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sqlUf );
$result = $stmt->execute([$idEstado]);
$uf = $stmt->fetch( PDO::FETCH_ASSOC );

if (!is_array($uf) || ($uf['uf'] ?? '') === '') {
    return;
}

$sql="SELECT id_municipio, nome_municipio from tbl_municipio where uf=?";

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([(string) $uf['uf']]);
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.(int) $dados[$x]['id_municipio'].'">'.stHtml($dados[$x]['nome_municipio']).'</option>';
}
?>
