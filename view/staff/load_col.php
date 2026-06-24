<?php
include("../cnf/session.php");

if($infoUser['nivel_id']!=0){
    $qry=" and contrato_id=".$infoUser['contrato_id'];
}

if($_POST['nivel']!=''){
    $qryNivel = "and nivel_id=".$_POST['nivel'];
}

$sql="SELECT id_user, concat(nome, ' ', sobrenome) as nome_col from tbl_user where id_user<>".$infoUser['id_user']." $qry $qryNivel order by nome_col asc";

//echo $sql;
//echo '<option value="">'.$sql.'</option>';
if($_POST['nivel']==''){
    echo '<option>Colaborador</option>';
}
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_user'].'">'.ucwords(strtolower($dados[$x]['nome_col'])).'</option>';
}
?>
