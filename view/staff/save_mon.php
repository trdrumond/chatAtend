<?php
include("../cnf/session.php");

$filaId = (int) ($_POST['fila_id'] ?? 0);
$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$chatId = (int) ($_POST['chat_id'] ?? 0);
$respMon = (int) ($_POST['resp_mon'] ?? 0);

if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    return;
}

$tableMon = 'tbl_in_mon_' . $filaId . '_' . $contratoId;
if (!preg_match('/^tbl_in_mon_\d+_\d+$/', $tableMon)) {
    return;
}

$stmt = $PDO->prepare(
    "SELECT a.id_campo, a.nome_campo, a.input_id, b.ativo, b.qualif"
    . " FROM tbl_forms_mon_input_campo a, tbl_forms_mon_input_campo_cnf b"
    . " where a.id_campo=b.campo_id and b.ativo=1 and a.fila_id=?"
);
$stmt->execute([$filaId]);
$campo = $stmt->fetchAll(PDO::FETCH_ASSOC);

$arrPt = [];
foreach ($campo as $c) {
    $nomeCampo = (string) ($c['nome_campo'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nomeCampo)) {
        continue;
    }

    $inputId = (int) $c['input_id'];
    $idCampo = (int) $c['id_campo'];
    $postVal = (string) ($_POST[$nomeCampo] ?? '');

    if ($inputId === 3) {
        $stmt = $PDO->prepare("SELECT valor_mon_option from tbl_forms_mon_input_option where campo_id=? and value_option=?");
        $stmt->execute([$idCampo, $postVal]);
    } elseif ($inputId === 4) {
        $stmt = $PDO->prepare("SELECT valor_mon_option from tbl_forms_mon_input_option where campo_id=? and desc_option=?");
        $stmt->execute([$idCampo, $postVal]);
    } else {
        $stmt = $PDO->prepare("SELECT valor_mon_option from tbl_forms_mon_input_option where campo_id=?");
        $stmt->execute([$idCampo]);
    }

    $opt = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($inputId !== 1) {
        $arrPt[$nomeCampo] = $opt['valor_mon_option'] ?? '0';
    } else {
        $arrPt[$nomeCampo] = ($postVal !== '') ? ($opt['valor_mon_option'] ?? '0') : '0';
    }
}

$avaliacao = array_sum(array_map('intval', $arrPt));

$colNames = [];
$placeholders = [];
$params = [$filaId, $chatId, $respMon, $avaliacao];

foreach ($campo as $c) {
    $nome = (string) ($c['nome_campo'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nome)) {
        continue;
    }
    $colNames[] = $nome;
    $colNames[] = 'pt_' . $nome;
    $placeholders[] = '?';
    $placeholders[] = '?';
    $params[] = (string) ($_POST[$nome] ?? '');
    $ptVal = $arrPt[$nome] ?? '0';
    $params[] = ($ptVal === null || $ptVal === '') ? '0' : (string) $ptVal;
}

if ($colNames === []) {
    return;
}

$sqlInsert = 'INSERT INTO ' . $tableMon
    . ' (data_hora, fila_id, chat_id, resp_mon, avaliacao, ' . implode(', ', $colNames) . ')'
    . ' VALUES (now(), ?, ?, ?, ?, ' . implode(', ', $placeholders) . ')';

$stmt = $PDO->prepare($sqlInsert);
$resultDados = $stmt->execute($params);

if ($resultDados == 1) {
    ?>
        <script>

            var id_chat = '<?= $chatId; ?>';
            var contrato = '<?= $contratoId; ?>';
            var fila = '<?= $filaId; ?>';
            var div_mon = '#monitoria_' + id_chat;
            $(div_mon).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

            $.post("staff/load_monitoria.php",
            {
                id_chat, contrato, fila
            },
            function (valor) {
                $(div_mon).html(valor);
            });

        </script>
    <?php
}

?>

