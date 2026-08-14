<?php
include("../cnf/session.php");

$nome = (string) ($_POST['nome'] ?? '');
$contrato = (int) ($_POST['contrato'] ?? 0);
if ($contrato < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
    return;
}

$stmt = $PDO->prepare("INSERT INTO tbl_regional (nome_regional, contrato_id) VALUES (?, ?)");
$result = $stmt->execute([$nome, $contrato]);

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
    actionPage('cad-reg', 'cnf');



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
