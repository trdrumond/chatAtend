<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

if ($infoUser['nivel_id'] > 2) {
    $qry = ' and id_contrato in (' . $infoUserConfig['contrato_id'] . ')';
} else {
    $qry = '';
}
$sql = "SELECT id_empresa, nome_empresa, contrato_id, (SELECT concat(nome_contrato, ' - ', uf) from tbl_contrato where id_contrato=contrato_id) as nome_contrato, (SELECT count(*) from tbl_user where empresa_id=id_empresa and ativo=1) as qtdUser, ativo from tbl_empresa where id_empresa<>'' $qry" . cnf_sql_order_ativo_nome('nome_empresa');
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Empresa', 'Empresas por contrato e usuários ativos');
$novoBtn = ($cad_cnf == 1)
    ? '<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>'
    : '';
cnf_page_actions($novoBtn);
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Nome empresa</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Ativos</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) { ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['nome_empresa']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <td class="text-center"><?= (int) $dados[$x]['qtdUser'] ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_empresa']) ?></td>
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
    $id = $dados[$x]['id_empresa'];
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-building"></i> Empresa: ' . htmlspecialchars($dados[$x]['nome_empresa']));
    cnf_form_section_open('Dados');
    cnf_field_input('name_alt_' . $id, 'Nome', ['value' => $dados[$x]['nome_empresa'], 'disabled' => true]);
    cnf_field_input('ctt_alt_' . $id, 'Contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Empresa ativa', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    cnf_modal_shell_close('feed_alt_' . $id, 'alt_' . $id);
    ?>
<script>
$(document).ready(function() {
    $("#status_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_<?= $id ?>:checked').val();
        altCtt(id, status);
    });
    $("#alt_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_<?= $id ?>:checked').val();
        altCtt(id, status);
    });
    function altCtt(id, status) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_emp.php", { id: id, status: status }, function(valor) {
            $("#feed_alt_<?= $id ?>").html(valor);
        });
    }
});
</script>
<?php } ?>

<?php if ($cad_cnf == 1) {
    $cttOpts = '<option value="">Selecione...</option>';
    $sql = 'SELECT id_contrato, nome_contrato, uf from tbl_contrato where ativo=1 and id_contrato in (' . $infoUserConfig['contrato_id'] . ') order by nome_contrato';
    $stmt = $PDO->prepare($sql);
    $stmt->execute();
    $ctts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ctts as $row) {
        $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
    }
    cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Nova empresa');
    cnf_form_section_open('Dados da empresa');
    cnf_field_input('name', 'Nome empresa', ['required' => true]);
    cnf_field_select('contrato', 'Contrato', $cttOpts, ['required' => true]);
    cnf_form_section_close();
    cnf_modal_shell_close('save_feed_cad');
    ?>
<script>
$(document).ready(function() {
    $("#save").click(function() {
        var nome = $('#name').val();
        var contrato = $('#contrato').val();
        saveCtt(nome, contrato);
    });
    $("#name").keyup(function() {
        $("#name").val(capitalize($("#name").val()));
    });
    function saveCtt(nome, contrato) {
        $("#save_feed_cad").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_emp.php", { nome: nome, contrato: contrato }, function(valor) {
            $("#save_feed_cad").html(valor);
        });
    }
});
</script>
<?php } ?>
<script type="text/javascript" src="js/load.js"></script>
