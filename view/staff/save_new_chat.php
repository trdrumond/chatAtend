<?php
include("../cnf/session.php");

//depurador($_POST);



$sql="INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES ('".$infoUser['contrato_id']."', '".$infoUser['id_user']."', '".$_POST['col']."', '".$_POST['grupo_com']."', '".$_POST['grupo_nome']."')";

//echo $sql;


$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();



if($result==1){




?>
<script>
    Swal.fire({
        position: 'bottom-start',
        icon: 'success',
        title: 'Novo Chat Iniciado!',
        showConfirmButton: false,
        timer: 1500
    });
    $("#new_registro").modal('hide');
    actionPage('com-idx', 'idx');



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
