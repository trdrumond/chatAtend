<?php
include("../cnf/session.php");

//var_dump($_POST);

$sql="INSERT INTO tbl_contrato (nome_contrato, uf) VALUES ('".$_POST['nome']."', '".$_POST['uf']."')";

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
    actionPage('cad-ctt', 'cnf');



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
