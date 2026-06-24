<?php
include("../cnf/session.php");

echo '<option value="">Selecione a empresa...</option>';

$sql="SELECT id_empresa, nome_empresa from tbl_empresa where contrato_id=".$_POST['contrato'];
//echo "<br>".$sql;
//echo '<option value="">'.$sql.'</option>';
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_empresa'].'">'.$dados[$x]['nome_empresa'].'</option>';
}
?>