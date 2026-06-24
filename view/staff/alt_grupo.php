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

$sql="UPDATE tbl_com_info SET grupo_nome='".$_POST['nome_grupo']."' where id_com=".$_POST['com'];
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($_POST['col']!=''){
    $sql='UPDATE tbl_com_config SET cols="'.$cols.'", equipe_adm=0, equipe_bko=0, equipe_ate=0 where grupo_com_id='.$_POST['com'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
}

?>

<script>
$("#alt_group_<?=$_POST['com']?>").modal('hide');
Swal.fire({
    position: 'bottom-start',
    icon: 'success',
    title: 'Grupo Alterado!',
    showConfirmButton: false,
    timer: 1500
});

//actionPage('com-idx', 'idx');
setTimeout(() => {
    actionPage('com-idx', 'idx');
    loadComList(0, <?=$_POST['com']?>);
}, "1000");





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

