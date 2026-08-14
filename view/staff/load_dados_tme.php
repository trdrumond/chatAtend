<?php
include("../cnf/session.php");

$sqlData = " and date_format(data_hora, '%Y-%m-%d')=curdate()";
$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$filaId = (int) ($_POST['fila_id'] ?? 0);

if ($contratoId != 0) {
    $sql = "SELECT sec_to_time(avg(time_to_sec(te))) as sla FROM tbl_chat_fila where status_fila=4 $sqlData  and contrato_id=? and fila_id=?";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$contratoId, $filaId]);
    $infoGeral = $stmt->fetch(PDO::FETCH_ASSOC);
    $cntHoje = $infoGeral['sla'] ?? '';
    $ex = explode(".", (string) $cntHoje);
    $cntHoje = $ex[0];
    $cntHoje = ($cntHoje == '') ? '--:--:--' : $cntHoje;
} else {
    $cttBind = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql = "SELECT sec_to_time(avg(time_to_sec(te))) as sla from tbl_chat_fila where status_fila=4 $sqlData and contrato_id in (" . $cttBind['ph'] . ")";
    $stmt = $PDO->prepare($sql);
    $stmt->execute($cttBind['ids']);
    $dadosHoje = $stmt->fetch(PDO::FETCH_ASSOC);
    $cntHoje = $dadosHoje['sla'] ?? '';
    $ex = explode(".", (string) $cntHoje);
    $cntHoje = $ex[0];
    $cntHoje = ($cntHoje == '') ? '--:--:--' : $cntHoje;
}

$table_tme = '<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sql = "SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(te))) as tme from tbl_chat_fila where te is not null $sqlData and fila_id=id_fila) as tme from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
$stmt = $PDO_LOAD->prepare($sql);
$stmt->execute();
$dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($z = 0; $z < count($dadosContratos); $z++) {
    if ($dadosContratos[$z]['id_fila'] != '') {
        $ex = explode('.', (string) $dadosContratos[$z]['tme']);
        $table_tme .= "<tr>";
        $table_tme .= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['nome_fila'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_tme .= "<td>" . htmlspecialchars((string) $ex[0], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_tme .= "</tr>";
    }
}
$table_tme .= '</tbody></table>';

?>


<script>
var valor = <?= json_encode((string) $cntHoje) ?>;
$('#dadosTme').html(valor);
var table_tme = <?= json_encode($table_tme) ?>;
$('#list-tme').html(table_tme);
</script>
