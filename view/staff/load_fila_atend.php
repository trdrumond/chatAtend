<?php
include("../cnf/session.php");

$filaId = (int) ($_POST['fila'] ?? 0);
$stm = $PDO->prepare("SELECT nome_fila from tbl_config_fila where id_fila=?");
$stm->execute([$filaId]);
$fila = $stm->fetch(PDO::FETCH_ASSOC);
echo htmlspecialchars((string) ($fila['nome_fila'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
