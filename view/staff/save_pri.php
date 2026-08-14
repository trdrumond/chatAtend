<?php
include("../cnf/session.php");

$nome = (string) ($_POST['nome'] ?? '');
$peso = (int) ($_POST['peso'] ?? 0);

if (trim($nome) === '') {
    return;
}

$stmt = $PDO->prepare("INSERT INTO tbl_prioridade (nome_prioridade, peso) VALUES (?, ?)");
$result = $stmt->execute([$nome, $peso]);

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
