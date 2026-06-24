<?php
include("../cnf/session.php");

//var_dump($_POST);

$_POST['assunto'] = ($_POST['assunto']=='') ? 0 : $_POST['assunto'];

$sql="INSERT INTO tbl_config_men_ini (titulo_men, txt, contrato_id, assunto_id) VALUES ('".$_POST['titulo']."', '".$_POST['mensagem']."', '".$_POST['contrato']."', '".$_POST['assunto']."')";

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
    actionPage('cad-men', 'cnf');



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
