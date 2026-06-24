<?php
// Variáveis esperadas pelo cadastro:
// $nome, $email, $login, $contrato, $municipio, $regional, $agencia, $pass
// $PDO (conexão já aberta pelo conn da API)

include(dirname(__FILE__) . "/../staff/newpass_email_config_envio.php");

$tituloSistema = 'SolveTask';
if (isset($PDO) && $PDO instanceof PDO) {
    try {
        $stmtCfg = $PDO->prepare("SELECT titulo_sistema FROM tbl_config_sis LIMIT 1");
        $stmtCfg->execute();
        $rowCfg = $stmtCfg->fetch(PDO::FETCH_ASSOC);
        if ($rowCfg && !empty($rowCfg['titulo_sistema'])) {
            $tituloSistema = (string) $rowCfg['titulo_sistema'];
        }
    } catch (Exception $e) {
        // mantém título padrão
    }
}

$assunto = 'SOLVETASK ' . $tituloSistema . ' - CADASTRO';
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
    <br><br><br><br><br><br><center>********** Não responda este e-mail **********</center>';

require_once(dirname(__FILE__) . "/../staff/phpmailer/class.phpmailer.php");

$enviado = false;
$erroEmail = null;

$dest = trim((string) $email);
if ($dest === '' || !filter_var($dest, FILTER_VALIDATE_EMAIL)) {
    $erroEmail = 'E-mail de destino inválido.';
} else {
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->SMTPAuth = true;
    $mail->Host = $host;
    $mail->Port = (int) $port;
    if ($mail->Port === 587) {
        $mail->SMTPSecure = 'tls';
    } elseif ($mail->Port === 465) {
        $mail->SMTPSecure = 'ssl';
    }
    $mail->Timeout = 30;
    $mail->Username = $caixaPostalServidorUser;
    $mail->Password = $caixaPostalServidorSenha;
    $mail->From = $caixaPostalServidorEmail;
    $mail->FromName = $caixaPostalServidorNome;
    $mail->IsHTML(true);
    $mail->Subject = $assunto;
    $mail->Body = $conteudo;
    $mail->AddAddress($dest);

    $okSend = $mail->Send();
    $enviado = ($okSend === true);
    if (!$enviado) {
        $erroEmail = trim((string) $mail->ErrorInfo);
        if ($erroEmail === '') {
            $erroEmail = 'Falha ao enviar e-mail (SMTP).';
        }
    }

    $mail->ClearAllRecipients();
    $mail->ClearAttachments();
}
