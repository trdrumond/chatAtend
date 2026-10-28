<?php
/**
 * Configuração SMTP — compartilhada pelos envios do sistema (piloto_2.0).
 * Credenciais locais preservadas; helpers compatíveis PHP 7.3–8.4.
 */
$destinatarioNome = 'SOLVETASK';
$caixaPostalServidorNome = 'Solvetask';
$caixaPostalServidorEmail = 'naoresponda@logos-ma.com.br';
$caixaPostalServidorUser = 'maillogos';
$caixaPostalServidorSenha = 'YhXYiPVX4160';
$host = 'smtplw.com.br';
$port = 587;
$urlSistema = 'https://solvetask.logos-ma.com.br';

/*
$caixaPostalServidorEmail = 'naoresponda@logos-ma.com.br';
$caixaPostalServidorSenha = 'sogol.ti';
$host = "email-ssl.com.br";
$port = 587;
*/

/**
 * Aplica configuração SMTP no PHPMailer 6.x (PHP 7.3–8.4).
 * Porta 587 sem TLS explícito — alinhado ao ambiente Locaweb/smtplw.
 *
 * @param PHPMailer $mail
 * @param string|null $replyToEmail
 * @param string|null $replyToName
 */
if (!function_exists('solvetask_configure_mailer')) {
function solvetask_configure_mailer($mail, $replyToEmail = null, $replyToName = null)
{
    global $host, $port, $caixaPostalServidorUser, $caixaPostalServidorSenha;
    global $caixaPostalServidorEmail, $caixaPostalServidorNome;

    $mail->isSMTP();
    $mail->Host = $host;
    $mail->Port = (int) $port;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = '';
    $mail->Username = $caixaPostalServidorUser;
    $mail->Password = $caixaPostalServidorSenha;

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'quoted-printable';

    $mail->setFrom($caixaPostalServidorEmail, $caixaPostalServidorNome, true);

    if ($replyToEmail) {
        $mail->addReplyTo($replyToEmail, $replyToName ? $replyToName : $caixaPostalServidorNome);
    }

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ),
    );
}
}

/**
 * Codifica o Subject em MIME Base64 (UTF-8 seguro em 7.3 e 8.4).
 */
if (!function_exists('solvetask_encode_subject')) {
function solvetask_encode_subject($assunto)
{
    $assunto = trim((string) $assunto);
    if ($assunto === '') {
        return 'Solvetask';
    }
    return '=?UTF-8?B?' . base64_encode($assunto) . '?=';
}
}

/**
 * Gera versão texto puro a partir do HTML (AltBody).
 */
if (!function_exists('solvetask_html_to_text')) {
function solvetask_html_to_text($html)
{
    $html = (string) $html;
    $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $text = preg_replace('/<\/p>/i', "\n\n", $text);
    $text = preg_replace('/<\/tr>/i', "\n", $text);
    $text = preg_replace('/<\/(h[1-6]|div|li)>/i', "\n", $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace("/[ \t]+/", ' ', $text);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
    return trim((string) $text);
}
}
