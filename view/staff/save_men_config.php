<?php
include("../cnf/session.php");

$assunto = ($_POST['assunto'] ?? '') === '' ? 0 : (int) $_POST['assunto'];
$titulo = (string) ($_POST['titulo'] ?? '');
$mensagem = (string) ($_POST['mensagem'] ?? '');
$contrato = (int) ($_POST['contrato'] ?? 0);
if ($contrato < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
    return;
}

$stmt = $PDO->prepare("INSERT INTO tbl_config_men_ini (titulo_men, txt, contrato_id, assunto_id) VALUES (?, ?, ?, ?)");
$result = $stmt->execute([$titulo, $mensagem, $contrato, $assunto]);

if ($result == 1) {
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
