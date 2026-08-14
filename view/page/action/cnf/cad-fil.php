<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

$cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
$listParams = [];
$qry = '';
if ($infoUser['nivel_id'] > 1) {
    $qry = ' and id_contrato in (' . $cttIn['ph'] . ')';
    $listParams = $cttIn['ids'];
}
$sql = "SELECT id_fila, nome_fila, assuntos_id, contrato_id, (SELECT concat(nome_contrato, ' - ', uf) from tbl_contrato where id_contrato=contrato_id) as nome_contrato, ativo, (SELECT count(*) from tbl_forms_pos_input_campo where fila_id=id_fila) as pos, (SELECT count(*) from tbl_forms_mon_input_campo where fila_id=id_fila) as mon, multichat from tbl_config_fila where id_fila<>'' $qry" . cnf_sql_order_ativo_nome('nome_fila');
$stmt = $PDO->prepare($sql);
$stmt->execute($listParams);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Filas', 'Filas por contrato, assuntos e configurações');
cnf_page_actions('<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>');
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Fila</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Pós-atendimento</th>
            <th class="text-center">Monitoria</th>
            <th class="text-center">Horários</th>
            <th class="text-center">Chats</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) {
            $posLabel = ($dados[$x]['pos'] == '') ? 'Padrão' : 'Personalizado';
            $monLabel = ($dados[$x]['mon'] == '') ? 'Sem Configuração' : 'Configurar';
            $hrLabel = $monLabel;
            ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['nome_fila']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <td class="text-center pointer" data-bs-toggle="modal" data-bs-target="#modal_pos_<?= $dados[$x]['id_fila'] ?>"><?= $posLabel ?></td>
            <td class="text-center pointer" data-bs-toggle="modal" data-bs-target="#modal_mon_<?= $dados[$x]['id_fila'] ?>"><?= $monLabel ?></td>
            <td class="text-center pointer" data-bs-toggle="modal" data-bs-target="#modal_hr_<?= $dados[$x]['id_fila'] ?>"><?= $hrLabel ?></td>
            <td class="text-center"><?= (int) $dados[$x]['multichat'] ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_fila']) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php
cnf_table_wrap_close();
echo cnf_datatable_init('tabela', [
    'order' => cnf_datatable_order_ativo_nome(6),
]);
?>

<?php for ($x = 0; $x < $count; $x++) {
    $id = $dados[$x]['id_fila'];
    $assOpts = '';
    $exp_ass = explode(',', $dados[$x]['assuntos_id']);
    $sqlAss = 'SELECT id_assunto, titulo_assunto from tbl_assunto where ativo=1 and contrato_id=? order by titulo_assunto asc';
    $stmt = $PDO->prepare($sqlAss);
    $stmt->execute([(int) $dados[$x]['contrato_id']]);
    $ls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ls as $row) {
        $sel = in_array($row['id_assunto'], $exp_ass) ? ' selected' : '';
        $assOpts .= '<option value="' . $row['id_assunto'] . '"' . $sel . '>' . htmlspecialchars($row['titulo_assunto']) . '</option>';
    }

    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-list"></i> Fila: ' . htmlspecialchars($dados[$x]['nome_fila']), 'lg');
    cnf_form_section_open('Dados');
    cnf_field_input('titulo_alt_ass_' . $id, 'Título', [
        'value' => $dados[$x]['nome_fila'],
        'disabled' => $infoUser['nivel_id'] > 0,
    ]);
    cnf_field_input('ctt_alt_ass_' . $id, 'Contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_field_input('multi_alt_ass_' . $id, 'MultiChat', [
        'type' => 'number',
        'value' => $dados[$x]['multichat'],
        'extra' => 'min="1" max="4"',
    ]);
    cnf_form_section_close();
    echo '<div' . ($infoUser['nivel_id'] > 0 ? ' style="display:none;"' : '') . '>';
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Fila ativa', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    echo '</div>';
    cnf_form_section_open('Assuntos vinculados');
    cnf_field_select('assuntos_sel_ass_' . $id, 'Assuntos', $assOpts, ['multiple' => true, 'name' => 'assunto_sel_' . $id . '[]']);
    cnf_form_section_close();
    cnf_modal_shell_close('feed_alt_' . $id, 'alt_ass_' . $id);
    ?>
