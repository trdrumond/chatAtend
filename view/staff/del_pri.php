<?php
include("../cnf/session.php");

//var_dump($_POST);

$idPri = (int) ($_POST['id'] ?? 0);
if ($idPri < 1) {
    return;
}
if ((int) ($infoUser['nivel_id'] ?? 99) !== 0) {
    return;
}

//echo "<br>".$status;

$sql="UPDATE tbl_prioridade SET ativo=0, del=1 where id_prioridade=?";
//echo $sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$idPri]);

if($result==1){
    $sql="UPDATE tbl_assunto SET prioridade_id=-1 where prioridade_id=?";
    //echo $sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute([$idPri]);

    $modalId = json_encode((string) $idPri);
?>

<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
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
