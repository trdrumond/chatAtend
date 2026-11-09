<?php
include("../cnf/session.php");
if (function_exists('session_write_close')) {
    session_write_close();
}
require_once __DIR__ . '/../page/action/cnf/_cnf_ui.php';

$chatId = (int) ($_POST['chatId'] ?? 0);
$indice = isset($_POST['indice']) ? htmlspecialchars((string) $_POST['indice'], ENT_QUOTES, 'UTF-8') : '';

$stmtChat = $PDO->prepare(
    'SELECT ci.fila_id, ci.token_chat, ci.assunto_id, ci.fila_chat_id, ci.rem_chat, ci.dest_chat,
            cf.nome_fila, cf.assuntos_id AS assuntos,
            tcf.protocolo, tcf.te, tcf.ta, tcf.motivo, tcf.hora_fim,
            DATE_FORMAT(tcf.hora_fim, \'%d/%m/%Y %H:%i\') AS hora_fim_fmt,
            UNIX_TIMESTAMP(tcf.hora_fim) AS hora_fim_ts,
            TIMESTAMPDIFF(SECOND, tcf.hora_fim, NOW()) AS hora_fim_elapsed,
            COALESCE(NULLIF(tcf.assunto_id, 0), NULLIF(ci.assunto_id, \'\')) AS assunto_ctx_id,
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

$posCtx = null;
$sessUserId = (int) ($_SESSION['dados']['id_user'] ?? 0);
if ($sessUserId > 0 && (string) ($fil['rem_chat'] ?? '') === (string) $sessUserId) {
    $posCtx = [
        'solicitante' => '',
        'assunto' => '',
        'te' => (string) ($fil['te'] ?? ''),
        'ta' => (string) ($fil['ta'] ?? ''),
        'hora_fim_fmt' => (string) ($fil['hora_fim_fmt'] ?? ''),
        'hora_fim_ts' => (int) ($fil['hora_fim_ts'] ?? 0),
        'elapsed' => max(0, (int) ($fil['hora_fim_elapsed'] ?? 0)),
        'motivo' => trim((string) ($fil['motivo'] ?? '')),
        'msgs' => [],
    ];

    $destId = (int) ($fil['dest_chat'] ?? 0);
    if ($destId > 0) {
        $stmtSol = $PDO->prepare('SELECT nome, sobrenome FROM tbl_user WHERE id_user = ? LIMIT 1');
        $stmtSol->execute([$destId]);
        $sol = $stmtSol->fetch(PDO::FETCH_ASSOC);
        if ($sol) {
            $posCtx['solicitante'] = trim((string) $sol['nome'] . ' ' . (string) $sol['sobrenome']);
        }
    }

    $assuntoCtxId = (int) ($fil['assunto_ctx_id'] ?? 0);
    if ($assuntoCtxId > 0) {
        $stmtAssCtx = $PDO->prepare('SELECT titulo_assunto FROM tbl_assunto WHERE id_assunto = ? LIMIT 1');
        $stmtAssCtx->execute([$assuntoCtxId]);
        $posCtx['assunto'] = (string) ($stmtAssCtx->fetchColumn() ?: '');
    }

    $stmtMsg = $PDO->prepare(
        'SELECT m.id_msg, m.rem_id, m.msg, DATE_FORMAT(m.data_hora, \'%d/%m %H:%i\') AS hora_msg,'
        . ' u.nome, u.sobrenome'
        . ' FROM tbl_chat_msg m'
        . ' LEFT JOIN tbl_user u ON u.id_user = m.rem_id'
        . ' WHERE m.chat_id = ?'
        . ' ORDER BY m.data_hora DESC, m.id_msg DESC'
        . ' LIMIT 8'
    );
    $stmtMsg->execute([$chatId]);
    $bkoRemId = (int) $fil['rem_chat'];
    foreach (array_reverse($stmtMsg->fetchAll(PDO::FETCH_ASSOC)) as $m) {
        $texto = html_entity_decode(strip_tags((string) $m['msg']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = trim(preg_replace('/\s+/u', ' ', $texto));
        if (function_exists('mb_strlen') && mb_strlen($texto, 'UTF-8') > 280) {
            $texto = rtrim(mb_substr($texto, 0, 280, 'UTF-8')) . '…';
        } elseif (!function_exists('mb_strlen') && strlen($texto) > 280) {
            $texto = rtrim(substr($texto, 0, 280)) . '…';
        }
        $remId = (int) $m['rem_id'];
        if ($remId === 0) {
            $tipo = 'sys';
            $autor = 'Sistema';
        } else {
            $tipo = $remId === $bkoRemId ? 'bko' : 'sol';
            $autor = trim((string) $m['nome'] . ' ' . (string) $m['sobrenome']);
            if ($autor === '') {
                $autor = $tipo === 'bko' ? 'Atendente' : 'Solicitante';
            }
        }
        $posCtx['msgs'][] = ['tipo' => $tipo, 'autor' => $autor, 'hora' => (string) $m['hora_msg'], 'texto' => $texto];
    }
}

$posElapsedFmt = '';
if ($posCtx !== null && $posCtx['hora_fim_ts'] > 0) {
    $e = $posCtx['elapsed'];
    $posElapsedFmt = sprintf('%02d:%02d:%02d', intdiv($e, 3600), intdiv($e % 3600, 60), $e % 60);
}

$posTimerTs = (int) ($fil['hora_fim_ts'] ?? 0);
$posTimerSec = max(0, (int) ($fil['hora_fim_elapsed'] ?? 0));
$posTimerFmt = $posTimerSec >= 3600
    ? sprintf('%d:%02d:%02d', intdiv($posTimerSec, 3600), intdiv($posTimerSec % 3600, 60), $posTimerSec % 60)
    : sprintf('%02d:%02d', intdiv($posTimerSec, 60), $posTimerSec % 60);
?>
<div class="st-pos-page cnf-page" id="st_pos_page_<?= $chatId ?>" data-chat-id="<?= $chatId ?>">
    <header class="cnf-header st-pos-header">
        <div class="st-pos-header__main">
            <h5 class="cnf-title"><i class="fas fa-clipboard-check" aria-hidden="true"></i> Pós-Atendimento</h5>
            <?php if ($nomeFila !== '') { ?>
            <p class="cnf-sub"><?= htmlspecialchars($nomeFila) ?></p>
            <?php } ?>
        </div>
        <div class="st-pos-header__chips">
            <?php if (!empty($fil['protocolo'])) { ?>
            <span class="st-pos-chip st-pos-protocol"><i class="fas fa-hashtag" aria-hidden="true"></i> Protocolo <?= htmlspecialchars($fil['protocolo']) ?></span>
            <?php } ?>
            <?php if ($posTimerTs > 0) { ?>
            <span class="st-pos-chip st-pos-timer" id="st_pos_timer_<?= $chatId ?>"
                  data-hora-fim-ts="<?= $posTimerTs ?>" data-elapsed-sec="<?= $posTimerSec ?>">
                <i class="fas fa-stopwatch" aria-hidden="true"></i> Tempo em pós: <span class="st-pos-timer__val"><?= htmlspecialchars($posTimerFmt) ?></span>
            </span>
            <?php } ?>
            <span class="st-pos-chip st-pos-chip--pend" id="st_pos_pend_<?= $chatId ?>" role="status" hidden>
                <i class="fas fa-layer-group" aria-hidden="true"></i> <span class="st-pos-chip__text"></span>
            </span>
        </div>
    </header>

    <form class="st-pos-body cnf-form st-form" id="body-pos_<?= $chatId ?>" method="post" action="#" novalidate>
    <div class="st-pos-main">
    <?php if ($posCtx !== null) {
        $h = static function ($v) {
            return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        };
        $metaItens = [
            ['solicitante', 'Solicitante', $posCtx['solicitante']],
            ['assunto', 'Assunto', $posCtx['assunto']],
            ['te', 'TE', $posCtx['te']],
            ['ta', 'TA', $posCtx['ta']],
            ['hora-fim', 'Encerrado em', $posCtx['hora_fim_fmt']],
        ];
    ?>
    <section class="st-pos-context" id="st_pos_context_<?= $chatId ?>"
             data-chat-id="<?= $chatId ?>"
             data-hora-fim-ts="<?= (int) $posCtx['hora_fim_ts'] ?>"
             data-elapsed-sec="<?= (int) $posCtx['elapsed'] ?>">
        <dl class="st-pos-meta">
            <?php foreach ($metaItens as $item) { ?>
            <div class="st-pos-meta__item st-pos-meta__item--<?= $item[0] ?>">
                <dt><?= $h($item[1]) ?></dt>
                <dd><?= $item[2] !== '' ? $h($item[2]) : '—' ?></dd>
            </div>
            <?php } ?>
            <div class="st-pos-meta__item st-pos-meta__item--elapsed">
                <dt>Tempo desde o encerramento</dt>
                <dd class="st-pos-elapsed" data-hora-fim-ts="<?= (int) $posCtx['hora_fim_ts'] ?>"><?= $posElapsedFmt !== '' ? $h($posElapsedFmt) : '—' ?></dd>
            </div>
        </dl>
        <?php if ($posCtx['motivo'] !== '') { ?>
        <div class="st-pos-motivo">
            <strong class="st-pos-motivo__label">Motivo do chamado</strong>
            <p class="st-pos-motivo__text"><?= $h($posCtx['motivo']) ?></p>
        </div>
        <?php } ?>
        <?php if ($posCtx['msgs'] !== []) { ?>
        <details class="st-pos-transcript">
            <summary>
                <span class="st-pos-transcript__title"><i class="fas fa-comments" aria-hidden="true"></i> Resumo da conversa</span>
                <span class="st-pos-transcript__count"><?= count($posCtx['msgs']) ?> <?= count($posCtx['msgs']) === 1 ? 'mensagem' : 'últimas mensagens' ?></span>
            </summary>
            <ol class="st-pos-transcript__list">
                <?php foreach ($posCtx['msgs'] as $msg) { ?>
                <li class="st-pos-msg st-pos-msg--<?= $msg['tipo'] ?>">
                    <span class="st-pos-msg__author"><?= $h($msg['autor']) ?></span>
                    <time class="st-pos-msg__time"><?= $h($msg['hora']) ?></time>
                    <p class="st-pos-msg__text"><?= $h($msg['texto']) ?></p>
                </li>
                <?php } ?>
            </ol>
        </details>
        <?php } ?>
    </section>
    <?php } ?>

        <?php if ($fila['qtd'] < 1) {
            $ex = array_filter(array_map('intval', explode(',', (string) ($fil['assuntos'] ?? ''))));
            cnf_form_section_open('Serviço realizado');
            echo '<p class="st-pos-intro cnf-form-hint">Selecione o serviço que melhor descreve o que foi feito neste atendimento.</p>';
            echo '<div class="st-pos-questions">';
            echo '<article class="st-pos-question st-pos-question--single" data-required="1">';
            echo '<div class="st-pos-question__head">';
            echo '<span class="st-pos-question__badge">Pergunta</span>';
            echo '<span class="st-pos-question__title">Qual serviço foi realizado?</span>';
            echo '<span class="st-pos-question__required">Obrigatória</span>';
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
                echo '<article class="st-pos-question"' . ((int)$cfg['obg'] === 1 ? ' data-required="1"' : '') . '>';
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
        ?>
    </div>
    <aside class="st-pos-aside" aria-label="Fechamento do pós-atendimento">
        <?php
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
                <div class="st-pos-seg st-pos-seg--3" id="st_pos_seg_sit_<?= $chatId ?>" role="radiogroup" aria-label="Situação da demanda">
                    <button type="button" class="st-pos-seg__btn" role="radio" aria-checked="false" data-value="4">
                        <i class="fas fa-check-circle" aria-hidden="true"></i><span>Finalizar</span>
                    </button>
                    <button type="button" class="st-pos-seg__btn" role="radio" aria-checked="false" data-value="7">
                        <i class="fas fa-comment-slash" aria-hidden="true"></i><span>Ausência de comunicação</span>
                    </button>
                    <button type="button" class="st-pos-seg__btn" role="radio" aria-checked="false" data-value="3">
                        <i class="fas fa-hourglass-half" aria-hidden="true"></i><span>Pendência</span>
                    </button>
                </div>
            </div>
            <div class="cnf-field-full st-pos-motivo-field" id="div_sit_<?= $chatId ?>" style="display: none;">
                <div class="st-field input-container">
                    <label class="st-label" for="motivo_situacao_<?= $chatId ?>">Motivo</label>
                    <textarea id="motivo_situacao_<?= $chatId ?>" class="input" rows="3" autocomplete="off"
                              aria-describedby="st_pos_motivo_count_<?= $chatId ?>"></textarea>
                    <p class="st-pos-counter" id="st_pos_motivo_count_<?= $chatId ?>"><span class="st-pos-counter__val">0</span> caracteres</p>
                </div>
            </div>
            <div class="st-pos-closure-card st-pos-closure-card--pausa" id="div_pausa_<?= $chatId ?>" style="display: none;">
                <?php
                cnf_field_select(
                    'pausa_bko_' . $chatId,
                    'Pausa após encerramento',
                    '<option value="">Selecione...</option>'
                    . '<option value="1">Sim, realizar uma pausa</option>'
                    . '<option value="0">Não, continuar atendimento</option>'
                );
                ?>
                <div class="st-pos-seg st-pos-seg--2" id="st_pos_seg_pausa_<?= $chatId ?>" role="radiogroup" aria-label="Pausa após encerramento">
                    <button type="button" class="st-pos-seg__btn" role="radio" aria-checked="false" data-value="1">
                        <i class="fas fa-mug-hot" aria-hidden="true"></i><span>Sim, pausar</span>
                    </button>
                    <button type="button" class="st-pos-seg__btn" role="radio" aria-checked="false" data-value="0">
                        <i class="fas fa-play" aria-hidden="true"></i><span>Não, continuar</span>
                    </button>
                </div>
                <div id="feed_pausa_<?= $chatId ?>"></div>
            </div>
        </div>
        <?php
        cnf_form_section_close();
        ?>
        <div class="st-pos-actions" id="st_pos_actions_<?= $chatId ?>">
            <div class="st-pos-progress" id="st_pos_progress_<?= $chatId ?>">
                <div class="st-pos-progress__track" role="progressbar" aria-label="Campos obrigatórios preenchidos"
                     aria-valuemin="0" aria-valuemax="0" aria-valuenow="0">
                    <span class="st-pos-progress__fill"></span>
                </div>
                <p class="st-pos-progress__text" aria-live="polite">
                    <strong class="st-pos-progress__done">0</strong> de <span class="st-pos-progress__total">0</span> obrigatórios preenchidos
                </p>
                <p class="st-pos-draft-note" id="st_pos_draft_<?= $chatId ?>" hidden>
                    <i class="fas fa-history" aria-hidden="true"></i> Rascunho local restaurado
                </p>
            </div>
            <div id="save_feed_<?= $chatId ?>" class="cnf-feed st-pos-feed" aria-live="polite"></div>
            <button type="button" id="btn_save_pos_<?= $chatId ?>" class="btn btn-solvetask st-pos-save">
                <span class="st-pos-save__icon" aria-hidden="true"><i class="fas fa-save"></i></span>
                <span class="st-pos-save__label">Gravar pós-atendimento</span>
            </button>
        </div>
    </aside>
    </form>
</div>

<script>
(function() {
    var chatId = <?= $chatId ?>;
    var $page = $('#st_pos_page_' + chatId);
    if (!$page.length || $page.data('stPosInit')) {
        return;
    }
    $page.data('stPosInit', true);

    var ns = '.stPos' + chatId;
    var $form = $('#body-pos_' + chatId);
    var $btn = $('#btn_save_pos_' + chatId);
    var $sit = $('#situacao_dem_' + chatId);
    var $pausa = $('#pausa_bko_' + chatId);
    var $pausaBox = $('#div_pausa_' + chatId);
    var $motivo = $('#motivo_situacao_' + chatId);
    var $confirma = $('#confirma_assunto_' + chatId);
    var $progress = $('#st_pos_progress_' + chatId);
    var draftKey = 'stPosDraft:' + chatId;
    var draftTtlMs = 7 * 24 * 3600 * 1000;
    var draftTimer = null;
    var restoring = false;
    var saving = false;
    var errSeq = 0;
    var lastProgress = '';
    var syncSit = null;
    var syncPausa = null;

    function pageAlive() {
        return document.body.contains($page[0]);
    }

    function isMotivoSit(v) {
        return v === '3' || v === '7';
    }

    function pad2(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function fmtClock(s, long) {
        var h = Math.floor(s / 3600);
        var m = Math.floor((s % 3600) / 60);
        var r = s % 60;
        if (long) {
            return pad2(h) + ':' + pad2(m) + ':' + pad2(r);
        }
        return h > 0 ? h + ':' + pad2(m) + ':' + pad2(r) : pad2(m) + ':' + pad2(r);
    }

    if ($('.tab').length === 1) {
        $pausaBox.show();
    }

    $form.on('submit' + ns, function(e) {
        e.preventDefault();
    });

    $pausa.on('change' + ns, function() {
        var feed = '#feed_pausa_' + chatId;
        var pausa = $pausa.val();
        var fila = '<?= (int)$fil['fila_id'] ?>';
        if (pausa === '1') {
            $.post('staff/load_fila_pos.php', { fila: fila, chatId: chatId }, function(valor) {
                $(feed).html(valor);
            });
        } else {
            $(feed).html('');
        }
    });

    function updateCounter() {
        $('#st_pos_motivo_count_' + chatId + ' .st-pos-counter__val').text(String(($motivo.val() || '').length));
    }

    $sit.on('change' + ns, function() {
        var sit = $sit.val();
        var $label = $('label[for="motivo_situacao_' + chatId + '"]');
        if (isMotivoSit(sit)) {
            $label.text(sit === '3' ? 'Motivo da pendência' : 'Motivo da ausência de comunicação');
            $('#div_sit_' + chatId).show();
            $motivo.prop('required', true);
            if (!restoring && $motivo[0] && $motivo[0].scrollIntoView) {
                $motivo[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        } else {
            $('#div_sit_' + chatId).hide();
            $motivo.prop('required', false).val('');
        }
        updateCounter();
    });

    $motivo.on('input' + ns, updateCounter);

    /* Grupo segmentado (role=radio) espelhando um <select> que continua sendo a fonte do valor. */
    function bindSeg($group, $select) {
        var $btns = $group.find('[role="radio"]');
        if (!$btns.length || !$select.length) {
            return null;
        }
        $select.addClass('st-pos-native').attr({ 'aria-hidden': 'true', tabindex: '-1' });
        $('label[for="' + $select.attr('id') + '"]').on('click' + ns, function(e) {
            e.preventDefault();
            var $on = $btns.filter('[aria-checked="true"]');
            ($on.length ? $on : $btns.first()).trigger('focus');
        });

        function sync() {
            var v = String($select.val() == null ? '' : $select.val());
            var found = false;
            $btns.each(function() {
                var on = $(this).attr('data-value') === v;
                found = found || on;
                $(this).attr({ 'aria-checked': on ? 'true' : 'false', tabindex: on ? '0' : '-1' })
                    .toggleClass('is-active', on);
            });
            if (!found) {
                $btns.first().attr('tabindex', '0');
            }
        }

        $btns.on('click' + ns, function() {
            var v = $(this).attr('data-value');
            if (String($select.val()) !== v) {
                $select.val(v).trigger('change');
            } else {
                sync();
            }
        });
        $btns.on('keydown' + ns, function(e) {
            var k = e.key;
            var dir = (k === 'ArrowRight' || k === 'ArrowDown') ? 1 : ((k === 'ArrowLeft' || k === 'ArrowUp') ? -1 : 0);
            if (!dir) {
                return;
            }
            e.preventDefault();
            var i = $btns.index(this);
            $btns.eq((i + dir + $btns.length) % $btns.length).trigger('focus').trigger('click');
        });
        $select.on('change' + ns, sync);
        sync();
        return sync;
    }

    syncSit = bindSeg($('#st_pos_seg_sit_' + chatId), $sit);
    syncPausa = bindSeg($('#st_pos_seg_pausa_' + chatId), $pausa);

    function boxFilled($box) {
        var ok = false;
        $box.find('input, select, textarea').each(function() {
            if (this.type === 'radio' || this.type === 'checkbox') {
                if (this.checked) {
                    ok = true;
                }
            } else if ($.trim(String($(this).val() || '')) !== '') {
                ok = true;
            }
        });
        return ok;
    }

    var $aside = $page.find('.st-pos-aside');
    var $scroller = $page.closest('[id^="div-"]');

    function syncAsideFit() {
        if (!$aside.length || !$scroller.length) {
            return;
        }
        $aside.toggleClass('is-tall', $aside[0].offsetHeight > $scroller[0].clientHeight - 12);
    }

    function refreshState() {
        var total = 0;
        var done = 0;
        $form.find('.st-pos-question').each(function() {
            var $q = $(this);
            var filled = boxFilled($q);
            $q.toggleClass('is-answered', filled);
            if ($q.attr('data-required') === '1') {
                total++;
                if (filled) {
                    done++;
                }
            }
        });
        if ($confirma.length) {
            total++;
            if ($confirma.val()) {
                done++;
            }
        }
        if (isMotivoSit(String($sit.val()))) {
            total++;
            if ($.trim(String($motivo.val() || '')) !== '') {
                done++;
            }
        }
        if ($pausaBox.is(':visible')) {
            total++;
            if ($pausa.val() !== '' && $pausa.val() != null) {
                done++;
            }
        }
        syncAsideFit();
        var key = done + '/' + total;
        if (key === lastProgress) {
            return;
        }
        lastProgress = key;
        $progress.find('.st-pos-progress__done').text(String(done));
        $progress.find('.st-pos-progress__total').text(String(total));
        $progress.find('.st-pos-progress__track').attr({ 'aria-valuemax': total, 'aria-valuenow': done });
        $progress.find('.st-pos-progress__fill').css('width', (total ? Math.round(done / total * 100) : 100) + '%');
        $progress.toggleClass('is-complete', total > 0 && done === total);
    }

    /* Validação inline */
    function clearError($box) {
        $box.removeClass('is-invalid').children('.st-pos-error').remove();
        $box.find('[aria-invalid]').each(function() {
            var $el = $(this);
            var prev = $el.attr('data-st-describedby');
            $el.removeAttr('aria-invalid data-st-describedby');
            if (prev) {
                $el.attr('aria-describedby', prev);
            } else {
                $el.removeAttr('aria-describedby');
            }
        });
    }

    function addError(list, $box, $focus, msg) {
        if (!$box.length) {
            $box = $focus.closest('.st-field');
        }
        if (!$box.length) {
            return;
        }
        for (var i = 0; i < list.length; i++) {
            if (list[i].box === $box[0]) {
                return;
            }
        }
        list.push({ box: $box[0], focus: $focus[0] || $box[0], msg: msg });
    }

    function renderErrors(list) {
        $form.find('.is-invalid').each(function() {
            clearError($(this));
        });
        list.sort(function(a, b) {
            return (a.box.compareDocumentPosition(b.box) & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1;
        });
        $.each(list, function(idx, it) {
            var id = 'st_pos_err_' + chatId + '_' + (++errSeq);
            var $msg = $('<p class="st-pos-error"></p>').attr('id', id);
            if (idx === 0) {
                $msg.attr('role', 'alert');
            }
            $msg.append($('<i class="fas fa-exclamation-circle" aria-hidden="true"></i>'), $('<span></span>').text(it.msg));
            $(it.box).addClass('is-invalid').append($msg);
            var $f = $(it.focus);
            var prev = $f.attr('aria-describedby') || '';
            $f.attr({ 'aria-invalid': 'true', 'data-st-describedby': prev, 'aria-describedby': (prev ? prev + ' ' : '') + id });
        });
        syncAsideFit();
        if (list.length) {
            var first = list[0];
            if (first.box.scrollIntoView) {
                first.box.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
            try {
                first.focus.focus({ preventScroll: true });
            } catch (e) {
                first.focus.focus();
            }
        }
    }

    function validate() {
        var errors = [];
        var $assRadios = $form.find('input[name=assunto_' + chatId + ']');
        if ($assRadios.length && !$assRadios.filter(':checked').length) {
            addError(errors, $assRadios.first().closest('.st-pos-question'), $assRadios.first(), 'Selecione o serviço realizado neste atendimento.');
        }
        if (!$confirma.val()) {
            addError(errors, $confirma.closest('.st-field'), $confirma, 'Selecione o assunto da chamada.');
        }
        if (isMotivoSit(String($sit.val())) && !String($motivo.val() || '').trim()) {
            addError(errors, $motivo.closest('.st-field'), $motivo, 'Informe o motivo da situação selecionada.');
        }
        if ($pausaBox.is(':visible') && $pausa.val() === '') {
            addError(errors, $pausaBox, $('#st_pos_seg_pausa_' + chatId + ' [role="radio"]').first(), 'Informe se deseja realizar uma pausa após o encerramento.');
        }
        var formEl = $form[0];
        if (formEl && typeof formEl.checkValidity === 'function' && !formEl.checkValidity()) {
            $.each(formEl.elements, function(_, el) {
                if (!el.willValidate || el.checkValidity()) {
                    return;
                }
                var $el = $(el);
                var $q = $el.closest('.st-pos-question');
                var msg = el.validationMessage || 'Preencha este campo.';
                if (el.validity && el.validity.valueMissing) {
                    msg = (el.tagName === 'SELECT' || el.type === 'radio' || el.type === 'checkbox') ? 'Selecione uma opção.' : 'Preencha este campo.';
                }
                addError(errors, $q.length ? $q : $el.closest('.st-field'), $el, msg);
            });
        }
        renderErrors(errors);
        return errors.length === 0;
    }

    /* Rascunho local (somente respostas do formulário; pausa fica de fora) */
    function lsGet() {
        try {
            return window.localStorage ? window.localStorage.getItem(draftKey) : null;
        } catch (e) {
            return null;
        }
    }

    function lsSet(v) {
        try {
            window.localStorage.setItem(draftKey, v);
        } catch (e) { /* quota/privado */ }
    }

    function lsDel() {
        try {
            window.localStorage.removeItem(draftKey);
        } catch (e) { /* quota/privado */ }
    }

    function collectDraft() {
        var data = {};
        $form.find('input, select, textarea').each(function() {
            var el = this;
            if (el === $pausa[0] || /^(password|file|hidden|submit|button)$/.test(el.type)) {
                return;
            }
            if (el.type === 'radio') {
                if (el.checked && el.name) {
                    data['r:' + el.name] = el.value;
                }
                return;
            }
            if (el.type === 'checkbox') {
                if (el.id) {
                    data['c:' + el.id] = el.checked ? 1 : 0;
                }
                return;
            }
            if (el.id) {
                data['f:' + el.id] = $(el).val();
            }
        });
        return data;
    }

    function scheduleDraft() {
        if (restoring) {
            return;
        }
        clearTimeout(draftTimer);
        draftTimer = setTimeout(function() {
            if (pageAlive()) {
                lsSet(JSON.stringify({ t: Date.now(), d: collectDraft() }));
            }
        }, 250);
    }

    function restoreDraft() {
        var raw = lsGet();
        if (!raw) {
            return false;
        }
        var obj = null;
        try {
            obj = JSON.parse(raw);
        } catch (e) {
            obj = null;
        }
        if (!obj || !obj.d || typeof obj.d !== 'object' || (obj.t && Date.now() - obj.t > draftTtlMs)) {
            lsDel();
            return false;
        }
        var d = obj.d;
        var applied = false;
        var sitId = $sit.attr('id');

        function setVal($el, v) {
            if (!$el.length || v == null) {
                return;
            }
            v = String(v);
            if ($el.is('select') && !$el.find('option').filter(function() { return this.value === v; }).length) {
                return;
            }
            if (String($el.val()) !== v) {
                $el.val(v);
                applied = true;
            }
        }

        restoring = true;
        if (d['f:' + sitId] != null) {
            setVal($sit, d['f:' + sitId]);
            $sit.trigger('change');
        }
        $.each(d, function(k, v) {
            var kind = k.slice(0, 2);
            var key = k.slice(2);
            var el;
            if (kind === 'f:' && key !== sitId) {
                el = document.getElementById(key);
                if (el && $.contains($form[0], el)) {
                    setVal($(el), v);
                }
            } else if (kind === 'r:') {
                $form.find('input[type=radio]').filter(function() {
                    return this.name === key && this.value === String(v);
                }).each(function() {
                    if (!this.checked) {
                        this.checked = true;
                        applied = true;
                    }
                });
            } else if (kind === 'c:') {
                el = document.getElementById(key);
                if (el && $.contains($form[0], el) && el.checked !== !!v) {
                    el.checked = !!v;
                    applied = true;
                }
            }
        });
        restoring = false;
        updateCounter();
        if (syncSit) {
            syncSit();
        }
        return applied;
    }

    $form.on('input' + ns + ' change' + ns, 'input, select, textarea', function() {
        var $inv = $(this).closest('.is-invalid');
        if ($inv.length) {
            clearError($inv);
        }
        refreshState();
        scheduleDraft();
    });

    /* Pendentes além do aberto */
    function renderPend(list) {
        var n = 0;
        $.each($.isArray(list) ? list : [], function(_, p) {
            var id = parseInt(p && p.chatId, 10);
            if (id > 0 && id !== chatId) {
                n++;
            }
        });
        var $b = $('#st_pos_pend_' + chatId);
        $b.find('.st-pos-chip__text').text('+' + n + (n === 1 ? ' pós pendente' : ' pós pendentes'));
        $b.prop('hidden', n < 1);
    }

    /* Contador "Tempo em pós" + sincronização de valores alterados sem change (ex.: Swal da pausa) */
    var $timer = $('#st_pos_timer_' + chatId);
    var $timerVal = $timer.find('.st-pos-timer__val');
    var $elapsedDd = $page.find('.st-pos-elapsed').filter(function() {
        return parseInt($(this).attr('data-hora-fim-ts'), 10) > 0;
    });
    var baseSec = parseInt($timer.attr('data-elapsed-sec'), 10);
    var t0 = Date.now();
    window.stPosTimers = window.stPosTimers || {};
    if (window.stPosTimers[chatId]) {
        clearInterval(window.stPosTimers[chatId]);
    }

    function teardown() {
        clearInterval(window.stPosTimers[chatId]);
        delete window.stPosTimers[chatId];
        clearTimeout(draftTimer);
        $(document).off('stBkoPosPendentes' + ns);
    }

    function tick() {
        if (!pageAlive()) {
            teardown();
            return;
        }
        if ($timer.length && !isNaN(baseSec)) {
            var s = baseSec + Math.floor((Date.now() - t0) / 1000);
            $timerVal.text(fmtClock(s, false));
            $elapsedDd.text(fmtClock(s, true));
        }
        if (syncPausa) {
            syncPausa();
        }
        if (syncSit) {
            syncSit();
        }
        refreshState();
    }

    $(document).off('stBkoPosPendentes' + ns).on('stBkoPosPendentes' + ns, function(e, lista) {
        if (!pageAlive()) {
            teardown();
            return;
        }
        renderPend(lista);
    });

    $(document).off('stPosSaved' + ns).on('stPosSaved' + ns, function(e, savedId) {
        if (parseInt(savedId, 10) !== chatId) {
            return;
        }
        $(document).off('stPosSaved' + ns);
        teardown();
        lsDel();
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Pós-atendimento gravado',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        }
    });

    $('#st_pos_draft_' + chatId).prop('hidden', !restoreDraft());
    updateCounter();
    refreshState();
    renderPend(window.stBkoPosPendentes);
    window.stPosTimers[chatId] = setInterval(tick, 1000);

    function setSaving(on) {
        saving = on;
        $btn.prop('disabled', on).attr('aria-busy', on ? 'true' : 'false').toggleClass('is-loading', on);
        $btn.find('.st-pos-save__icon').html(on
            ? '<span class="spinner-border spinner-border-sm"></span>'
            : '<i class="fas fa-save"></i>');
        $btn.find('.st-pos-save__label').text(on ? 'Gravando...' : 'Gravar pós-atendimento');
    }

    $btn.on('click' + ns, function() {
        if (saving) {
            return;
        }
        var feed = '#save_feed_' + chatId;
        var indice = '<?= $indice ?>';
        var fila_id = '<?= (int)$fil['fila_id'] ?>';
        var assunto = $('input[name=assunto_' + chatId + ']:checked').val();
        var pausa = $('#pausa_bko_' + chatId).val();
        var situacao_dem = $('#situacao_dem_' + chatId).val();
        var confirma_assunto = $('#confirma_assunto_' + chatId).val();
        var motivo_situacao = $('#motivo_situacao_' + chatId).val();
        var tokenChat = '<?= htmlspecialchars($fil['token_chat'], ENT_QUOTES, 'UTF-8') ?>';

        if (!validate()) {
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

        $(feed).empty();
        setSaving(true);

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
            var $feed = $(feed);
            var html = String(valor == null ? '' : valor);
            if (!$feed.length) {
                return;
            }
            // O script de sucesso de save_pos.php chama clearInterval(time_{chatId}); sem a global ele lança ReferenceError.
            if (typeof window['time_' + chatId] === 'undefined') {
                window['time_' + chatId] = 0;
            }
            // innerHTML não executa scripts: a resposta roda uma única vez via stBkoInjectScripts.
            if (typeof window.stBkoInjectScripts === 'function') {
                $feed[0].innerHTML = html;
                window.stBkoInjectScripts($feed);
            } else {
                $feed.html(html);
            }
            if ($feed.find('.text-danger').length || html.indexOf('<script') === -1) {
                setSaving(false);
            }
        }).fail(function() {
            $(feed).html('<div class="text-danger">Não foi possível gravar o pós-atendimento. Verifique a conexão e tente novamente.</div>');
            setSaving(false);
        });
    });
})();
</script>
