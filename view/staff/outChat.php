<?php
//include("../cnf/session.php");

//depurador($_POST);
//remetente, destinatario, contrato, tokenChat, mensagem

$txt = '-'.$_POST['chatId'].
$txt .= ' - '.$_POST['destinatario'];
$txt .= ' - '.$_POST['contrato'];
$txt .= ' - '.$_POST['tokenChat'];
$txt .= ' - '.$_POST['mensagem'];


file_put_contents("outChat.txt", $txt);
