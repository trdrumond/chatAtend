<?php
include("../cnf/session.php");

$chatId = (int) ($_POST['chatId'] ?? 0);
$dest = (int) ($_POST['destinatario'] ?? 0);
$contrato = (int) ($_POST['contrato'] ?? 0);
$tokenChat = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($_POST['tokenChat'] ?? ''));
$mensagem = substr((string) ($_POST['mensagem'] ?? ''), 0, 500);

$txt = '-' . $chatId;
$txt .= ' - ' . $dest;
$txt .= ' - ' . $contrato;
$txt .= ' - ' . $tokenChat;
$txt .= ' - ' . $mensagem;

file_put_contents(__DIR__ . '/outChat.txt', $txt);
