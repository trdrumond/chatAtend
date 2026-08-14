<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    return;
}
$peso = (int) ($_POST['peso'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_prioridade SET ativo=?, peso=? where id_prioridade=?");
$result = $stmt->execute([$status, $peso, $id]);

if ($result == 1) {
    if ($status === 0) {
        $stmt = $PDO->prepare("UPDATE tbl_assunto SET prioridade_id=-1 where prioridade_id=?");
        $stmt->execute([$id]);
    }

    $modalId = json_encode((string) $id);
?>

<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
    actionPage('cad-pri', 'cnf');



    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
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
