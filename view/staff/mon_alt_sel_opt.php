<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$sql = "UPDATE tbl_forms_mon_input_option SET opt_correta=? where id_option=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$status, $id]);

?>
