<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

$sql="UPDATE tbl_forms_pos_input_option SET ativo=$status where id_option=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

/*
if($result==1){
    echo "gravado";
}
*/

?>
