<?php
include("../cnf/session.php");
if (function_exists('session_write_close')) {
    session_write_close();
}
require_once __DIR__ . '/../page/action/cnf/_cnf_ui.php';

$chatId = (int) ($_POST['chatId'] ?? 0);
$indice = isset($_POST['indice']) ? htmlspecialchars((string) $_POST['indice'], ENT_QUOTES, 'UTF-8') : '';

$stmtChat = $PDO->prepare(
    'SELECT ci.fila_id, ci.token_chat, ci.assunto_id, ci.fila_chat_id,
            cf.nome_fila, cf.assuntos_id AS assuntos,
            tcf.protocolo,
            (SELECT COUNT(*) FROM tbl_forms_pos_input_campo WHERE fila_id = ci.fila_id) AS qtd_campos
     FROM tbl_chat_info ci
     INNER JOIN tbl_config_fila cf ON cf.id_fila = ci.fila_id
     LEFT JOIN tbl_chat_fila tcf ON tcf.id_fila_chat = ci.fila_chat_id
     WHERE ci.id_chat = ?'
);
$stmtChat->execute([$chatId]);
$fil = $stmtChat->fetch(PDO::FETCH_ASSOC);

if (!$fil) {
    echo '<p class="text-danger">Atendimento não encontrado.</p>';
    exit;
}

$nomeFila = (string) ($fil['nome_fila'] ?? '');
$fila = ['qtd' => (int) ($fil['qtd_campos'] ?? 0)];

$campoConfig = [];
$campoScript = [];
$optionsByCampo = [];

if ($fila['qtd'] >= 1) {
    include_once("../cnf/func_input.php");

    $filaId = (int) $fil['fila_id'];
    $sql = "SELECT a.campo_id AS id_campo, a.fila_id, b.nome_fila, a.input_id, c.nome_input, c.tipo_input,"
        . " d.desc_campo, d.nome_campo, a.ativo, a.date_time, a.ordem, a.obg"
        . " FROM tbl_forms_pos_input_campo_cnf a, tbl_config_fila b, tbl_forms_pos_input c, tbl_forms_pos_input_campo d"
        . " WHERE b.id_fila = ? AND b.id_fila = a.fila_id AND a.ativo = 1"
        . " AND a.fila_id = b.id_fila AND a.input_id = c.id_input AND a.campo_id = d.id_campo"
        . " ORDER BY ordem ASC";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$filaId]);
    $campoConfig = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($campoConfig as $cfg) {
        $campoScript[] = [
            'nome_campo' => $cfg['nome_campo'],
            'input_id' => $cfg['input_id'],
            'tipo_input' => $cfg['tipo_input'],
        ];
    }

    if (count($campoConfig) > 0) {
        $idsCampo = array_unique(array_map(static function ($c) {
            return (int)$c['id_campo'];
        }, $campoConfig));
        $optBind = stSqlInBind(array_values($idsCampo));
        $sqlOpt = "SELECT id_option, campo_id, desc_option, referencia, value_option, ativo"
            . " FROM tbl_forms_pos_input_option WHERE campo_id IN (" . $optBind['ph'] . ")";
        $stmtOpt = $PDO->prepare($sqlOpt);
        $stmtOpt->execute($optBind['params']);
        while ($rowOpt = $stmtOpt->fetch(PDO::FETCH_ASSOC)) {
            $cid = (int)$rowOpt['campo_id'];
            if (!isset($optionsByCampo[$cid])) {
                $optionsByCampo[$cid] = [];
            }
            $optionsByCampo[$cid][] = $rowOpt;
        }
    }
}

