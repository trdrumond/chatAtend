<?php
include("../cnf/conn.php");

$idCampo = (int) ($_POST['id_campo'] ?? 0);
$servicoId = (int) ($_POST['id_servico'] ?? 0);
$inputId = (int) ($_POST['id_input'] ?? 0);

if (($_POST['tipo_input'] ?? '') === 'checkbox') {
    $very_1 = "SELECT id_option from tbl_servicos_input_option where referencia='opcao_chk_1' and campo_id=? and servico_id=? and input_id=?";
    $stmt = $PDO->prepare($very_1);
    $result = $stmt->execute([$idCampo, $servicoId, $inputId]);
    $ver_1 = $stmt->fetch(PDO::FETCH_ASSOC);

    if (($ver_1['id_option'] ?? '') !== '') {
        $sql_1 = "UPDATE tbl_servicos_input_option SET desc_option=?, value_option=? where referencia='opcao_chk_1' and campo_id=? and servico_id=? and input_id=?";
        $params1 = [$_POST['opcao_chk_1'] ?? '', nomeCampoInput($_POST['opcao_chk_1'] ?? ''), $idCampo, $servicoId, $inputId];
    } else {
        $sql_1 = "INSERT INTO tbl_servicos_input_option (servico_id, input_id, campo_id, desc_option, value_option, referencia) VALUES (?, ?, ?, ?, ?, 'opcao_chk_1')";
        $params1 = [$servicoId, $inputId, $idCampo, $_POST['opcao_chk_1'] ?? '', nomeCampoInput($_POST['opcao_chk_1'] ?? '')];
    }
    $stmt = $PDO->prepare($sql_1);
    $result_1 = $stmt->execute($params1);

    $very_2 = "SELECT id_option from tbl_servicos_input_option where referencia='opcao_chk_2' and campo_id=? and servico_id=? and input_id=?";
    $stmt = $PDO->prepare($very_2);
    $result = $stmt->execute([$idCampo, $servicoId, $inputId]);
    $ver_2 = $stmt->fetch(PDO::FETCH_ASSOC);

    if (($ver_2['id_option'] ?? '') !== '') {
        $sql_2 = "UPDATE tbl_servicos_input_option SET desc_option=?, value_option=? where referencia='opcao_chk_2' and campo_id=? and servico_id=? and input_id=?";
        $params2 = [$_POST['opcao_chk_2'] ?? '', nomeCampoInput($_POST['opcao_chk_2'] ?? ''), $idCampo, $servicoId, $inputId];
    } else {
        $sql_2 = "INSERT INTO tbl_servicos_input_option (servico_id, input_id, campo_id, desc_option, value_option, referencia) VALUES (?, ?, ?, ?, ?, 'opcao_chk_2')";
        $params2 = [$servicoId, $inputId, $idCampo, $_POST['opcao_chk_2'] ?? '', nomeCampoInput($_POST['opcao_chk_2'] ?? '')];
    }
    $stmt = $PDO->prepare($sql_2);
    $result_2 = $stmt->execute($params2);

    if (($result_1 == 1) && ($result_2 == 1)) {
        ?>
            <script>
                loadConfigServ(<?php echo $servicoId; ?>);
                function loadConfigServ(id_servico){
                    $("#div_config_serv_<?php echo $servicoId; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/config_servicos.php",
                    {
                        id_servico
                    },
                    function (valor) {
                        $("#div_config_serv_<?php echo $servicoId; ?>").html(valor);
                    });
                }
            </script>
        <?php
    }
}

if (($_POST['tipo_input'] ?? '') === 'select') {
    $very_1 = "SELECT count(id_option) as qtd from tbl_servicos_input_option where referencia='select' and campo_id=? and servico_id=? and input_id=?";
    $stmt = $PDO->prepare($very_1);
    $result = $stmt->execute([$idCampo, $servicoId, $inputId]);
    $qtd = $stmt->fetch(PDO::FETCH_ASSOC);
    $value_option = 'sel_' . ((int) ($qtd['qtd'] ?? 0) + 1);
    $sqlInsert = "INSERT INTO tbl_servicos_input_option (servico_id, input_id, campo_id, desc_option, value_option, referencia) VALUES (?, ?, ?, ?, ?, 'select')";
    $stmt = $PDO->prepare($sqlInsert);
    $result_1 = $stmt->execute([$servicoId, $inputId, $idCampo, $_POST['opcao_sel'] ?? '', $value_option]);

    if ($result_1 == 1) {
        ?>
            <script>
                loadTable('<?= $servicoId ?>', '<?= $idCampo ?>', '<?= $inputId ?>');
                $("#btn_save_sel").val('');

                function loadTable(id_servico, id_campo, id_input){
                    $("#tbl_sel").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                    $.post("staff/tbl_sel.php",
                    {
                        id_servico, id_campo, id_input
                    },
                    function (valor) {
                        $("#tbl_sel").html(valor);
                    });
                }

                function loadConfigServ(id_servico){
                    $("#div_config_serv_<?php echo $servicoId; ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                    $.post("staff/config_servicos.php",
                    {
                        id_servico
                    },
                    function (valor) {
                        $("#div_config_serv_<?php echo $servicoId; ?>").html(valor);
                    });
                }
            </script>
        <?php
    }
}

?>

