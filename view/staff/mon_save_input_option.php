<?php
include("../cnf/conn.php");

$idCampo = (int) ($_POST['id_campo'] ?? 0);
$idFila = (int) ($_POST['id_fila'] ?? 0);
$idInput = (int) ($_POST['id_input'] ?? 0);

if (($_POST['tipo_input'] ?? '') == 'checkbox') {
    $very_1 = "SELECT id_option from tbl_forms_mon_input_option where referencia='opcao_chk_1_mon' and campo_id=? and fila_id=? and input_id=?";
    $stmt = $PDO->prepare($very_1);
    $result = $stmt->execute([$idCampo, $idFila, $idInput]);
    $ver_1 = $stmt->fetch(PDO::FETCH_ASSOC);

    if (($ver_1['id_option'] ?? '') != '') {
        $sql_1 = "UPDATE tbl_forms_mon_input_option SET desc_option=?, value_option=?, valor_mon_option=? where referencia='opcao_chk_1_mon' and campo_id=? and fila_id=? and input_id=?";
        $params1 = [$_POST['opcao_chk_1_mon'] ?? '', nomeCampoInput($_POST['opcao_chk_1_mon'] ?? ''), $_POST['valor_chk_1_mon'] ?? '', $idCampo, $idFila, $idInput];
    } else {
        $sql_1 = "INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, referencia, valor_mon_option) VALUES (?, ?, ?, ?, ?, 'opcao_chk_1_mon', ?)";
        $params1 = [$idFila, $idInput, $idCampo, $_POST['opcao_chk_1_mon'] ?? '', nomeCampoInput($_POST['opcao_chk_1_mon'] ?? ''), $_POST['valor_chk_1_mon'] ?? ''];
    }
    $stmt = $PDO->prepare($sql_1);
    $result_1 = $stmt->execute($params1);

    $very_2 = "SELECT id_option from tbl_forms_mon_input_option where referencia='opcao_chk_2_mon' and campo_id=? and fila_id=? and input_id=?";
    $stmt = $PDO->prepare($very_2);
    $result = $stmt->execute([$idCampo, $idFila, $idInput]);
    $ver_2 = $stmt->fetch(PDO::FETCH_ASSOC);

    if (($ver_2['id_option'] ?? '') != '') {
        $sql_2 = "UPDATE tbl_forms_mon_input_option SET desc_option=?, value_option=?, valor_mon_option=? where referencia='opcao_chk_2_mon' and campo_id=? and fila_id=? and input_id=?";
        $params2 = [$_POST['opcao_chk_2_mon'] ?? '', nomeCampoInput($_POST['opcao_chk_2_mon'] ?? ''), $_POST['valor_chk_2_mon'] ?? '', $idCampo, $idFila, $idInput];
    } else {
        $sql_2 = "INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, referencia, valor_mon_option) VALUES (?, ?, ?, ?, ?, 'opcao_chk_2_mon', ?)";
        $params2 = [$idFila, $idInput, $idCampo, $_POST['opcao_chk_2_mon'] ?? '', nomeCampoInput($_POST['opcao_chk_2_mon'] ?? ''), $_POST['valor_chk_2_mon'] ?? ''];
    }
    $stmt = $PDO->prepare($sql_2);
    $result_2 = $stmt->execute($params2);
    if (($result_1 == 1) && ($result_2 == 1)) {
        ?>
        <script>
            loadConfigServ(<?= $idFila ?>);
            function loadConfigServ(id_fila){
                $("#div_config_form_mon_<?= $idFila ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                $.post("staff/mon_config_form.php",
                {
                    id_fila
                },
                function (valor) {
                    $("#div_config_form_mon_<?= $idFila ?>").html(valor);
                });
            }
        </script>
        <?php
    }
}

if (($_POST['tipo_input'] ?? '') == 'select') {
    $very_1 = "SELECT count(id_option) as qtd from tbl_forms_mon_input_option where referencia='select' and campo_id=? and fila_id=? and input_id=?";
    $stmt = $PDO->prepare($very_1);
    $result = $stmt->execute([$idCampo, $idFila, $idInput]);
    $qtd = $stmt->fetch(PDO::FETCH_ASSOC);
    $value_option = 'sel_' . (($qtd['qtd'] ?? 0) + 1);
    $sqlInsert = "INSERT INTO tbl_forms_mon_input_option (fila_id, input_id, campo_id, desc_option, value_option, valor_mon_option, referencia) VALUES (?, ?, ?, ?, ?, ?, 'select')";
    $stmt = $PDO->prepare($sqlInsert);
    $result_1 = $stmt->execute([$idFila, $idInput, $idCampo, $_POST['opcao_sel_mon'] ?? '', $value_option, $_POST['valor_sel_mon'] ?? '']);
    if ($result_1 == 1) {
        ?>
        <script>
            loadTable('<?= $idFila ?>', '<?= $idCampo ?>', '<?= $idInput ?>');
            $("#btn_save_sel").val('');

            function loadTable(id_fila, id_campo, id_input){
                $("#tbl_sel").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..."></div>');
                $.post("staff/mon_tbl_sel.php",
                {
                    id_fila, id_campo, id_input
                },
                function (valor) {
                    $("#tbl_sel").html(valor);
                });
            }

            function loadConfigServ(id_fila){
                $("#div_config_form_mon_<?= $idFila ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                $.post("staff/mon_config_form.php",
                {
                    id_fila
                },
                function (valor) {
                    $("#div_config_form_mon_<?= $idFila ?>").html(valor);
                });
            }
        </script>
        <?php
    }
}

if (($_POST['tipo_input'] ?? '') == 'text') {
    $sql = "UPDATE tbl_forms_mon_input_option SET valor_mon_option=? where referencia='text' and campo_id=? and fila_id=? and input_id=?";
    $stmt = $PDO->prepare($sql);
    $result_1 = $stmt->execute([$_POST['valor_txt_mon'] ?? '', $idCampo, $idFila, $idInput]);
    if ($result_1 == 1) {
        ?>
        <script>
            loadConfigServ(<?= $idFila ?>);
            function loadConfigServ(id_fila){
                $("#div_config_form_mon_<?= $idFila ?>").html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                $.post("staff/mon_config_form.php",
                {
                    id_fila
                },
                function (valor) {
                    $("#div_config_form_mon_<?= $idFila ?>").html(valor);
                });
            }
        </script>
        <?php
    }
}

?>
