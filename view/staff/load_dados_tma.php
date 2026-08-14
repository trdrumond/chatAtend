<?php
include("../cnf/session.php");

$sqlData = " and date_format(data_hora, '%Y-%m-%d')=curdate()";
$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$formId = (int) ($_POST['form_id'] ?? 0);
$respId = (int) ($_POST['resp_id'] ?? 0);

if ($contratoId != 0) {
    $params = [$contratoId, $formId];
    $sql = "SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' $sqlData and contrato_id=? and fila_id=?";
    if ($respId > 0) {
        $sql .= " and bko_resp=?";
        $params[] = $respId;
    }
    $stmt = $PDO->prepare($sql);
    $stmt->execute($params);
    $infoUserSla = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $PDO->prepare("SELECT sec_to_time(avg(time_to_sec(ta))) as sla FROM tbl_chat_fila where status_fila>=4 and hora_fim<>'' $sqlData  and contrato_id=? and fila_id=?");
    $stmt->execute([$contratoId, $formId]);
    $infoGeral = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($nivel_usu < 4) {
        $cntHoje = $infoGeral['sla'] ?? '';
    } else {
        $cntHoje = $infoUserSla['sla'] ?? '';
    }
} else {
    $cttBind = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql = "SELECT sec_to_time(avg(time_to_sec(ta))) as sla from tbl_chat_fila where status_fila>=4 and hora_fim<>'' $sqlData and contrato_id in (" . $cttBind['ph'] . ")";
    $stmt = $PDO->prepare($sql);
    $stmt->execute($cttBind['ids']);
    $dadosHoje = $stmt->fetch(PDO::FETCH_ASSOC);
    $cntHoje = $dadosHoje['sla'] ?? '';
    $ex = explode(".", (string) $cntHoje);
    $cntHoje = $ex[0];
    $cntHoje = ($cntHoje == '') ? '--:--:--' : $cntHoje;
}

$table_tma = '<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sql = "SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(ta))) as tma from tbl_chat_fila where ta is not null $sqlData and fila_id=id_fila) as tma from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
$stmt = $PDO_LOAD->prepare($sql);
$stmt->execute();
$dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($z = 0; $z < count($dadosContratos); $z++) {
    if ($dadosContratos[$z]['id_fila'] != '') {
        $ex = explode('.', (string) $dadosContratos[$z]['tma']);
        $table_tma .= "<tr>";
        $table_tma .= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['nome_fila'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_tma .= "<td>" . htmlspecialchars((string) $ex[0], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_tma .= "</tr>";
    }
}
$table_tma .= '</tbody></table>';

?>


<script>
var valor = <?= json_encode((string) $cntHoje) ?>;
$('#dadosTma').html(valor);
var table_tma = <?= json_encode($table_tma) ?>;
$('#list-tma').html(table_tma);
</script>
