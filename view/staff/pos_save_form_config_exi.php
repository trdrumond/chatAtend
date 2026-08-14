<?php
include("../cnf/session.php");

$idCampo = (int) ($_POST['id_campo'] ?? 0);
$idForm = (int) ($_POST['id_form'] ?? 0);

$sql_v = "SELECT campo_id from tbl_forms_dados_input_campo_cnf where campo_id=? and form_id=?";
$stmt = $PDO->prepare($sql_v);
$result = $stmt->execute([$idCampo, $idForm]);
$ver_campo = $stmt->fetch(PDO::FETCH_ASSOC);

if (($ver_campo['campo_id'] ?? '') == '') {
    $sql = "SELECT count(*) as qtd from tbl_forms_dados_input_campo_cnf where form_id=?";
    $stm = $PDO->prepare($sql);
    $stm->execute([$idForm]);
    $ver = $stm->fetch(PDO::FETCH_ASSOC);
    $ordem = ($ver['qtd'] ?? 0) + 1;

    $sql = "SELECT input_id from tbl_forms_dados_input_campo where id_campo=?";
    $stm = $PDO->prepare($sql);
    $stm->execute([$idCampo]);
    $inp = $stm->fetch(PDO::FETCH_ASSOC);

    $sql = "INSERT INTO tbl_forms_dados_input_campo_cnf (campo_id, form_id, input_id, ordem) VALUES (?, ?, ?, ?)";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$idCampo, $idForm, $inp['input_id'] ?? null, $ordem]);
    if ($result == 1) {
        echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
        ?>
        <script>
            load(<?= $idForm ?>);
            $("#form_id_<?= $idForm ?>").val('');
            $("#nome_campo_<?= $idForm ?>").val('');

            function load(id_forms){
                $("#tbl_<?= $idForm ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..."></div>');
                $.post("staff/fdd_tbl_config_form.php",
                {
                    id_forms: id_forms
                },
                function (valor) {
                    $("#tbl_<?= $idForm ?>").html(valor);
                });
            }
        </script>
        <?php
    }
} else {
    echo "<br>Campo ja cadastrado para este formulário!";
}

?>
