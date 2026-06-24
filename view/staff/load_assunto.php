<?php
include("../cnf/session.php");


if($_GET['op']=='men'){
    echo '<option value="">Todos</option>';
}


$sql="SELECT id_assunto, titulo_assunto from tbl_assunto where ativo=1 and contrato_id=".$_POST['contrato']." order by titulo_assunto asc";

//echo $sql;
//echo '<option value="">'.$sql.'</option>';
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_assunto'].'">'.$dados[$x]['titulo_assunto'].'</option>';
}
?>
