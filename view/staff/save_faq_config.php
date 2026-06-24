<?php
include("../cnf/session.php");

//var_dump($_POST);

$_POST['assunto'] = ($_POST['assunto']=='') ? 0 : $_POST['assunto'];

if(strpos($_POST['mensagem'], '<a href')){
    $_POST['mensagem'] = str_replace('<a', '<a target="_blank"', $_POST['mensagem']);
}

$sql="INSERT INTO tbl_faq (titulo_faq, txt, contrato_id, assunto_id, fila_id) VALUES ('".$_POST['titulo']."', '".$_POST['mensagem']."', '".$_POST['contrato']."', '".$_POST['assunto']."', '".$_POST['fila']."')";

//echo $sql;

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();


if($result==1){
?>

<script>
    Swal.fire({
        position: 'bottom-start',
        icon: 'success',
        title: 'Gravado com sucesso!',
        showConfirmButton: false,
        timer: 1500
    });
    $("#new_registro").modal('hide');
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
