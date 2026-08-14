<?php
include("../cnf/session.php");

$params = [(int) $infoUser['id_user']];
$qry = '';
$qryNivel = '';

if($infoUser['nivel_id']!=0){
    $qry=" and contrato_id=?";
    $params[] = (int) $infoUser['contrato_id'];
}

if(($_POST['nivel'] ?? '')!=''){
    $qryNivel = "and nivel_id=?";
    $params[] = (int) $_POST['nivel'];
}

$sql="SELECT id_user, concat(nome, ' ', sobrenome) as nome_col from tbl_user where id_user<>? $qry $qryNivel order by nome_col asc";

//echo $sql;
//echo '<option value="">'.$sql.'</option>';
if(($_POST['nivel'] ?? '')==''){
    echo '<option>Colaborador</option>';
}
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute($params);
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.(int) $dados[$x]['id_user'].'">'.stHtml(ucwords(strtolower((string) $dados[$x]['nome_col']))).'</option>';
}
?>
