<?php
include("../cnf/session.php");

$userIdPost = (int) ($_POST['id'] ?? 0);

if ($userIdPost < 1) {
    return;
}

$sql = "SELECT id_user, nome, sobrenome, email, nome_usuario, contrato_id, (SELECT nome_contrato from tbl_contrato where id_contrato=contrato_id) as contrato, municipio_id, (SELECT nome_municipio from tbl_municipio where id_municipio=municipio_id) as municipio, regional_id, (SELECT nome_regional from tbl_regional where id_regional=regional_id) as regional, agencia_id, (SELECT nome_agencia from tbl_agencia where id_agencia=agencia_id) as agencia from tbl_user where id_user = ?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$userIdPost]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($user)) {
    return;
}

$nome = trim($user['nome']) . ' ' . trim($user['sobrenome']);
$email = trim($user['email']);
$login = trim($user['nome_usuario']);
$contrato = $user['contrato'];
$municipio = $user['municipio'];
$regional = $user['regional'];
$agencia = $user['agencia'];
$pass = newPass();
$senha = generateHash($pass);

include("../staff/newpass_email_config_cad.php");
require_once("../staff/phpmailer/class.phpmailer.php");

$mail = new PHPMailer();

$mail->IsSMTP();
$mail->Port = $port;
$mail->Host = $host;
$mail->SMTPAuth = true;
$mail->Username = $caixaPostalServidorUser;
$mail->Password = $caixaPostalServidorSenha;
$mail->From = $caixaPostalServidorEmail;
$mail->FromName = $caixaPostalServidorNome;
$mail->AddAddress($email);
$mail->IsHTML(true);
$mail->Subject  = utf8_decode($assunto);
$mail->Body = utf8_decode($conteudo);
$enviado = $mail->Send();
$mail->ClearAllRecipients();
$mail->ClearAttachments();

if ($enviado) {
    $sqlmAIL = "UPDATE tbl_user SET flag_mail=1, senha_usuario=? where id_user=?";
    $stmt = $PDO->prepare($sqlmAIL);
    $result = $stmt->execute([$senha, $userIdPost]);
    echo "<div style='color: green'>E-mail enviado com sucesso!";
    echo "</div>";
    echo '<script>$("#mail_' . $userIdPost . '").html("");$("#close_mail").click();</script>';
} else {
    echo "<div style='color: red'>Erro ao enviar e-mail: " . $mail->ErrorInfo;
    echo "</div>";
}
?>

