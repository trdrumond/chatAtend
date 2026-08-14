<?php
require_once __DIR__ . '/../cnf/session.php';

$msg = (string) ($_POST['msg'] ?? '');
$chatId = (int) ($_POST['chat_id'] ?? 0);

echo stChatRenderPostedMsg($msg, $chatId, $PDO);
