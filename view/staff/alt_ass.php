<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    return;
}
$stmtCtt = $PDO->prepare("SELECT contrato_id from tbl_assunto where id_assunto=?");
$stmtCtt->execute([$id]);
$rowCtt = $stmtCtt->fetch(PDO::FETCH_ASSOC);
if (!is_array($rowCtt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($rowCtt['contrato_id'] ?? 0))) {
    return;
}
$titulo = (string) ($_POST['titulo'] ?? '');
$procedimento = (string) ($_POST['procedimento'] ?? '');
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_assunto SET ativo=?, titulo_assunto=?, procedimento=? where id_assunto=?");
$result = $stmt->execute([$status, $titulo, $procedimento, $id]);

if ($result == 1) {
    $modalId = json_encode((string) $id);
?>

<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
    actionPage('cad-ass', 'cnf');



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