<?php } ?>

<script>
$(function() {
    $('#action-page').on('click', '[id^="alt_ass_"]', function() {
        var id = this.id.replace('alt_ass_', '');
        var status = $('#status_' + id + ':checked').val();
        var titulo = $('#titulo_alt_ass_' + id).val();
        var multichat = $('#multi_alt_ass_' + id).val();
        var assuntos = $('#assuntos_sel_ass_' + id).val();
        $('#feed_alt_' + id).html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post('staff/alt_fil.php', { id: id, status: status, titulo: titulo, assuntos: assuntos, multichat: multichat }, function(valor) {
            $('#feed_alt_' + id).html(valor);
        });
    });
});
</script>

<?php for ($x = 0; $x < $count; $x++) {
    $id = $dados[$x]['id_fila'];
    ?>
<div class="modal fade" id="modal_pos_<?= $id ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-config">
        <div class="modal-content cnf-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-check"></i> Configurar Pós-Atendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body cnf-form st-form" id="div_config_form_<?= $id ?>">
                <?php
                $dados_form['id_fila'] = $id;
                include('staff/pos_config_form.php');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_mon_<?= $id ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-config">
        <div class="modal-content cnf-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-headset"></i> Configurar Monitoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body cnf-form st-form" id="div_config_form_mon_<?= $id ?>">
                <?php
                $dados_form['id_fila'] = $id;
                include('staff/mon_config_form.php');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_hr_<?= $id ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-config">
        <div class="modal-content cnf-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock"></i> Configuração de Horários</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body cnf-form st-form" id="div_config_form_hr_<?= $id ?>">
                <?php
                $dados_form['id_fila'] = $id;
                include('staff/hr_config_form.php');
                ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php
$cttOpts = '<option value="">Selecione...</option>';
$sql = 'SELECT id_contrato, nome_contrato, uf, ativo from tbl_contrato where ativo=1 and id_contrato in (' . $cttIn['ph'] . ') order by nome_contrato';
$stmt = $PDO->prepare($sql);
$stmt->execute($cttIn['ids']);
$ctts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($ctts as $row) {
    $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}
cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Nova fila', 'lg');
cnf_form_section_open('Dados da fila');
cnf_field_select('contrato', 'Contrato', $cttOpts, ['required' => true]);
cnf_field_input('titulo', 'Título', ['required' => true]);
cnf_field_input('multichat', 'MultiChat', ['type' => 'number', 'value' => '1', 'extra' => 'min="1" max="4"', 'required' => true]);
cnf_form_section_close();
cnf_form_section_open('Assuntos');
cnf_field_select('assuntos_sel', 'Assuntos', '', ['multiple' => true, 'name' => 'assunto_sel[]']);
cnf_form_section_close();
cnf_modal_shell_close('save_feed_cad');
?>
<script>
$(document).ready(function() {
    $("#contrato").change(function() {
        var contrato = $('#contrato').val();
        loadAssuntos(contrato);
    });

    $("#save").click(function() {
        var titulo = $('#titulo').val();
        var contrato = $('#contrato').val();
        var assunto = $('#assuntos_sel').val();
        var multichat = $('#multichat').val();
        saveRegistro(titulo, contrato, assunto, multichat);
    });

    $("#titulo").keyup(function() {
        $("#titulo").val(capitalize($("#titulo").val()));
    });

    function saveRegistro(titulo, contrato, assunto, multichat) {
        $("#save_feed_cad").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_fil.php", { titulo, contrato, assunto, multichat }, function(valor) {
            $("#save_feed_cad").html(valor);
        });
    }

    function loadAssuntos(contrato) {
        $("#assuntos_sel").html('Carregando...');
        $.post("staff/load_assunto.php", { contrato }, function(valor) {
            $("#assuntos_sel").html(valor);
        });
    }
});
</script>
<script type="text/javascript" src="js/load.js"></script>
