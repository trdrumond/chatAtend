<?php
include('../staff/newpass_email_config_envio.php');
include_once("config.php");

$assunto ='SOLVETASK '.$config_sis['titulo_sistema'].' - NOVA SENHA';

$conteudo ='Olá <b>'.$nome.'</b>,<br><br>
    Foi solicitada uma nova senha para o seu usuário (<strong>'.$login.'</strong>) no sistema Solvetask.<br><br>
    Sua nova senha é <b>'.$novaSenha.'</b><br><br>
    Acesse o sistema utilizando seu nome de usuário e a senha enviada neste e-mail.<br><br>
    https://solvetask.logos-ma.com.br <br>
    <br><br><br><br><br><br><center>********** Não responda este e-mail **********</center>
    ';
