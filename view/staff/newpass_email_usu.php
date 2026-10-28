<?php

include("../staff/newpass_email_config.php");
require_once("../staff/phpmailer/class.phpmailer.php");

$email = isset($email) ? (string) $email : '';
$assunto = isset($assunto) ? (string) $assunto : '';
$conteudo = isset($conteudo) ? (string) $conteudo : '';
$enviado = false;

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return;
}

$mail = new PHPMailer();
solvetask_configure_mailer($mail);
$mail->Timeout = 20;
$mail->addAddress($email, isset($nome) ? (string) $nome : '');
$mail->isHTML(true);
$mail->Subject = solvetask_encode_subject($assunto);
$mail->Body = $conteudo;
$mail->AltBody = solvetask_html_to_text($conteudo);
$enviado = $mail->send();
$mail->clearAllRecipients();
$mail->clearAttachments();
