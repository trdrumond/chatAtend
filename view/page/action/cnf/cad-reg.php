<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

if ($infoUser['nivel_id'] > 2) {
    $qry = ' and id_contrato in (' . $infoUserConfig['contrato_id'] . ')';
} else {
    $qry = '';
}
$sql = "SELECT id_regional, nome_regional, contrato_id, (SELECT concat(nome_contrato, ' - ', uf) from tbl_contrato where id_contrato=contrato_id) as nome_contrato, (SELECT count(*) from tbl_user where regional_id=id_regional and ativo=1) as qtdUser, ativo from tbl_regional where id_regional<>'' $qry" . cnf_sql_order_ativo_nome('nome_regional');
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Regional', 'Regionais por contrato e vínculo com agências');
cnf_page_actions('<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>');
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Nome regional</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Ativos</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) { ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['nome_regional']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <td class="text-center"><?= (int) $dados[$x]['qtdUser'] ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_regional']) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php
cnf_table_wrap_close();
echo cnf_datatable_init('tabela', [
    'order' => cnf_datatable_order_ativo_nome(3),
]);
?>

<?php for ($x = 0; $x < $count; $x++) {
    $id = $dados[$x]['id_regional'];
    $agOpts = '';
    $sqlAg = 'SELECT id_agencia, nome_agencia, regional_id from tbl_agencia where ativo=1 and contrato_id=' . (int) $dados[$x]['contrato_id'];
    $stmt = $PDO->prepare($sqlAg);
    $stmt->execute();
    $info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($info as $row) {
        $sel = ($row['regional_id'] == $id) ? ' selected' : '';
        $agOpts .= '<option value="' . $row['id_agencia'] . '"' . $sel . '>' . htmlspecialchars($row['nome_agencia']) . '</option>';
    }
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-map-marker-alt"></i> Regional: ' . htmlspecialchars($dados[$x]['nome_regional']), 'lg');
    cnf_form_section_open('Dados');
    cnf_field_input('name_alt_' . $id, 'Nome', ['value' => $dados[$x]['nome_regional'], 'disabled' => true]);
    cnf_field_input('ctt_alt_' . $id, 'Contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Regional ativa', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    cnf_form_section_open('Agências vinculadas');
    cnf_field_select('age_alt_' . $id, 'Agências', $agOpts, ['multiple' => true]);
    cnf_form_section_close();
    cnf_modal_shell_close('feed_alt_' . $id, 'alt_' . $id);
    ?>
<script>
$(document).ready(function() {
    $("#status_<?= $id ?>").click(function() {
        altCtt(<?= $id ?>, $('#status_<?= $id ?>:checked').val(), $('#age_alt_<?= $id ?>').val());
    });
    $("#alt_<?= $id ?>").click(function() {
        altCtt(<?= $id ?>, $('#status_<?= $id ?>:checked').val(), $('#age_alt_<?= $id ?>').val());
    });
    function altCtt(id, status, agencias) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_reg.php", { id: id, status: status, agencias: agencias }, function(valor) {
            $("#feed_alt_<?= $id ?>").html(valor);
        });
    }
});
</script>
<?php } ?>

<?php
$cttOpts = '<option value="">Selecione...</option>';
$sql = 'SELECT id_contrato, nome_contrato, uf from tbl_contrato where ativo=1 and id_contrato in (' . $infoUserConfig['contrato_id'] . ') order by nome_contrato';
$stmt = $PDO->prepare($sql);
$stmt->execute();
$ctts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($ctts as $row) {
    $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}
cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Nova regional');
cnf_form_section_open('Dados da regional');
cnf_field_input('name', 'Nome regional', ['required' => true]);
cnf_field_select('contrato', 'Contrato', $cttOpts, ['required' => true]);
cnf_form_section_close();
cnf_modal_shell_close('save_feed_cad');
?>
<script>
$(document).ready(function() {
    $("#save").click(function() {
        $("#save_feed_cad").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_reg.php", { nome: $('#name').val(), contrato: $('#contrato').val() }, function(valor) {
            $("#save_feed_cad").html(valor);
        });
    });
    $("#name").keyup(function() { $("#name").val(capitalize($("#name").val())); });
});
</script>
<script type="text/javascript" src="js/load.js"></script>
