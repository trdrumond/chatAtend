<?php
include("../cnf/session.php");

echo '<option value="">Regional</option>';

$sql="SELECT id_regional, nome_regional from tbl_regional where ativo=1 and contrato_id=".$_POST['contrato'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_regional'].'">'.$dados[$x]['nome_regional'].'</option>';
}
?>