$assuntoOpts = '';
$assuntosIds = stParseIdCsv((string) ($fil['assuntos'] ?? ''));
if ($assuntosIds !== []) {
    $assBind = stSqlInBind($assuntosIds);
    $stmtAss = $PDO->prepare(
        'SELECT id_assunto, titulo_assunto FROM tbl_assunto'
        . ' WHERE ativo = 1 AND id_assunto IN (' . $assBind['ph'] . ') ORDER BY titulo_assunto ASC'
    );
    $stmtAss->execute($assBind['params']);
    $ass = $stmtAss->fetchAll(PDO::FETCH_ASSOC);
    foreach ($ass as $row) {
        $sel = ((int) $row['id_assunto'] === (int) $fil['assunto_id']) ? ' selected' : '';
        $assuntoOpts .= '<option value="' . (int) $row['id_assunto'] . '"' . $sel . '>'
            . htmlspecialchars($row['titulo_assunto']) . '</option>';
    }
}
?>
<div class="st-pos-page cnf-page">
    <header class="cnf-header st-pos-header">
        <div>
            <h5 class="cnf-title"><i class="fas fa-clipboard-check"></i> Pós-Atendimento</h5>
            <?php if ($nomeFila !== '') { ?>
            <p class="cnf-sub"><?= htmlspecialchars($nomeFila) ?></p>
            <?php } ?>
            <?php if (!empty($fil['protocolo'])) { ?>
            <p class="st-pos-protocol"><i class="fas fa-hashtag" aria-hidden="true"></i> Protocolo <?= htmlspecialchars($fil['protocolo']) ?></p>
            <?php } ?>
        </div>
    </header>

    <form class="st-pos-body cnf-form st-form" id="body-pos_<?= $chatId ?>" method="post" action="#" novalidate>
        <?php if ($fila['qtd'] < 1) {
            $ex = array_filter(array_map('intval', explode(',', (string) ($fil['assuntos'] ?? ''))));
            cnf_form_section_open('Serviço realizado');
            echo '<p class="st-pos-intro cnf-form-hint">Selecione o serviço que melhor descreve o que foi feito neste atendimento.</p>';
            echo '<div class="st-pos-questions">';
            echo '<article class="st-pos-question st-pos-question--single">';
            echo '<div class="st-pos-question__head">';
            echo '<span class="st-pos-question__badge">Pergunta</span>';
            echo '<span class="st-pos-question__title">Qual serviço foi realizado?</span>';
            echo '</div>';
            $assuntoOptions = [];
            if (count($ex) > 0) {
                $ass2Bind = stSqlInBind(array_values($ex));
                $stmtAss2 = $PDO->prepare(
                    'SELECT id_assunto, titulo_assunto FROM tbl_assunto WHERE id_assunto IN (' . $ass2Bind['ph'] . ') ORDER BY titulo_assunto ASC'
                );
                $stmtAss2->execute($ass2Bind['params']);
                $assuntoOptions = $stmtAss2->fetchAll(PDO::FETCH_ASSOC);
            }
            $radioPairClass = count($assuntoOptions) === 2 ? ' st-radio-group--pair' : '';
            echo '<div class="st-pos-question__answers st-radio-group cnf-field-full' . $radioPairClass . '">';
            foreach ($assuntoOptions as $assunt) {
                $radioId = 'assunto_' . (int)$assunt['id_assunto'] . '_' . $chatId;
                echo '<label class="st-radio-option st-pos-answer" for="' . htmlspecialchars($radioId) . '">';
                echo '<input type="radio" id="' . htmlspecialchars($radioId) . '" name="assunto_' . $chatId . '"'
                    . ' value="' . (int)$assunt['id_assunto'] . '" class="form-check-input" required>';
                echo '<span class="st-pos-answer__text">' . htmlspecialchars($assunt['titulo_assunto']) . '</span>';
                echo '</label>';
            }
            echo '</div></article></div>';
            cnf_form_section_close();
        } else {
            cnf_form_section_open('Questionário do atendimento');
            echo '<p class="st-pos-intro cnf-form-hint">Responda cada pergunta abaixo com base no que foi tratado com o solicitante.</p>';
            echo '<div class="st-pos-questions">';
            for ($z = 0; $z < count($campoConfig); $z++) {
                $cfg = $campoConfig[$z];
                $cid = (int)$cfg['id_campo'];
                $opts = $optionsByCampo[$cid] ?? [];
                echo '<article class="st-pos-question">';
                echo '<div class="st-pos-question__head">';
                echo '<span class="st-pos-question__badge">Pergunta ' . ($z + 1) . '</span>';
                echo '<span class="st-pos-question__title">' . htmlspecialchars($cfg['desc_campo']) . '</span>';
                if ((int)$cfg['obg'] === 1) {
                    echo '<span class="st-pos-question__required">Obrigatória</span>';
                }
                echo '</div>';
                echo '<div class="st-pos-question__answers">';
                if ($cfg['tipo_input'] === 'checkbox') {
                    $opt1 = ['desc_option' => '', 'id_option' => '', 'value_option' => ''];
                    $opt2 = ['desc_option' => '', 'id_option' => '', 'value_option' => ''];
                    foreach ($opts as $o) {
                        if ($o['referencia'] === 'opcao_chk_1') {
                            $opt1 = $o;
                        }
                        if ($o['referencia'] === 'opcao_chk_2') {
                            $opt2 = $o;
                        }
                    }
                    inputCheckbox(
                        $cfg['desc_campo'],
                        $cfg['nome_campo'],
                        $opt1['desc_option'],
                        $opt1['id_option'],
                        $opt1['value_option'],
                        $opt2['desc_option'],
                        $opt2['id_option'],
                        $opt2['value_option'],
                        $cfg['obg'],
                        $chatId
                    );
                } elseif ($cfg['tipo_input'] === 'select') {
                    $options = [];
                    foreach ($opts as $o) {
                        if ($o['ativo'] == 1 && ($o['referencia'] === 'select' || $o['referencia'] === '' || $o['referencia'] === null)) {
                            $options[] = [
                                'desc_option' => $o['desc_option'],
                                'value_option' => $o['value_option']
                            ];
                        }
                    }
                    inputSelect($cfg['desc_campo'], $cfg['nome_campo'], $options, $cfg['obg'], $chatId);
                } else {
                    inputText($cfg['desc_campo'], $cfg['nome_campo'], $cfg['tipo_input'], $cfg['obg'], $chatId);
                }
                echo '</div></article>';
            }
            echo '</div>';
            cnf_form_section_close();
        }

        cnf_form_section_open('Confirmação do chamado');
        echo '<p class="st-pos-intro cnf-form-hint">Confirme o assunto registrado para este protocolo.</p>';
        cnf_field_select(
            'confirma_assunto_' . $chatId,
            'Assunto da chamada',
            $assuntoOpts,
            ['required' => true]
        );
        cnf_form_section_close();

        cnf_form_section_open('Encerramento');
        echo '<p class="st-pos-intro cnf-form-hint">Defina a situação final da demanda e, se necessário, registre pausa ou pendência.</p>';
        ?>
        <div class="st-pos-closure-grid" id="st_pos_closure_<?= $chatId ?>">
            <div class="st-pos-closure-card" id="div_pausa_<?= $chatId ?>" style="display: none;">
                <?php
                cnf_field_select(
                    'pausa_bko_' . $chatId,
                    'Pausa após encerramento',
                    '<option value="">Selecione...</option>'
                    . '<option value="1">Sim, realizar uma pausa</option>'
                    . '<option value="0">Não, continuar atendimento</option>'
                );
                ?>
                <div id="feed_pausa_<?= $chatId ?>"></div>
            </div>
            <div class="st-pos-closure-card st-pos-closure-card--sit">
                <?php
                cnf_field_select(
                    'situacao_dem_' . $chatId,
                    'Situação da demanda',
                    '<option value="4">Finalizar</option>'
                    . '<option value="7">Ausência de Comunicação</option>'
                    . '<option value="3">Pendência</option>'
                );
                ?>
            </div>
        </div>
        <div class="cnf-field-full" id="div_sit_<?= $chatId ?>" style="display: none;">
            <?php cnf_field_input('motivo_situacao_' . $chatId, 'Motivo', ['extra' => 'autocomplete="off"']); ?>
        </div>
        <?php
        cnf_form_section_close();
        ?>
    </form>

    <footer class="st-pos-footer cnf-modal-footer cnf-modal-footer--inline">
        <div id="save_feed_<?= $chatId ?>" class="cnf-feed"></div>
        <button type="button" id="btn_save_pos_<?= $chatId ?>" class="btn btn-solvetask">
            <i class="fas fa-save"></i> Gravar pós-atendimento
        </button>
    </footer>
