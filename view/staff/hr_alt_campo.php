<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

if ($id < 1) {
    return;
}

$sql = "UPDATE tbl_fila_horario SET ativo=? where id_hr=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$status, $id]);

if ($result == 1) {
    echo "gravado";
}

?>

