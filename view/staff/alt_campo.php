<?php
include("../cnf/session.php");

//var_dump($_POST);

$idCampo = (int) ($_POST['id'] ?? 0);

if(($_POST['status'] ?? '')!=''){
    $status=1;
} else {
    $status=0;
}

$sql="UPDATE tbl_servicos_input_campo SET ativo=? where id_campo=?";

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$status, $idCampo]);

/*
if($result==1){
    echo "gravado";
}
*/

?>
