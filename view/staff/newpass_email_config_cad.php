<?php
include('../staff/newpass_email_config_envio.php');
include_once("config.php");

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
