<?php
include("../cnf/session.php");

$msg = (string) ($_POST['msg'] ?? '');
$chatId = (int) ($_POST['chatId'] ?? 0);

$sqlVisual = "UPDATE tbl_com_msg_group_view SET dt_view=now() where group_chat=? and user_id=?";
$stmt = $PDO->prepare($sqlVisual);
$stmt->execute([$chatId, (int) $infoUser['id_user']]);

echo stChatRenderPostedMsg($msg, 0, null);
?>

<script>
loadComList();
</script>
