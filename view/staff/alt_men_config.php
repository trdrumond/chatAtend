<?php
include("../cnf/session.php");

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    return;
}
$stmtCtt = $PDO->prepare("SELECT contrato_id from tbl_config_men_ini where id_campo=?");
$stmtCtt->execute([$id]);
$rowCtt = $stmtCtt->fetch(PDO::FETCH_ASSOC);
if (!is_array($rowCtt) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($rowCtt['contrato_id'] ?? 0))) {
    return;
}
$titulo = (string) ($_POST['titulo'] ?? '');
$mensagem = (string) ($_POST['mensagem'] ?? '');
$assunto = (int) ($_POST['assunto'] ?? 0);
$status = (($_POST['status'] ?? '') !== '') ? 1 : 0;

$stmt = $PDO->prepare("UPDATE tbl_config_men_ini SET titulo_men=?, txt=?, assunto_id=?, ativo=? where id_campo=?");
$result = $stmt->execute([$titulo, $mensagem, $assunto, $status, $id]);

if ($result == 1) {
    $modalId = json_encode((string) $id);
?>
<script>

    $("#modal_alt_" + <?= $modalId ?>).modal('hide');
    actionPage('cad-men', 'cnf');



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
