<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

if ($infoUser['nivel_id'] > 2) {
    $qry = ' and id_contrato in (' . $infoUserConfig['contrato_id'] . ')';
} else {
    $qry = '';
}
$sql = "SELECT id_agencia, nome_agencia, contrato_id, (SELECT concat(nome_contrato, ' - ', uf) from tbl_contrato where id_contrato=contrato_id) as nome_contrato, regional_id, (SELECT nome_regional from tbl_regional where id_regional=regional_id) as nome_regional, (SELECT count(*) from tbl_user where agencia_id=id_agencia and ativo=1) as qtdUser, ativo from tbl_agencia where id_agencia<>'' $qry" . cnf_sql_order_ativo_nome('nome_agencia');
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Agências', 'Agências por contrato, regional e usuários ativos');
cnf_page_actions('<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>');
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Nome agência</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Regional</th>
            <th class="text-center">Ativos</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) { ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['nome_agencia']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_regional'] ?? '') ?></td>
            <td class="text-center"><?= (int) $dados[$x]['qtdUser'] ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_agencia']) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php
cnf_table_wrap_close();
echo cnf_datatable_init('tabela', [
    'order' => cnf_datatable_order_ativo_nome(4),
]);
?>

<?php for ($x = 0; $x < $count; $x++) {
    $id = $dados[$x]['id_agencia'];
    $regOpts = '<option value="0"></option>';
    $sqlReg = 'SELECT id_regional, nome_regional from tbl_regional where ativo=1 and contrato_id=' . (int) $dados[$x]['contrato_id'];
    $stmt = $PDO->prepare($sqlReg);
    $stmt->execute();
    $regs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($regs as $row) {
        $sel = ($row['id_regional'] == $dados[$x]['regional_id']) ? ' selected' : '';
        $regOpts .= '<option value="' . $row['id_regional'] . '"' . $sel . '>' . htmlspecialchars($row['nome_regional']) . '</option>';
    }
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-store"></i> Agência: ' . htmlspecialchars($dados[$x]['nome_agencia']));
    cnf_form_section_open('Dados');
    cnf_field_input('name_alt_' . $id, 'Nome', ['value' => $dados[$x]['nome_agencia'], 'disabled' => true]);
    cnf_field_input('ctt_alt_' . $id, 'Contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Agência ativa', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    cnf_form_section_open('Regional');
    cnf_field_select('reg_alt_' . $id, 'Regional', $regOpts);
    cnf_form_section_close();
    echo '<div id="feed_alt_' . $id . '" class="cnf-feed"></div>';
    echo '</div></div></div></div>';
    ?>
<script>
$(document).ready(function() {
    $("#status_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var regional = $('#reg_alt_<?= $id ?>').val();
        var status = $('#status_<?= $id ?>:checked').val();
        altCtt(id, status, regional);
    });
    $("#reg_alt_<?= $id ?>").change(function() {
        var id = <?= $id ?>;
        var regional = $('#reg_alt_<?= $id ?>').val();
        var status = $('#status_<?= $id ?>:checked').val();
        altCtt(id, status, regional);
    });
    function altCtt(id, status, regional) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_age.php", { id: id, status: status, regional: regional }, function(valor) {
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
cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Nova agência');
cnf_form_section_open('Dados da agência');
cnf_field_input('name', 'Nome agência', ['required' => true]);
cnf_field_select('contrato', 'Contrato', $cttOpts, ['required' => true]);
cnf_field_select('regional', 'Regional', '<option value="0">Regional</option>');
cnf_form_section_close();
cnf_modal_shell_close('save_feed');
?>
<script>
$(document).ready(function() {
    $("#save").click(function() {
        var nome = $('#name').val();
        var contrato = $('#contrato').val();
        var regional = $('#regional').val();
        saveCtt(nome, contrato, regional);
    });
    $("#name").keyup(function() {
        $("#name").val(titleCase($("#name").val()));
    });
    $("#contrato").change(function() {
        var contrato = $('#contrato').val();
        loadRegional(contrato);
    });
    function saveCtt(nome, contrato, regional) {
        $("#save_feed").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_age.php", { nome: nome, contrato: contrato, regional: regional }, function(valor) {
            $("#save_feed").html(valor);
        });
    }
    function loadRegional(contrato) {
        $("#regional").html('Carregando...');
        $.post("staff/load_regional.php", { contrato: contrato }, function(valor) {
            $("#regional").html(valor);
        });
    }
});
</script>
<script type="text/javascript" src="js/load.js"></script>
