<?php
include("../cnf/session.php");

if (($_POST['nome_campo'] ?? '') != '') {
    $idFila = (int) ($_POST['id_fila'] ?? 0);
    $idInput = (int) ($_POST['id_input'] ?? 0);
    $nomeCampo = (string) $_POST['nome_campo'];
    $nomeCampoDb = nomeCampoInput($nomeCampo);

    $sql_v = "SELECT desc_campo from tbl_forms_mon_input_campo where desc_campo=? and fila_id=?";
    $stmt = $PDO->prepare($sql_v);
    $result = $stmt->execute([$nomeCampo, $idFila]);
    $verifica_nome = $stmt->fetch(PDO::FETCH_ASSOC);
    if (($verifica_nome['desc_campo'] ?? '') == '') {
        $sql = "SELECT count(*) as qtd from tbl_forms_mon_input_campo_cnf where fila_id=?";
        $stm = $PDO->prepare($sql);
        $stm->execute([$idFila]);
        $ver = $stm->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT contrato_id from tbl_config_fila where id_fila=?";
        $stm = $PDO->prepare($sql);
        $stm->execute([$idFila]);
        $ctt = $stm->fetch(PDO::FETCH_ASSOC);

        $ordem = ($ver['qtd'] ?? 0) + 1;

        $sql = "INSERT INTO tbl_forms_mon_input_campo (desc_campo, nome_campo, input_id, fila_id) VALUES (?, ?, ?, ?)";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$nomeCampo, $nomeCampoDb, $idInput, $idFila]);

        if ($result == 1) {
            $sql = "SELECT id_campo from tbl_forms_mon_input_campo where desc_campo=? and fila_id=?";
            $stm = $PDO->prepare($sql);
            $stm->execute([$nomeCampo, $idFila]);
            $info_campo = $stm->fetch(PDO::FETCH_ASSOC);

            $sql = "SELECT tipo_input, parametro from tbl_forms_mon_input where id_input=?";
            $stm = $PDO->prepare($sql);
            $stm->execute([$idInput]);
            $info_input = $stm->fetch(PDO::FETCH_ASSOC);

            $sql = "INSERT INTO tbl_forms_mon_input_campo_cnf (campo_id, fila_id, input_id, ordem) VALUES (?, ?, ?, ?)";
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute([$info_campo['id_campo'] ?? null, $idFila, $idInput, $ordem]);
            if ($result == 1) {
                $tableMon = 'tbl_in_mon_' . $idFila . '_' . (int) ($ctt['contrato_id'] ?? 0);
                if (preg_match('/^tbl_in_mon_\d+_\d+$/', $tableMon)) {
                    $add = "ADD COLUMN `" . $nomeCampoDb . "` " . ($info_input['parametro'] ?? '') . ";";
                    $alterTable = "ALTER TABLE `{$tableMon}` {$add}";
                    $stmt = $PDO->prepare($alterTable);
                    $result = $stmt->execute();
                    if ($result == 1) {
                        $add = "ADD COLUMN `pt_" . $nomeCampoDb . "` int(3) DEFAULT NULL;";
                        $alterTable = "ALTER TABLE `{$tableMon}` {$add}";
                        $stmt = $PDO->prepare($alterTable);
                        $result = $stmt->execute();

                        if ($result == 1) {
                            echo '<br><i class="fas fa-check-circle" style="color: green"></i>';
                            ?>
                            <script>
                                load(<?= $idFila ?>);
                                $("#fila_id_mon_<?= $idFila ?>").val('');
                                $("#nome_campo_mon_<?= $idFila ?>").val('');

                                function load(id_filas){
                                    $("#tbl_mon_<?= $idFila ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
                                    $.post("staff/mon_tbl_config_form.php",
                                    {
                                        id_filas: id_filas
                                    },
                                    function (valor) {
                                        $("#tbl_mon_<?= $idFila ?>").html(valor);
                                    });
                                }
                            </script>
                            <?php
                        }
                    }
                }
            }
        }
    } else {
        echo "<br>O campo cadastrado ja existe!";
    }
} else {
    echo "<br>Preencha os campos corretamente!";
}

?>
