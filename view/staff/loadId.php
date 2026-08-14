<?php
include("../cnf/session.php");

$sql = "SELECT id_fila_chat, (SELECT id_chat from tbl_chat_info where fila_chat_id=id_fila_chat) as chat_id from tbl_chat_fila where protocolo=? order by id_fila_chat desc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([(string) ($_POST['protocolo'] ?? '')]);
$infoId = $stmt->fetch(PDO::FETCH_ASSOC);
$chatId = $infoId['chat_id'] ?? '';
?>
<script>
    var chat_id = <?= json_encode((string) $chatId, JSON_UNESCAPED_UNICODE) ?>;
</script>
