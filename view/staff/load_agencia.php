<?php
include("../cnf/session.php");

echo '<option value="">Agência</option>';


$sql="SELECT id_agencia, nome_agencia from tbl_agencia where ativo=1";

//echo '<option value="">'.$sql.'</option>';

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_agencia'].'">'.$dados[$x]['nome_agencia'].'</option>';
}
?>
