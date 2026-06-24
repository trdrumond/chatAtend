<?php
include("../cnf/session.php");

echo '<option value="">Contrato</option>';

/*
$sqlUf="SELECT uf from tbl_estado".$_POST['uf'];
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sqlUf );
$result = $stmt->execute();
$uf = $stmt->fetch( PDO::FETCH_ASSOC );
*/

//$sql="SELECT id_contrato, nome_contrato from tbl_contrato where ativo=1 and uf='".$uf['uf']."'";
$sql="SELECT id_contrato, nome_contrato from tbl_contrato where ativo=1";

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_contrato'].'">'.$dados[$x]['nome_contrato'].'</option>';
}
?>
