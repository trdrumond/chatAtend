<?php

include("../staff/newpass_email_config_cad.php");
require_once("../staff/phpmailer/class.phpmailer.php");

$enviado = false;

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return;
}

$mail = new PHPMailer();
solvetask_configure_mailer($mail);
$mail->addAddress($email, isset($nome) ? (string) $nome : '');
$mail->isHTML(true);
$mail->Subject = solvetask_encode_subject(isset($assunto) ? $assunto : '');
$mail->Body = isset($conteudo) ? (string) $conteudo : '';
$mail->AltBody = solvetask_html_to_text($mail->Body);

$enviado = $mail->send();
$mail->clearAllRecipients();
$mail->clearAttachments();
