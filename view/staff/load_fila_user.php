<?php
include("../cnf/session.php");

echo '<option value="">Fila</option>';

$sql="SELECT id_fila, nome_fila from tbl_config_fila where contrato_id=".$_POST['contrato']."  order by nome_fila asc";

//echo $sql;
//echo '<option value="">'.$sql.'</option>';
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_fila'].'">'.$dados[$x]['nome_fila'].'</option>';
}
?>
