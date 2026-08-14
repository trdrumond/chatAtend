<?php
include("../cnf/session.php");

$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$filaId = (int) ($_POST['fila_id'] ?? 0);

if ($contratoId != 0) {
    $sql = "SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=2  and contrato_id=? and fila_id=?";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$contratoId, $filaId]);
    $infoGeral = $stmt->fetch(PDO::FETCH_ASSOC);
    $cntHoje = $infoGeral['qtd'] ?? 0;
} else {
    $cttBind = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql = "SELECT count(*) as qtd from tbl_chat_fila where status_fila=2 and contrato_id in (" . $cttBind['ph'] . ")";
    $stmt = $PDO->prepare($sql);
    $stmt->execute($cttBind['ids']);
    $dadosHoje = $stmt->fetch(PDO::FETCH_ASSOC);
    $cntHoje = $dadosHoje['qtd'] ?? '';
    $cntHoje = ($cntHoje == '') ? '---' : $cntHoje;
}

$table_atend = '<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sql = "SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=2 and date_format(hora_inicio, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
$stmt = $PDO_LOAD->prepare($sql);
$stmt->execute();
$dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($z = 0; $z < count($dadosContratos); $z++) {
    if ($dadosContratos[$z]['id_fila'] != '') {
        $table_atend .= "<tr>";
        $table_atend .= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['nome_fila'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_atend .= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['qtd'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_atend .= "</tr>";
    }
}
$table_atend .= '</tbody></table>';

?>


<script>
var valor = <?= json_encode((string) $cntHoje) ?>;
$('#dadosAtend').html(valor);
var table_atend = <?= json_encode($table_atend) ?>;
$('#list-atend').html(table_atend);
</script>
