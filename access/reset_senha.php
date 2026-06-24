<?php
include_once("../access/conn_config.php");

$sql = "SELECT value, pref from config where ativo=1 and value='".$_POST['contrato']."'";
//echo "<br>".$sql;
$stmt = $PDO_CONF->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetch( PDO::FETCH_ASSOC );


    //if($_POST['contrato']=='piloto'){$_POST['contrato']= $_POST['contrato']."_2.2";}

    $end = 'https://'.$dados['pref'].'.logos-ma.com.br/chat-'.$dados['value'];
    //echo "<br>".$end;
    

    
?>

<script>
resetSenha("<?=$_POST['login']?>", "<?=$_POST['email']?>");

function resetSenha(login, email) {
    $("#feedback").html(
        '<div id="error" class="alert alert-info" role="alert"><center><img src="imagem/loading.gif" width="80"><br>Validando dados...</center></div>'
    );

    $.post("<?=$end?>/view/staff/newpass.php", {
            login,
            email
        },
        function(valor) {
            $("#feedback").html(valor);
        });

}
</script>