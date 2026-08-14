<?php
include("../cnf/session.php");

$nome = (string) ($_POST['nome'] ?? '');
$uf = (string) ($_POST['uf'] ?? '');
if ((int) ($infoUser['nivel_id'] ?? 0) > 1) {
    return;
}

$stmt = $PDO->prepare("INSERT INTO tbl_contrato (nome_contrato, uf) VALUES (?, ?)");
$result = $stmt->execute([$nome, $uf]);

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
