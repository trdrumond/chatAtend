<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
$valor = (int) ($_POST['valor'] ?? 0);

$sql = "UPDATE tbl_forms_mon_input_option SET valor_mon_option=? where id_option=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$valor, $id]);

?>
