<?php

include("../staff/newpass_email_config.php");
require_once("../staff/phpmailer/class.phpmailer.php");

$mail = new PHPMailer();

$mail->IsSMTP();
$mail->Port =$port;
$mail->Host = $host;
$mail->SMTPAuth = true;
$mail->Username = $caixaPostalServidorUser;;
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


if ($enviado) {
  echo '<br><br><center class="a-reset"><div id="error" class="alert alert-success" role="alert"><h6>Sua nova senha foi enviada.<br>Verifique seu e-mail.</h6></div></center>';
} else {
  echo '<br><br><center class="a-reset"><h6>Não foi possível enviar sua nova senha agora.<br>Tente novamente daqui alguns minutos.</h6></div></center>';
  //echo "<b>Informações do erro:</b> " . $mail->ErrorInfo;
}



