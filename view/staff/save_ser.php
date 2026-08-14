<?php
include("../cnf/session.php");

$nome = (string) ($_POST['nome'] ?? '');
$contratoId = (int) ($_POST['contrato'] ?? 0);

if ($nome === '' || $contratoId < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    return;
}

$sql = "INSERT INTO tbl_servicos (nome_servico, contrato_id) VALUES (?, ?)";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$nome, $contratoId]);

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
    actionPage('cad-ser', 'cnf');

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

