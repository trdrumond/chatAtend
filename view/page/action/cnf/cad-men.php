<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

$cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
$listParams = [];
if ($infoUser['nivel_id'] > 2) {
    $qry = ' and id_contrato in (' . $cttIn['ph'] . ')';
    $listParams = $cttIn['ids'];
} else {
    $qry = '';
}
$sql = "SELECT id_campo, titulo_men, txt, contrato_id, (SELECT concat(nome_contrato, ' - ', uf) from tbl_contrato where id_contrato=contrato_id) as nome_contrato, assunto_id, (SELECT concat(titulo_assunto) from tbl_assunto where id_assunto=assunto_id) as nome_assunto, ativo from tbl_config_men_ini where id_campo<>'' $qry" . cnf_sql_order_ativo_nome('titulo_men');
$stmt = $PDO->prepare($sql);
$stmt->execute($listParams);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Mensagens Predefinidas', 'Mensagens iniciais por contrato e assunto');
cnf_page_actions('<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>');
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Título</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Assunto</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) {
            $nomeAssunto = ((int) $dados[$x]['assunto_id'] === 0) ? 'Todos' : $dados[$x]['nome_assunto'];
        ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['titulo_men']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <td class="text-center"><?= htmlspecialchars($nomeAssunto) ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_campo']) ?></td>
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
    $id = $dados[$x]['id_campo'];
    $msgAltId = 'mensagem_alt_' . $id . '_' . time();
    $assOpts = '<option value="0"' . ((int) $dados[$x]['assunto_id'] === 0 ? ' selected' : '') . '>Todos</option>';
    $sqlAss = 'SELECT id_assunto, titulo_assunto from tbl_assunto where ativo=1 and contrato_id=? order by titulo_assunto';
    $stmt = $PDO->prepare($sqlAss);
    $stmt->execute([(int) $dados[$x]['contrato_id']]);
    $dd = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dd as $row) {
        $sel = ((int) $dados[$x]['assunto_id'] === (int) $row['id_assunto']) ? ' selected' : '';
        $assOpts .= '<option value="' . $row['id_assunto'] . '"' . $sel . '>' . htmlspecialchars($row['titulo_assunto']) . '</option>';
    }
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-comment-dots"></i> Mensagem: ' . htmlspecialchars($dados[$x]['titulo_men']), 'xl');
    cnf_form_section_open('Dados');
    cnf_field_input('titulo_alt_' . $id, 'Título', ['value' => $dados[$x]['titulo_men']]);
    cnf_field_input('ctt_alt_' . $id, 'Contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_field_select('ass_alt_' . $id, 'Assunto', $assOpts);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Mensagem ativa', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    cnf_form_section_open('Mensagem');
    cnf_field_textarea($msgAltId, 'Mensagem', ['value' => $dados[$x]['txt'], 'full' => true]);
    cnf_form_section_close();
    cnf_modal_shell_close('feed_alt_' . $id, 'alt_' . $id);
    ?>
<script>
$(document).ready(function() {
    function altCtt(id, status, titulo, assunto, mensagem) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_men_config.php", { id: id, status: status, titulo: titulo, assunto: assunto, mensagem: mensagem }, function(valor) {
            $("#feed_alt_<?= $id ?>").html(valor);
        });
    }
    $("#status_<?= $id ?>").click(function() {
        altCtt(<?= $id ?>, $('#status_<?= $id ?>:checked').val(), $('#titulo_alt_<?= $id ?>').val(), $('#ass_alt_<?= $id ?>').val(), $('#<?= $msgAltId ?>').val());
    });
    $("#alt_<?= $id ?>").click(function() {
        altCtt(<?= $id ?>, $('#status_<?= $id ?>:checked').val(), $('#titulo_alt_<?= $id ?>').val(), $('#ass_alt_<?= $id ?>').val(), $('#<?= $msgAltId ?>').val());
    });
    <?php if ((int) $infoUser['env_img'] === 1) { ?>
    stTinyMceApply('textarea#<?= $msgAltId ?>', {
        menubar: false,
        height: 200,
        branding: false,
        promotion: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'bold italic alignleft aligncenter alignright alignjustify bullist numlist',
        invalid_elements: "div,span,a,nav,code,h1,h2,h3,h4,h5,script,style,tr,table,td,javascript",
        content_style: 'body { font-size:12px }'
    });
    <?php } ?>
});
</script>
<?php } ?>

<?php
$msgNewId = 'mensagem_' . time();
$cttOpts = '<option value="">Selecione...</option>';
$sql = 'SELECT id_contrato, nome_contrato, uf from tbl_contrato where ativo=1 and id_contrato in (' . $cttIn['ph'] . ') order by nome_contrato';
$stmt = $PDO->prepare($sql);
$stmt->execute($cttIn['ids']);
$ctts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($ctts as $row) {
    $cttOpts .= '<option value="' . $row['id_contrato'] . '">' . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}
cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Nova mensagem', 'xl');
cnf_form_section_open('Dados da mensagem');
cnf_field_input('titulo', 'Título', ['required' => true]);
cnf_field_select('contrato', 'Contrato', $cttOpts, ['required' => true]);
cnf_field_select('fila', 'Fila', '<option value="">Selecione...</option>');
cnf_field_select('assunto', 'Assunto', '<option value="">Selecione...</option>');
cnf_form_section_close();
cnf_form_section_open('Mensagem');
cnf_field_textarea($msgNewId, 'Mensagem', ['full' => true]);
cnf_form_section_close();
cnf_modal_shell_close('save_feed_cad');
?>
<script>
$(document).ready(function() {
    $("#save").click(function() {
        saveCtt($('#titulo').val(), $('#<?= $msgNewId ?>').val(), $('#contrato').val(), $('#assunto').val());
    });
    $("#contrato").change(function() { loadFila($('#contrato').val()); });
    $("#fila").change(function() { loadAssuntos($('#fila').val()); });
    $("#titulo").keyup(function() { $("#titulo").val(capitalize($("#titulo").val())); });
    function saveCtt(titulo, mensagem, contrato, assunto) {
        $("#save_feed_cad").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_men_config.php", { titulo: titulo, mensagem: mensagem, contrato: contrato, assunto: assunto }, function(valor) {
            $("#save_feed_cad").html(valor);
        });
    }
    function loadAssuntos(fila) {
        $("#assunto").html('Carregando...');
        $.post("staff/load_assunto_men.php", { fila: fila }, function(valor) {
            $("#assunto").html(valor);
        });
    }
    function loadFila(contrato) {
        $("#fila").html('Carregando...');
        $.post("staff/load_fila.php", { contrato: contrato }, function(valor) {
            $("#fila").html(valor);
        });
    }
    <?php if ((int) $infoUser['env_img'] === 1) { ?>
    stTinyMceApply('textarea#<?= $msgNewId ?>', {
        menubar: false,
        height: 200,
        branding: false,
        promotion: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'bold italic alignleft aligncenter alignright alignjustify bullist numlist',
        invalid_elements: "div,span,a,nav,code,h1,h2,h3,h4,h5,script,style,tr,table,td,javascript",
        content_style: 'body { font-size:12px }'
    });
    <?php } ?>
});
</script>
<script type="text/javascript" src="js/load.js"></script>
