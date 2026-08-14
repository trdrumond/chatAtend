<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $id)) {
    return;
}
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_contrato SET ativo=? where id_contrato=?");
$result = $stmt->execute([$status, $id]);

if ($result == 1) {
    $modalId = json_encode((string) $id);
?>
<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
    actionPage('cad-ctt', 'cnf');



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
