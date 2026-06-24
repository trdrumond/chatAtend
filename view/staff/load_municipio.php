<?php
include("../cnf/session.php");

echo '<option value="">Município</option>';

$sqlUf="SELECT uf from tbl_estado where id_estado=".$_POST['uf'];
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sqlUf );
$result = $stmt->execute();
$uf = $stmt->fetch( PDO::FETCH_ASSOC );

$sql="SELECT id_municipio, nome_municipio from tbl_municipio where uf='".$uf['uf']."'";

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_municipio'].'">'.$dados[$x]['nome_municipio'].'</option>';
}
?>
