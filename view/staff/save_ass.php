<?php
include("../cnf/session.php");


//depurador($_POST);



$sql="INSERT INTO tbl_assunto (titulo_assunto, procedimento, contrato_id) VALUES ('".$_POST['titulo']."', '".$_POST['procedimento']."', '".$_POST['contrato']."')";

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
    actionPage('cad-ass', 'cnf');



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
