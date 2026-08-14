<?php
include("../cnf/session.php");

//var_dump($_POST);

$idOption = (int) ($_POST['id'] ?? 0);

if(($_POST['status'] ?? '')!=''){
    $status=1;
} else {
    $status=0;
}

$sql="UPDATE tbl_servicos_input_option SET ativo=? where id_option=?";

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$status, $idOption]);

/*
if($result==1){
    echo "gravado";
}
*/

?>