</div>

<script>
(function() {
    var chatId = <?= $chatId ?>;

    function stPosSyncClosureLayout() {
        var $grid = $('#st_pos_closure_' + chatId);
        var $pausa = $('#div_pausa_' + chatId);
        if ($pausa.length && $pausa.is(':visible')) {
            $grid.addClass('st-pos-closure-grid--dual');
        } else {
            $grid.removeClass('st-pos-closure-grid--dual');
        }
    }

    var spans = $('.tab');
    if (spans.length === 1) {
        $('#div_pausa_' + chatId).show();
    }
    stPosSyncClosureLayout();

    $('#pausa_bko_' + chatId).change(function() {
        var feed = '#feed_pausa_' + chatId;
        var pausa = $('#pausa_bko_' + chatId).val();
        var fila = '<?= (int)$fil['fila_id'] ?>';
        if (pausa === '1') {
            $.post('staff/load_fila_pos.php', { fila: fila, chatId: chatId }, function(valor) {
                $(feed).html(valor);
            });
        } else {
            $(feed).html('');
        }
    });

    $('#situacao_dem_' + chatId).change(function() {
        var sit = $('#situacao_dem_' + chatId).val();
        var $motivo = $('#motivo_situacao_' + chatId);
        var $label = $('label[for="motivo_situacao_' + chatId + '"]');
        if (sit === '3' || sit === '7') {
            $label.text(sit === '3' ? 'Motivo da pendência' : 'Motivo da ausência de comunicação');
            $('#div_sit_' + chatId).show();
            $motivo.prop('required', true);
            $('#body-pos_' + chatId).animate({ scrollTop: 100000 }, 'slow');
        } else {
            $('#div_sit_' + chatId).hide();
            $motivo.prop('required', false).val('');
        }
    });

    $('#btn_save_pos_' + chatId).click(function() {
        var feed = '#save_feed_' + chatId;
        var indice = '<?= $indice ?>';
        var fila_id = '<?= (int)$fil['fila_id'] ?>';
        var assunto = $('input[name=assunto_' + chatId + ']:checked').val();
        var pausa = $('#pausa_bko_' + chatId).val();
        var situacao_dem = $('#situacao_dem_' + chatId).val();
        var confirma_assunto = $('#confirma_assunto_' + chatId).val();
        var motivo_situacao = $('#motivo_situacao_' + chatId).val();
        var tokenChat = '<?= htmlspecialchars($fil['token_chat'], ENT_QUOTES, 'UTF-8') ?>';
        var $formRoot = $('#body-pos_' + chatId);

        if ($formRoot.find('input[name=assunto_' + chatId + ']').length && !assunto) {
            alert('Selecione o serviço realizado neste atendimento.');
            return;
        }
        if (!confirma_assunto) {
            alert('Selecione o assunto da chamada.');
            return;
        }
        if ((situacao_dem === '3' || situacao_dem === '7') && !String(motivo_situacao || '').trim()) {
            alert('Informe o motivo da situação selecionada.');
            return;
        }
        if ($('#div_pausa_' + chatId).is(':visible') && pausa === '') {
            alert('Informe se deseja realizar uma pausa após o encerramento.');
            return;
        }
        if ($formRoot[0] && typeof $formRoot[0].checkValidity === 'function' && !$formRoot[0].checkValidity()) {
            if (typeof $formRoot[0].reportValidity === 'function') {
                $formRoot[0].reportValidity();
            }
            return;
        }
        <?php
        if (count($campoScript) > 0) {
            for ($num = 0; $num < count($campoScript); $num++) {
                $nomeCampoJs = preg_replace('/[^a-zA-Z0-9_]/', '_', $campoScript[$num]['nome_campo']);
                if ($campoScript[$num]['tipo_input'] !== 'checkbox') {
                    echo 'var v_' . $nomeCampoJs . ' = $("#' . $campoScript[$num]['nome_campo'] . '_' . $chatId . '").val();' . "\n";
                } else {
                    echo 'var v_' . $nomeCampoJs . ' = $("input:radio[name=' . $campoScript[$num]['nome_campo'] . '_' . $chatId . ']:checked").val();' . "\n";
                }
            }
        }
        ?>

        $(feed).html('<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden"></span></div>');

        var payload = {
            assunto: assunto,
            tokenChat: tokenChat,
            fila_id: fila_id,
            pausa: pausa,
            situacao_dem: situacao_dem,
            confirma_assunto: confirma_assunto,
            motivo_situacao: motivo_situacao,
            indice: indice
        };

        <?php
        if (count($campoScript) > 0) {
            for ($num = 0; $num < count($campoScript); $num++) {
                $nomeCampoJs = preg_replace('/[^a-zA-Z0-9_]/', '_', $campoScript[$num]['nome_campo']);
                echo 'payload["' . $campoScript[$num]['nome_campo'] . '"] = v_' . $nomeCampoJs . ';' . "\n";
            }
        }
        ?>

        $.post('staff/save_pos.php', payload, function(valor) {
            $(feed).html(valor);
            if (typeof window.stBkoInjectScripts === 'function') {
                window.stBkoInjectScripts($(feed));
            }
        });
    });
})();
</script>
