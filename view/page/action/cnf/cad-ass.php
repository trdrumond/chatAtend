<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

if ($infoUser['nivel_id'] > 2) {
    $qry = ' and id_contrato in (' . $infoUserConfig['contrato_id'] . ')';
} else {
    $qry = '';
}
$sql = "SELECT id_assunto, titulo_assunto, procedimento, contrato_id, (SELECT concat(nome_contrato, ' - ', uf) from tbl_contrato where id_contrato=contrato_id) as nome_contrato, date_format(data_hora, '%Y-%m-%d') as data_cad, date_format(data_alt, '%Y-%m-%d') as data_alt, date_format(data_alt, '%H:%i:%s') as hora_alt, ativo from tbl_assunto where id_assunto<>'' $qry" . cnf_sql_order_ativo_nome('titulo_assunto');
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Assuntos', 'Assuntos por contrato e procedimentos vinculados');
cnf_page_actions('<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>');
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Assunto</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Situação</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) {
            $sts = ((int) $dados[$x]['ativo'] === 1) ? 'ONLINE' : 'OFFLINE';
        ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['titulo_assunto']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= htmlspecialchars($sts) ?></td>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_assunto']) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php
cnf_table_wrap_close();
echo cnf_datatable_init('tabela', [
    'columnDefs' => '[{ targets: [3], visible: false }]',
    'order' => cnf_datatable_order_ativo_nome(2),
]);
?>

<?php for ($x = 0; $x < $count; $x++) {
    $id = $dados[$x]['id_assunto'];
    $procAltId = 'procedimento_alt_' . $id;
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-book"></i> Assunto: ' . htmlspecialchars($dados[$x]['titulo_assunto']), 'xl');
    cnf_form_section_open('Dados');
    cnf_field_input('titulo_alt_' . $id, 'Título', ['value' => $dados[$x]['titulo_assunto']]);
    cnf_field_input('ctt_alt_' . $id, 'Contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Assunto ativo', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    cnf_form_section_open('Procedimento');
    cnf_field_textarea($procAltId, 'Procedimento', ['value' => $dados[$x]['procedimento'], 'rows' => 20, 'full' => true]);
    cnf_form_section_close();
    cnf_form_section_open('Histórico');
    cnf_field_input('data_cad_' . $id, 'Cadastrado em', ['type' => 'date', 'value' => $dados[$x]['data_cad'], 'disabled' => true]);
    cnf_field_input('data_alt_' . $id, 'Última atualização', ['type' => 'date', 'value' => $dados[$x]['data_alt'], 'disabled' => true]);
    cnf_field_input('hora_alt_' . $id, 'Hora', ['type' => 'time', 'value' => $dados[$x]['hora_alt'], 'disabled' => true]);
    cnf_form_section_close();
    cnf_modal_shell_close('feed_alt_' . $id, 'alt_' . $id);
} ?>

<?php
cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Novo assunto', 'xl');
cnf_form_section_open('Dados do assunto');
$cttOpts = '<option value="">Selecione...</option>';
$sql = 'SELECT id_contrato, nome_contrato, uf from tbl_contrato where ativo=1 and id_contrato in (' . $infoUserConfig['contrato_id'] . ') order by nome_contrato';
$stmt = $PDO->prepare($sql);
$stmt->execute();
$ctts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($ctts as $row) {
    $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}
cnf_field_select('contrato', 'Contrato', $cttOpts, ['required' => true]);
cnf_field_input('titulo', 'Título', ['required' => true]);
cnf_form_section_close();
cnf_form_section_open('Procedimento');
cnf_field_textarea('procedimento_new', 'Procedimento', ['rows' => 20, 'full' => true]);
cnf_form_section_close();
cnf_modal_shell_close('save_feed_cad');
?>
<script>
$(function() {
    var tinyOpts = {
        menubar: false,
        height: 400,
        branding: false,
        promotion: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'bold italic alignleft aligncenter alignright alignjustify bullist numlist',
        invalid_elements: 'div,span,a,nav,code,h1,h2,h3,h4,h5,script,style,tr,table,td,javascript',
        content_style: 'body { font-size:12px }'
    };

    function procVal(id) {
        return (typeof stTinyMceGetContent === 'function') ? stTinyMceGetContent(id) : $('#' + id).val();
    }

    $('#action-page').on('click', '[id^="alt_"]', function() {
        var id = this.id.replace(/^alt_/, '');
        if (!/^\d+$/.test(id)) {
            return;
        }
        var procId = 'procedimento_alt_' + id;
        $('#feed_alt_' + id).html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post('staff/alt_ass.php', {
            id: id,
            status: $('#status_' + id + ':checked').val(),
            titulo: $('#titulo_alt_' + id).val(),
            procedimento: procVal(procId)
        }, function(valor) {
            $('#feed_alt_' + id).html(valor);
        });
    });

    $('#action-page').on('click', '#save', function() {
        $('#save_feed_cad').html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post('staff/save_ass.php', {
            titulo: $('#titulo').val(),
            contrato: $('#contrato').val(),
            procedimento: procVal('procedimento_new')
        }, function(valor) {
            $('#save_feed_cad').html(valor);
        });
    });

    $('#action-page').on('keyup', '#titulo', function() {
        $(this).val(capitalize($(this).val()));
    });

    if (typeof stTinyMceApplyOnModal === 'function') {
        $('#action-page [id^="modal_alt_"]').each(function() {
            var assuntoId = this.id.replace('modal_alt_', '');
            stTinyMceApplyOnModal(this, '#procedimento_alt_' + assuntoId, tinyOpts);
        });
        stTinyMceApplyOnModal('#new_registro', '#procedimento_new', tinyOpts);
    }
});
</script>
<script type="text/javascript" src="js/load.js"></script>
