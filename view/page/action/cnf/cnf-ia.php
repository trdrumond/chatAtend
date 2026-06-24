<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/_cnf_ui.php';
require_once __DIR__ . '/../../../cnf/st_ia_analytics.php';

$nivelLogin = (int) ($_SESSION['dados']['nivel_id'] ?? 99);
if ($nivelLogin > 1) {
    echo '<div class="alert alert-danger">Acesso negado.</div>';
    exit;
}

$schemaOk = stIaSchemaReady($PDO);
$keyOk = $schemaOk && stIaGetApiKey($PDO) !== null;

cnf_page_open('Configuração IA', 'Chave OpenAI para análise diária de indicadores (D+1)');
cnf_page_actions(
    '<a href="#" class="btn btn-outline-secondary btn-sm" onclick="window.actionPageNav(\'dash\', \'cnf\'); return false;">'
    . '<i class="fas fa-arrow-left"></i> Voltar</a>'
);
cnf_page_header_close();

if (!$schemaOk) {
    echo '<div class="alert alert-warning">Execute a migration <code>docs/sql/migration_ia_analise.sql</code> no banco de dados.</div>';
}

cnf_form_full_open();
cnf_form_section_open('Chave API OpenAI');
?>
<div class="cnf-field cnf-field--full">
    <label for="openai_api_key">Chave OpenAI</label>
    <input type="password" id="openai_api_key" name="openai_api_key" class="form-control"
        placeholder="sk-proj-..." autocomplete="off">
    <small class="form-text text-muted">
        Sem chave configurada, o sistema exibe apenas indicadores numéricos — sem interpretação por IA.
        <?php if ($keyOk) { ?>
        <span class="text-success">Chave atualmente configurada.</span>
        <?php } ?>
    </small>
</div>
<?php
cnf_form_section_close();
cnf_form_full_close();
?>

<div class="cnf-form-actions">
    <button type="button" id="btn_save_ia_key" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar chave</button>
    <button type="button" id="btn_clear_ia_key" class="btn btn-outline-danger"><i class="fas fa-trash"></i> Remover chave</button>
</div>

<script>
$(function () {
    $('#btn_save_ia_key').on('click', function () {
        var key = $('#openai_api_key').val();
        if (!key) {
            Swal.fire('Atenção', 'Informe a chave ou use Remover chave.', 'warning');
            return;
        }
        $.post('staff/save_ia_config.php', { openai_api_key: key }, function (r) {
            if (r && r.ok) {
                Swal.fire('Sucesso', r.mensagem || 'Chave salva', 'success');
                $('#openai_api_key').val('');
            } else {
                Swal.fire('Erro', (r && r.error) || 'Falha ao salvar', 'error');
            }
        }, 'json').fail(function () {
            Swal.fire('Erro', 'Falha na comunicação', 'error');
        });
    });
    $('#btn_clear_ia_key').on('click', function () {
        Swal.fire({
            title: 'Remover chave?',
            text: 'A funcionalidade de IA ficará desativada.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post('staff/save_ia_config.php', { openai_api_key: '' }, function (r) {
                if (r && r.ok) {
                    Swal.fire('Removida', r.mensagem || 'Chave removida', 'success');
                } else {
                    Swal.fire('Erro', (r && r.error) || 'Falha', 'error');
                }
            }, 'json');
        });
    });
});
</script>

</div>

