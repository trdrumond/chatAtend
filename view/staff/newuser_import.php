<?php
include_once("config.php");
require_once dirname(__FILE__) . '/phpmailer/class.phpmailer.php';
require_once dirname(__FILE__) . '/newpass_email_config_envio.php';

$assunto = 'SOLVETASK ' . $config_sis['titulo_sistema'] . ' - CADASTRO';

$conteudo = 'Olá <b>' . $nome . '</b>,<br><br>
    Seguem suas informações de acesso ao sistema Solvetask:<br><br>
    Nome: <b>' . $nome . '</b><br>
    Contrato: <b>' . $contrato . '</b><br>
    Município: <b>' . $municipio . '</b><br>
    Regional: <b>' . $regional . '</b><br>
    Agência: <b>' . $agencia . '</b><br>
    Login: <b>' . $login . '</b><br>
    Senha Inicial: <b>' . $pass . '</b> (será necessário modificar esta senha em seu primeiro acesso)<br>
    E-mail: <b>' . $email . '</b><br>
    <br><br>
    Para acessar o sistema entre no endereço: <br>
    https://solvetask.logos-ma.com.br
    <br><br><br><br><br><br><center>********** Não responda este e-mail **********</center>
    ';

$mail = new PHPMailer();
solvetask_configure_mailer($mail);
$mail->setFrom($caixaPostalServidorEmail, 'SOLVETASK - CADASTRO', true);
$mail->isHTML(true);
$mail->Subject = solvetask_encode_subject($assunto);
$mail->Body = (string) $conteudo;
$mail->AltBody = solvetask_html_to_text($conteudo);
$mail->addAddress((string) $email);
$mail->send();
