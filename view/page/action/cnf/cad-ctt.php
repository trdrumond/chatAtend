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
$sql = "SELECT id_contrato, nome_contrato, ativo, uf, com, new_conv, grupos, men_massa, resp_men, env_file, env_img, nome_robo, (SELECT count(*) from tbl_user where contrato_id=id_contrato and ativo=1) as qtdUser from tbl_contrato where id_contrato<>'' $qry" . cnf_sql_order_ativo_nome('nome_contrato');
$stmt = $PDO->prepare($sql);
$stmt->execute($listParams);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Contratos', 'Contratos, usuários ativos e configurações de comunicação');
cnf_page_actions('<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>');
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Nome contrato</th>
            <th class="text-center">UF</th>
            <th class="text-center">Ativos</th>
            <th class="text-center">Configuração</th>
            <th class="text-center">Situação</th>
            <th class="text-center cnf-col-act">Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($x = 0; $x < $count; $x++) { ?>
        <tr>
            <td><?= htmlspecialchars($dados[$x]['nome_contrato']) ?></td>
            <td class="text-center"><?= htmlspecialchars($dados[$x]['uf']) ?></td>
            <td class="text-center"><?= (int) $dados[$x]['qtdUser'] ?></td>
            <td class="text-center pointer" data-bs-toggle="modal" data-bs-target="#modal_com_<?= $dados[$x]['id_contrato'] ?>">Configurar</td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_contrato']) ?></td>
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
    $id = $dados[$x]['id_contrato'];
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-file-contract"></i> Contrato: ' . htmlspecialchars($dados[$x]['nome_contrato']));
    cnf_form_section_open('Dados');
    cnf_field_input('name_alt_' . $id, 'Nome contrato', ['value' => $dados[$x]['nome_contrato'], 'disabled' => true]);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Contrato ativo', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    cnf_modal_shell_close('feed_alt_' . $id, 'alt_' . $id, false);
    ?>
<script>
$(document).ready(function() {
    $("#status_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_<?= $id ?>:checked').val();
        altCtt(id, status);
    });

    function altCtt(id, status) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt.php", { id: id, status: status, status: status }, function(valor) {
            $("#feed_alt_<?= $id ?>").html(valor);
        });
    }
});
</script>
<?php } ?>

