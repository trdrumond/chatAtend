<?php
include("../cnf/session.php");

//depurador($_POST);
$cols="'1'";
if($infoUser['id_user']!=1){
    $cols.=",'".$infoUser['id_user']."'";
}

for($x=0; $x<count($_POST['col']);$x++){
    $cols.=",'".$_POST['col'][$x]."'";
}


//echo "<br>".$cols;

//$sql="INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES ('".$infoUser['contrato_id']."', '".$infoUser['id_user']."', '".$_POST['col']."', '".$_POST['grupo_com']."', '".$_POST['grupo_nome']."')";

$stmt = $PDO->prepare("SELECT count(grupo_com) as qtd from tbl_com_info where grupo_com<>''");
$result = $stmt->execute();
$qtd_group = $stmt->fetch( PDO::FETCH_ASSOC );
$qtd_group = $qtd_group['qtd'] + 1;

$sql='INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES ("'.$infoUser['contrato_id'].'", "0", "0", "'.$qtd_group.'", "'.$_POST['nome_grupo'].'")';

//echo "<br>".$sql;


$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
    $stmt = $PDO->prepare("SELECT id_com from tbl_com_info where grupo_com=".$qtd_group);
    $result = $stmt->execute();
    $com_group = $stmt->fetch( PDO::FETCH_ASSOC );

    $sql='INSERT INTO tbl_com_config (grupo_com_id, cols) VALUES ("'.$com_group['id_com'].'", "'.$cols.'")';
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    //echo "<br>".$sql;

    if($result==1){




    ?>
    <script>

        Swal.fire({
            position: 'bottom-start',
            icon: 'success',
            title: 'Novo grupo criado!',
            showConfirmButton: false,
            timer: 1500
        });
        $("#new_group").modal('hide');
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


}
?>
