<?php

include("../staff/newpass_email_config.php");
require_once("../staff/phpmailer/class.phpmailer.php");

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

if ($enviado) {
  echo '<br><br><center class="a-reset"><div id="error" class="alert alert-success" role="alert"><h6>Sua nova senha foi enviada.<br>Verifique seu e-mail.</h6></div></center>';
} else {
  echo '<br><br><center class="a-reset"><h6>Não foi possível enviar sua nova senha agora.<br>Tente novamente daqui alguns minutos.</h6></div></center>';
}
