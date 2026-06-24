<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

$sql="UPDATE tbl_forms_mon_input_campo_cnf SET ativo=$status where campo_id=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

/*
if($result==1){
    echo "gravado";
}
*/

?>
