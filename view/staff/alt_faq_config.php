<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}


if(strpos($_POST['mensagem'], '<a href')){
    $_POST['mensagem'] = str_replace('<a', '<a target="_blank"', $_POST['mensagem']);
}



$sql="UPDATE tbl_faq SET titulo_faq='".$_POST['titulo']."', txt='".$_POST['mensagem']."', assunto_id='".$_POST['assunto']."', ativo=$status where id_faq=".$_POST['id'];

//echo "<br>".$sql;


$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();


if($result==1){
?>
<script>

    $("#modal_alt_<?php echo $_POST['id']; ?>").modal('hide');
    actionPage('cad-faq', 'cnf');



    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        //console.log('A ação é: ' + action);
        $.post("action.php",
        {
            action: action, sec: sec
        },
        function (valor) {
            $("#action-page").html(valor);
        });
    }


</script>
<?php
    }


    ?>
