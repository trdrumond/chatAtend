<?php
include("../cnf/session.php");

echo '<option value="">Empresa</option>';

$sql="SELECT id_empresa, nome_empresa from tbl_empresa where ativo=1";

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    echo '<option value="'.$dados[$x]['id_empresa'].'">'.$dados[$x]['nome_empresa'].'</option>';
}
?>
