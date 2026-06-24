<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

/*

$very_1="SELECT campo_id from tbl_forms_mon_input_option where id_option=".$_POST['id'];
$stmt = $PDO->prepare( $very_1 );
$result = $stmt->execute();
$dd = $stmt->fetch( PDO::FETCH_ASSOC );

$sql="UPDATE tbl_forms_mon_input_option SET opt_correta=0 where campo_id=".$dd['campo_id'];
//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
*/

$sql="UPDATE tbl_forms_mon_input_option SET opt_correta=$status where id_option=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

/*
if($result==1){
    echo "gravado";
}
*/

?>
