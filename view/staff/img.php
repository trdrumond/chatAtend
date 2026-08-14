<?php
include("../cnf/session.php");

$chatId = (int) ($_GET['id'] ?? 0);
$chave = (string) ($_GET['key'] ?? '');

$stmt = $PDO->prepare("SELECT src from tbl_img where chat_id=? and chave=?");
$stmt->execute([$chatId, $chave]);
$infoImg = $stmt->fetch(PDO::FETCH_ASSOC);
$src = is_array($infoImg) ? (string) ($infoImg['src'] ?? '') : '';

echo '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" style="width: 100%">';
