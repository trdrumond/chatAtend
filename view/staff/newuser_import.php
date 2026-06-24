<?php
include_once("config.php");

$destinatarioNome = 'SOLVETASK';
$caixaPostalServidorNome = 'Solvetask';
$caixaPostalServidorEmail = 'naoresponda@logos-ma.com.br';
$caixaPostalServidorSenha = 'sogol.ti';
$host = "email-ssl.com.br";
$port = 587;
//echo "<BR>tESTE 3";


$assunto ='SOLVETASK '.$config_sis['titulo_sistema'].' - CADASTRO';

$conteudo ='Olá <b>'.$nome.'</b>,<br><br>
    Seguem suas informações de acesso ao sistema Solvetask:<br><br>
    Nome: <b>'.$nome.'</b><br>
    Contrato: <b>'.$contrato.'</b><br>
    Município: <b>'.$municipio.'</b><br>
    Regional: <b>'.$regional.'</b><br>
    Agência: <b>'.$agencia.'</b><br>
    Login: <b>'.$login.'</b><br>
    Senha Inicial: <b>'.$pass.'</b> (será necessário modificar esta senha em seu primeiro acesso)<br>
    E-mail: <b>'.$email.'</b><br>
    <br><br>
    Para acessar o sistema entre no endereço: <br>
    https://solvetask.logos-ma.com.br



    <br><br><br><br><br><br><center>********** Não responda este e-mail **********</center>
    ';
    //echo "<BR>tESTE 4";



include_once("../staff/phpmailer/class.phpmailer.php");
//echo "<BR>tESTE 5";
//echo "<br>".$conteudo;

$mail = new PHPMailer();
$mail->Charset   = 'ISO-8859-1';
$mail->IsSMTP(); // Define que a mensagem será SMTP
$mail->SMTPDebug = 1;
$mail->Port = 587; //Indica a porta de conexão
	//$mail->Host = "smtplw.com.br"; // Endereço do servidor SMTP
$mail->Host = $host; // Endereço do servidor SMTP
$mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)
$mail->Username = $caixaPostalServidorUser; // Usuário do servidor SMTP
$mail->Password = $caixaPostalServidorSenha; // Senha do servidor SMTP
$mail->From = $caixaPostalServidorEmail; // Seu e-mail
$mail->FromName = "SOLVETASK - CADASTRO"; // Seu nome
$mail->IsHTML(true);
$mail->Subject  = utf8_decode($assunto);
$mail->Body  = utf8_decode($conteudo);
$mail->AddAddress($email);
//echo "<BR>tESTE 6";
//print_r($mail->Subject);
//print_r($mail->Body);
$mail->send();

/*
if(!$mail->send()){
    echo '<br>Erro ao enviar E-mail: '. print_r($mail->ErrorInfo); exit;
} else {
    echo "<br>Mensagem enviada com sucesso!";
}
*/


