<?php
include("../cnf/session.php");

$userDestinatario = $_POST['dest'] ?? '';
$chatId = (int) ($_POST['chatId'] ?? 0);

$size_max = 5000000;
$allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt', 'mp3', 'mp4', 'wav', 'csv', 'odt', 'ods'];

if (!isset($_FILES['arquivo']) || !is_array($_FILES['arquivo'])) {
    echo "<div style='color: red'>Arquivo não enviado.</div>";
    echo "<script>$('#bar').val('0');</script>";
    echo "<script>$('#porcentagem').html('0%');</script>";
} elseif ($_FILES['arquivo']['size'] > $size_max) {
    echo "<div style='color: red'>Arquivo maior do que o permitido</div>";
    echo "<script>$('#bar').val('0');</script>";
    echo "<script>$('#porcentagem').html('0%');</script>";
} elseif ($_FILES['arquivo']['error'] !== 0) {
    echo "<div style='color: red'>Ocorreu um erro ao enviar o arquivo, tente novamente.</div>";
    echo "<script>$('#bar').val('0');</script>";
    echo "<script>$('#porcentagem').html('0%');</script>";
} else {
    $origName = (string) ($_FILES['arquivo']['name'] ?? '');
    $ext = strtolower((string) pathinfo($origName, PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', $ext);
    if ($ext === '' || !in_array($ext, $allowedExt, true)) {
        echo "<div style='color: red'>Tipo de arquivo não permitido.</div>";
        echo "<script>$('#bar').val('0');</script>";
        echo "<script>$('#porcentagem').html('0%');</script>";
    } else {
        $token = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($_POST['token'] ?? ''));
        $nameFile = $token . '-' . time() . '.' . $ext;
        $pat = '../file/';
        if (!is_dir($pat) && !mkdir($pat, 0755, true) && !is_dir($pat)) {
            echo "<div style='color: red'>Pasta de upload indisponível.</div>";
            echo "<script>$('#bar').val('0');</script>";
            echo "<script>$('#porcentagem').html('0%');</script>";
        } else {
            move_uploaded_file($_FILES['arquivo']['tmp_name'], $pat . $nameFile);

            $valorFile = 'file/' . $nameFile;
            echo "<script>$('#bar').hide();</script>";
            echo "<script>$('#porcentagem').hide();</script>";
            echo "<div style='color: green'><h1>Upload concluído com sucesso!</h1></div>";

            echo "<script>$('#ipt_file_" . $chatId . "').val(" . json_encode($valorFile, JSON_UNESCAPED_SLASHES) . ");</script>";
            echo "<script>$('#save_file_" . $chatId . "').prop( 'disabled', false );</script>";
        }
    }
}

?>
