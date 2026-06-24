<?php
include("../cnf/session.php");

//depurador($_POST);

//depurador($_FILES['arquivo']);

$userDestinatario = $_POST['dest'];
$chatId = $_POST['chatId'];

$size_max = 5000000;

if($_FILES['arquivo']['size'] > $size_max){
    echo "<div style='color: red'>Arquivo maior do que o permitido</div>";
    echo "<script>$('#bar').val('0');</script>";
    echo "<script>$('#porcentagem').html('0%');</script>";
    //echo "<script>setTimeout(function () {zera_file()}, 2000);</script>";

} else {
    if($_FILES['arquivo']['error']!==0){
        echo "<div style='color: red'>Ocorreu um erro ao enviar o arquivo, tente novamente.</div>";
        echo "<script>$('#bar').val('0');</script>";
        echo "<script>$('#porcentagem').html('0%');</script>";
        //echo "<script>setTimeout(function () {zera_file()}, 2000);</script>";
    } else {


            $ext = explode(".", $_FILES['arquivo']['name']);
            $ext=end($ext);
            $nameFile = $_POST['token']. "-".strtotime(date('Y-m-d H:i:s'))."." . $ext;
            $pat = '../file/';
            move_uploaded_file($_FILES['arquivo']['tmp_name'], $pat . $nameFile);

            $valorFile = 'file/' . $nameFile;
            //echo '<a href="file/'.$nameFile.'" target="_blank">Arquivo enviado</a>';
            echo "<script>$('#bar').hide();</script>";
            echo "<script>$('#porcentagem').hide();</script>";
            echo "<div style='color: green'><h1>Upload concluído com sucesso!</h1></div>";

            echo "<script>$('#ipt_file_".$chatId."').val('".$valorFile."');</script>";
            echo "<script>$('#save_file_".$chatId."').prop( 'disabled', false );</script>";

    }

}

?>