<?php for ($x = 0; $x < $count; $x++) {
    $id = $dados[$x]['id_contrato'];
    ?>
<div class="modal fade" id="modal_com_<?= $id ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content cnf-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cog"></i> Configurações do Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body cnf-form st-form">
    <?php
    cnf_form_section_open('Comunicação');
    cnf_field_switch('status_com_' . $id, 'Comunicação', (int) $dados[$x]['com'] === 1);
    cnf_field_switch('status_com_new_conv_' . $id, 'Novas conversas', (int) $dados[$x]['new_conv'] === 1);
    cnf_field_switch('status_com_grupo_' . $id, 'Grupos', (int) $dados[$x]['grupos'] === 1);
    cnf_field_switch('status_com_men_massa_' . $id, 'Mensagem em massa', (int) $dados[$x]['men_massa'] === 1);
    cnf_field_switch('status_com_resp_men_' . $id, 'Responder mensagens (exceto Adm)', (int) $dados[$x]['resp_men'] === 1);
    cnf_field_switch('status_env_file_' . $id, 'Envio de arquivo', (int) $dados[$x]['env_file'] === 1);
    cnf_field_switch('status_env_img_' . $id, 'Envio de imagem', (int) $dados[$x]['env_img'] === 1);
    cnf_form_section_close();
    cnf_form_section_open('Assistente virtual (fila)');
    $nomeRoboVal = trim((string) ($dados[$x]['nome_robo'] ?? ''));
    cnf_field_input('nome_robo_' . $id, 'Nome do robô', [
        'value' => $nomeRoboVal,
        'placeholder' => 'Robô Logos (padrão se vazio)',
        'maxlength' => 80,
    ]);
    echo '<p class="st-form-hint cnf-form-hint">Exibido na tela de fila do solicitante. Deixe em branco para usar &quot;Robô Logos&quot;.</p>';
    echo '<div class="d-flex align-items-center gap-2 mt-2">';
    echo '<button type="button" class="btn btn-solvetask btn-sm" id="save_nome_robo_' . $id . '"><i class="fas fa-save"></i> Salvar nome do robô</button>';
    echo '</div>';
    cnf_form_section_close();
    echo '<div id="feed_com_' . $id . '" class="cnf-feed"></div>';
    ?>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $("#status_env_img_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_env_img_<?= $id ?>:checked').val();
        altCttEnvImg(id, status);
    });

    function altCttEnvImg(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_env_img.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#status_env_file_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_env_file_<?= $id ?>:checked').val();
        altCttEnvFile(id, status);
    });

    function altCttEnvFile(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_env_file.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#status_com_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_com_<?= $id ?>:checked').val();
        altCttCom(id, status);
    });

    function altCttCom(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_com.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#status_com_new_conv_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_com_new_conv_<?= $id ?>:checked').val();
        altCttComNewConv(id, status);
    });

    function altCttComNewConv(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_com_new_conv.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#status_com_grupo_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_com_grupo_<?= $id ?>:checked').val();
        altCttComGrupos(id, status);
    });

    function altCttComGrupos(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_com_grupos.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#status_com_men_massa_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_com_men_massa_<?= $id ?>:checked').val();
        altCttComMenMassa(id, status);
    });

    function altCttComMenMassa(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_com_men_massa.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#status_com_resp_men_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_com_resp_men_<?= $id ?>:checked').val();
        altCttComRespMen(id, status);
    });

    function altCttComRespMen(id, status) {
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_com_resp_men.php", { id, status }, function(valor) {
            $("#feed_com_<?= $id ?>").html(valor);
        });
    }

    $("#save_nome_robo_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var nomeRobo = $('#nome_robo_<?= $id ?>').val();
        $("#feed_com_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_ctt_nome_robo.php", { id: id, nome_robo: nomeRobo }, function(resp) {
            var data = resp;
            if (typeof resp === 'string') {
                try { data = JSON.parse(resp); } catch (e) { data = {}; }
            }
            if (data.ok) {
                $("#feed_com_<?= $id ?>").html('<span class="text-success">Nome do robô salvo.</span>');
            } else {
                $("#feed_com_<?= $id ?>").html('<span class="text-danger">Não foi possível salvar.</span>');
            }
        }).fail(function() {
            $("#feed_com_<?= $id ?>").html('<span class="text-danger">Erro ao salvar.</span>');
        });
    });
});
</script>
<?php } ?>

<?php
$ufOpts = '<option value="">Selecione...</option>';
$sql = 'SELECT nome_estado, id_estado, uf from tbl_estado order by nome_estado';
$stmt = $PDO->prepare($sql);
$stmt->execute();
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($estados as $row) {
    $ufOpts .= '<option value="' . htmlspecialchars($row['uf']) . '">' . htmlspecialchars($row['nome_estado'] . ' - ' . $row['uf']) . '</option>';
}
cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Novo contrato');
cnf_form_section_open('Dados do contrato');
cnf_field_input('name', 'Nome contrato', ['required' => true]);
cnf_field_select('uf', 'UF', $ufOpts, ['required' => true]);
cnf_form_section_close();
cnf_modal_shell_close('save_feed');
?>
<script>
$(document).ready(function() {
    $("#save").click(function() {
        var nome = $('#name').val();
        var uf = $('#uf').val();
        saveCtt(nome, uf);
    });

    $("#name").keyup(function() {
        $("#name").val(capitalize($("#name").val()));
    });

    function saveCtt(nome, uf) {
        $("#save_feed").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_ctt.php", { nome: nome, uf: uf }, function(valor) {
            $("#save_feed").html(valor);
        });
    }
});
</script>
<script type="text/javascript" src="js/load.js"></script>
