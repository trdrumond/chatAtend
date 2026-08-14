<?php
include("../cnf/session.php");

$titulo = (string) ($_POST['titulo'] ?? '');
$contrato = (int) ($_POST['contrato'] ?? 0);
if ($contrato < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
    return;
}
$multichat = (int) ($_POST['multichat'] ?? 0);
$assuntoRaw = $_POST['assunto'] ?? [];
if (!is_array($assuntoRaw)) {
    $assuntoRaw = [$assuntoRaw];
}
$assuntos = implode(',', array_map('intval', $assuntoRaw));

$stmt = $PDO->prepare("INSERT INTO tbl_config_fila (nome_fila, contrato_id, assuntos_id, multichat) VALUES (?, ?, ?, ?)");
$result = $stmt->execute([$titulo, $contrato, $assuntos, $multichat]);

if ($result == 1) {
        $stmt = $PDO->prepare("SELECT id_fila, contrato_id from tbl_config_fila where ativo=1 and nome_fila=? and contrato_id=?");
        $stmt->execute([$titulo, $contrato]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($info) || empty($info['id_fila'])) {
            return;
        }

        $idFila = (int) $info['id_fila'];
        $idCtt = (int) $info['contrato_id'];
        $tablePos = 'tbl_in_pos_' . $idFila . '_' . $idCtt;
        $tableMon = 'tbl_in_mon_' . $idFila . '_' . $idCtt;
        if (!preg_match('/^tbl_in_pos_\d+_\d+$/', $tablePos) || !preg_match('/^tbl_in_mon_\d+_\d+$/', $tableMon)) {
            return;
        }

        $create = "CREATE TABLE `{$tablePos}` ("
            ."`id_fila_pos` int(11) NOT NULL AUTO_INCREMENT,"
            ."`data_hora` timestamp NOT NULL DEFAULT current_timestamp(),"
            ."`situacao_id` int(2) NOT NULL DEFAULT 1,"
            ."`fila_id` int(11) DEFAULT NULL,"
            ."`chat_id` int(11) DEFAULT NULL,"
            ."`tp` varchar(12) DEFAULT NULL,"
            ."PRIMARY KEY (`id_fila_pos`)"
            .") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $stmt = $PDO->prepare($create);
        $stmt->execute();

        $create = "CREATE TABLE `{$tableMon}` ("
            ."`id_mon` int(11) NOT NULL AUTO_INCREMENT,"
            ."`data_hora` timestamp NOT NULL DEFAULT current_timestamp(),"
            ."`fila_id` int(11) DEFAULT NULL,"
            ."`chat_id` int(11) DEFAULT NULL,"
            ."`resp_mon` int(11) DEFAULT NULL,"
            ."PRIMARY KEY (`id_mon`)"
            .") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $stmt = $PDO->prepare($create);
        $result = $stmt->execute();

        if ($result == 1) {
            $idx = "ALTER TABLE `{$tablePos}` ADD INDEX `idx` (`id_fila_pos`,`data_hora`,`situacao_id`,`fila_id`,`chat_id`);";
            $stmt = $PDO->prepare($idx);
            $stmt->execute();

            $idx = "ALTER TABLE `{$tableMon}` ADD INDEX `idx` (`id_mon`,`data_hora`,`fila_id`,`chat_id`,`resp_mon`);";
            $stmt = $PDO->prepare($idx);
            $stmt->execute();

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
    actionPage('cad-fil', 'cnf');



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
    }}

    ?>
