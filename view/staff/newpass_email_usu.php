<?php

include("../staff/newpass_email_config.php");
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

/*
if ($enviado) {
  echo '<br><br><center class="a-reset"><h6>Sua nova senha foi enviada.<br>Verifique seu e-mail.</h6>';
} else {
  echo '<br><br><center class="a-reset"><h6>Não foi possível enviar sua nova senha, faça contato com seu gestor informando o Faça um print dessa tela e envie para nossa equipe do TI para que possamos verificar o que ocorre.</h6>';
  //echo "<b>Informações do erro:</b> " . $mail->ErrorInfo;
}
*/