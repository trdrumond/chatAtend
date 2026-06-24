<?php

include("../staff/newpass_email_config_cad.php");
require_once("../staff/phpmailer/class.phpmailer.php");

$mail = new PHPMailer();

$mail->IsSMTP();
$mail->Port =$port;
$mail->Host = $host;
$mail->SMTPAuth = true;
$mail->Username = $caixaPostalServidorUser;
$mail->Password = $caixaPostalServidorSenha;
$mail->From = $caixaPostalServidorEmail;
$mail->FromName = $caixaPostalServidorNome;
$mail->AddAddress($email);
$mail->IsHTML(true);
$mail->Subject  = utf8_decode($assunto);
$mail->Body =   utf8_decode($conteudo);
$enviado = $mail->Send();
$mail->ClearAllRecipients();
$mail->ClearAttachments();