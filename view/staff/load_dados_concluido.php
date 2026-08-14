<?php
include("../cnf/session.php");

$contratoId = (int) ($_POST['contrato_id'] ?? 0);
$formId = (int) ($_POST['form_id'] ?? 0);
$respId = (int) ($_POST['resp_id'] ?? 0);

if ($contratoId != 0) {
    $params = [$contratoId, $formId];
    $sql = "SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m')=date_format(curdate(), '%Y-%m') and contrato_id=? and fila_id=?";
    if ($respId > 0) {
        $sql .= " and bko_resp=?";
        $params[] = $respId;
    }
    $stmt = $PDO->prepare($sql);
    $stmt->execute($params);
    $infoUserCnt = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $PDO->prepare("SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m')=date_format(curdate(), '%Y-%m') and contrato_id=? and fila_id=?");
    $stmt->execute([$contratoId, $formId]);
    $infoGeral = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($nivel_usu < 4) {
        $cntHoje = $infoGeral['qtd'] ?? 0;
    } else {
        $cntHoje = $infoUserCnt['qtd'] ?? 0;
    }
} else {
    $cttBind = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sql = "SELECT count(*) as qtd from tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and contrato_id in (" . $cttBind['ph'] . ")";
    $stmt = $PDO->prepare($sql);
    $stmt->execute($cttBind['ids']);
    $dadosHoje = $stmt->fetch(PDO::FETCH_ASSOC);
    $cntHoje = $dadosHoje['qtd'] ?? '';
    $cntHoje = ($cntHoje == '') ? '---' : $cntHoje;
}

$table_concluido = '<table class="table table-hover"><thrade><th>FILA</th><th>QTD</th></thrade><tbody>';
$sql = "SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd from tbl_config_fila a where a.ativo=1 order by a.nome_fila";
$stmt = $PDO_LOAD->prepare($sql);
$stmt->execute();
$dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($z = 0; $z < count($dadosContratos); $z++) {
    if ($dadosContratos[$z]['id_fila'] != '') {
        $table_concluido .= "<tr>";
        $table_concluido .= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['nome_fila'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_concluido .= "<td>" . htmlspecialchars((string) $dadosContratos[$z]['qtd'], ENT_QUOTES, 'UTF-8') . "</td>";
        $table_concluido .= "</tr>";
    }
}
$table_concluido .= '</tbody></table>';

?>


<script>
var valor = <?= json_encode((string) $cntHoje) ?>;
$('#dadosConcluido').html(valor);
var table_concluido = <?= json_encode($table_concluido) ?>;
$('#list-concluido').html(table_concluido);
</script>
