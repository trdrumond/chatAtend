<?php
include("../cnf/session.php");

$msg = (string) ($_POST['msg'] ?? '');

if (($_POST['how'] ?? '') === 'other') {
    $sqlVisual = "UPDATE tbl_com_msg SET dt_visual=now() where dt_visual is null and com_id=? and dest_id=?";
    $stmt = $PDO->prepare($sqlVisual);
    $stmt->execute([(int) ($_POST['chatId'] ?? 0), (int) $infoUser['id_user']]);
}

echo stChatRenderPostedMsg($msg, 0, null);
