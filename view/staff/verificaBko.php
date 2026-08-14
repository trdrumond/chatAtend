<?php
include("../cnf/session.php");

$sql = "SELECT bko_resp from tbl_chat_fila where id_fila_chat=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([(int) ($_POST['id'] ?? 0)]);
$infoChat = $stmt->fetch(PDO::FETCH_ASSOC);
if (($infoChat['bko_resp'] ?? '') != ($_POST['resp_id'] ?? '')) {
?>
<script>
    document.location.reload(true);
</script>

<?php } ?>
