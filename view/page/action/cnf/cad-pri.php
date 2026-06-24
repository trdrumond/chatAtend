<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';

$sql = 'SELECT id_prioridade, nome_prioridade, peso, ativo from tbl_prioridade where id_prioridade > 0 and del=0' . cnf_sql_order_ativo_nome('nome_prioridade');
$stmt = $PDO->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($dados);

cnf_page_open('Cadastro de Prioridades', 'Prioridades e pesos para ordenação de demandas');
$novoBtn = ($cad_cnf == 1)
    ? '<button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro"><i class="fas fa-plus"></i> Novo</button>'
    : '';
cnf_page_actions($novoBtn);
cnf_table_wrap_open();
?>
<table id="tabela" class="table table-sm cnf-table">
    <thead>
        <tr>
            <th>Prioridade</th>
            <th class="text-center">Peso</th>
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
            <td><?= htmlspecialchars($dados[$x]['nome_prioridade']) ?></td>
            <td class="text-center"><?= (int) $dados[$x]['peso'] ?></td>
            <?= cnf_status_cell((int) $dados[$x]['ativo']) ?>
            <td class="text-center"><?= $sts ?></td>
            <td class="text-center"><?= cnf_action_icon('modal_alt_' . $dados[$x]['id_prioridade']) ?></td>
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
    $id = $dados[$x]['id_prioridade'];
    cnf_modal_shell_open('modal_alt_' . $id, '<i class="fas fa-sort-amount-up"></i> Prioridade: ' . htmlspecialchars($dados[$x]['nome_prioridade']));
    cnf_form_section_open('Dados');
    cnf_field_input('nome_alt_' . $id, 'Nome prioridade', [
        'value' => $dados[$x]['nome_prioridade'],
        'extra' => 'name="nome_alt_' . $id . '" min="1" max="100"',
    ]);
    cnf_field_input('peso_alt_' . $id, 'Peso', [
        'type' => 'number',
        'value' => $dados[$x]['peso'],
        'extra' => 'name="peso_alt_' . $id . '" min="1" max="100"',
    ]);
    cnf_form_section_close();
    cnf_form_section_open('Situação');
    cnf_field_switch('status_' . $id, 'Prioridade ativa', (int) $dados[$x]['ativo'] === 1);
    cnf_form_section_close();
    echo '</div>';
    echo '<div class="modal-footer cnf-modal-footer">';
    echo '<div id="feed_alt_' . $id . '" class="cnf-feed"></div>';
    echo '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>';
    echo '<button type="button" id="alt_' . $id . '" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar</button>';
    if ($infoUser['nivel_id'] < 1) {
        echo '<button type="button" id="del_' . $id . '" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>';
    }
    echo '</div></div></div></div>';
    ?>
<script>
$(document).ready(function() {
    $("#alt_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        var status = $('#status_<?= $id ?>:checked').val();
        var peso_alt = $('#peso_alt_<?= $id ?>').val();
        altCtt(id, status, peso_alt);
    });
    function altCtt(id, status, peso) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/alt_pri.php", { id, status, peso }, function(valor) {
            $("#feed_alt_<?= $id ?>").html(valor);
        });
    }
    <?php if ($infoUser['nivel_id'] < 1) { ?>
    $("#del_<?= $id ?>").click(function() {
        var id = <?= $id ?>;
        delCtt(id);
    });
    function delCtt(id) {
        $("#feed_alt_<?= $id ?>").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/del_pri.php", { id }, function(valor) {
            $("#feed_alt_<?= $id ?>").html(valor);
        });
    }
    <?php } ?>
});
</script>
<?php } ?>

<?php if ($cad_cnf == 1) {
    cnf_modal_shell_open('new_registro', '<i class="fas fa-plus-circle"></i> Nova prioridade');
    cnf_form_section_open('Dados da prioridade');
    cnf_field_input('nome', 'Nome prioridade', ['required' => true]);
    cnf_field_input('peso', 'Peso', ['type' => 'number', 'required' => true, 'extra' => 'min="1" max="100"']);
    cnf_form_section_close();
    cnf_modal_shell_close('save_feed_cad');
    ?>
<script>
$(document).ready(function() {
    $("#save").click(function() {
        var nome = $('#nome').val();
        var peso = $('#peso').val();
        saveRegistro(nome, peso);
    });
    $("#titulo").keyup(function() {
        $("#titulo").val(capitalize($("#titulo").val()));
    });
    function saveRegistro(nome, peso) {
        $("#save_feed_cad").html('<div class="spinner-border spinner-border-sm" role="status"></div>');
        $.post("staff/save_pri.php", { nome, peso }, function(valor) {
            $("#save_feed_cad").html(valor);
        });
    }
});
</script>
<?php } ?>
<script type="text/javascript" src="js/load.js"></script>
