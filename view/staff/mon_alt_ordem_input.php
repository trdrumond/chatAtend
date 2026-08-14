<?php
include("../cnf/conn.php");
depurador($_POST);

$idCampo = (int) ($_POST['id_campo'] ?? 0);
$ordemNova = (int) ($_POST['ordem'] ?? 0);
$filaId = (int) ($_POST['fila'] ?? 0);

if ($idCampo < 1 || $filaId < 1) {
    return;
}

$stmt = $PDO->prepare("SELECT campo_id, ordem from tbl_forms_mon_input_campo_cnf where campo_id=?");
$stmt->execute([$idCampo]);
$ver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($ver)) {
    return;
}

$ordemAtual = (int) $ver['ordem'];

if ($ordemNova < $ordemAtual) {
    $stmt = $PDO->prepare("UPDATE tbl_forms_mon_input_campo_cnf SET ordem=0 where campo_id=?");
    $stmt->execute([$idCampo]);

    for ($x = $ordemNova; $x < $ordemAtual; $x++) {
        $stmt = $PDO->prepare("SELECT campo_id from tbl_forms_mon_input_campo_cnf where form_id=? and ordem=?");
        $stmt->execute([$filaId, $x]);
        $ver_1 = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($ver_1) && !empty($ver_1['campo_id'])) {
            $newOrder = $x + 1;
            $stmt = $PDO->prepare("UPDATE tbl_forms_mon_input_campo_cnf SET ordem=? where campo_id=?");
            $stmt->execute([$newOrder, (int) $ver_1['campo_id']]);
        }
    }

    $stmt = $PDO->prepare("UPDATE tbl_forms_mon_input_campo_cnf SET ordem=? where campo_id=?");
    $stmt->execute([$ordemNova, $idCampo]);
} elseif ($ordemNova > $ordemAtual) {
    $stmt = $PDO->prepare("UPDATE tbl_forms_mon_input_campo_cnf SET ordem=0 where campo_id=?");
    $stmt->execute([$idCampo]);

    for ($x = $ordemAtual; $x < $ordemNova; $x++) {
        $stmt = $PDO->prepare("SELECT campo_id from tbl_forms_mon_input_campo_cnf where form_id=? and ordem=?");
        $stmt->execute([$filaId, $x + 1]);
        $ver_1 = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($ver_1) && !empty($ver_1['campo_id'])) {
            $stmt = $PDO->prepare("UPDATE tbl_forms_mon_input_campo_cnf SET ordem=? where campo_id=?");
            $stmt->execute([$x, (int) $ver_1['campo_id']]);
        }
    }

    $stmt = $PDO->prepare("UPDATE tbl_forms_mon_input_campo_cnf SET ordem=? where campo_id=?");
    $stmt->execute([$ordemNova, $idCampo]);
}

?>

        <script>
                load(<?php echo $filaId; ?>);

                function load(id_filas){

                    $("#tbl_<?php echo $filaId; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..."></div>');
                        $.post("staff/mon_tbl_config_form.php",
                    {
                        id_filas: id_filas
                    },
                    function (valor) {
                        $("#tbl_<?php echo $filaId; ?>").html(valor);
                    });

                }
        </script>

