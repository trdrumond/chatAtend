<?php
include("../cnf/session.php");

//var_dump($_POST);


$sql="UPDATE tbl_forms_mon_input_option SET valor_mon_option=".$_POST['valor']." where id_option=".$_POST['id'];

echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

/*
if($result==1){
    echo "gravado";
}
*/

?>
