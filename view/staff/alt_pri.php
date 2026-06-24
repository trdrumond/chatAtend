<?php
include("../cnf/session.php");

//var_dump($_POST);

if($_POST['status']!=''){
    $status=1;
} else {
    $status=0;
}

//echo "<br>".$status;

$sql="UPDATE tbl_prioridade SET ativo=$status, peso='".$_POST['peso']."' where id_prioridade=".$_POST['id'];

//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
    if($status==0){
        $sql="UPDATE tbl_assunto SET prioridade_id=-1 where prioridade_id=".$_POST['id'];
        //echo $sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();
    }

?>

<script>

    $("#modal_alt_<?php echo $_POST['id']; ?>").modal('hide');
    actionPage('cad-pri', 'cnf');



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
