<?php
include("../cnf/session.php");


$stm = $PDO->query("SELECT nome_fila from tbl_config_fila where id_fila=".$_POST['fila']);
$fila = $stm->fetch(PDO::FETCH_ASSOC);
echo $fila['nome_fila']
?>
