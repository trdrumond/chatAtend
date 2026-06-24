<?php
include("../cnf/session.php");

var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

$sql="UPDATE tbl_fila_horario SET ativo=$status where id_hr=".$_POST['id'];

echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();


if($result==1){
    echo "gravado";
}


?>
