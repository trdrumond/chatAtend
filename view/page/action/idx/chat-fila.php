<?php
require_once __DIR__ . '/../../../cnf/session.php';

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

$tmpDash=10000;

$idAte = (int) $infoUser['id_user'];

$stmt = $PDO->prepare("SELECT id_fila_chat, protocolo, data_hora, fila_id, bko_resp, fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=fila_id) as nome_fila, assunto_id, (SELECT titulo_assunto from tbl_assunto where id_assunto=assunto_id) as nome_assunto from tbl_chat_fila where status_fila=1 and ate_resp=?");
$stmt->execute([$idAte]);
$infoFila = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($infoFila)) {
    $infoFila = [];
}

$filaIdChat = (int) ($infoFila['fila_id'] ?? 0);
$assuntoIdChat = (int) ($infoFila['assunto_id'] ?? 0);

$sql="SELECT id_faq, titulo_faq, txt from tbl_faq where fila_id=? and (assunto_id=0 or assunto_id=?)";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$filaIdChat, $assuntoIdChat]);
$infoFaq = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($infoFaq);

$sql="SELECT titulo_assunto, procedimento, date_format(data_alt, '%d/%m/%Y %H:%i:%s') as data_alt, date_format(data_alt, '%Y-%m-%d') as data_ver from tbl_assunto where id_assunto=?";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$assuntoIdChat]);
$infoAssunto = $stmt->fetch( PDO::FETCH_ASSOC );

if($infoAssunto['procedimento']=='' && count($infoFaq)>0){
    $activeTabFaq = ' active';
    $activeDivFaq = ' show active';
} else {
    $activeTabFaq = '';
    $activeDivFaq = '';
}

$hasSidePanel = ($infoAssunto['procedimento'] != '') || (count($infoFaq) > 0);
$dashboardClass = 'st-fila-sol-workspace' . ($hasSidePanel ? '' : ' st-fila-sol-workspace--solo');
$dataEntradaFila = !empty($infoFila['data_hora']) ? date('c', strtotime($infoFila['data_hora'])) : '';

?>

<div id="dashboard" class="<?= htmlspecialchars($dashboardClass) ?>">
    <div id="div_ope" class="st-fila-sol-main">
        <div id="bloco_central" class="st-fila-sol-card">
            <div class="st-fila-sol-card__hero">
                <span class="st-fila-sol-status" aria-live="polite">
                    <span class="st-fila-sol-status__pulse" aria-hidden="true"></span>
                    <span class="st-fila-sol-status__text">Na fila de espera</span>
                </span>
            </div>

            <dl class="st-fila-sol-meta">
                <div class="st-fila-sol-meta__item">
                    <dt>Protocolo</dt>
                    <dd><?= htmlspecialchars($infoFila['protocolo']) ?></dd>
                </div>
                <div class="st-fila-sol-meta__item">
                    <dt>Fila</dt>
                    <dd><?= htmlspecialchars($infoFila['nome_fila']) ?></dd>
                </div>
                <div class="st-fila-sol-meta__item st-fila-sol-meta__item--full">
                    <dt>Assunto</dt>
                    <dd><?= htmlspecialchars($infoFila['nome_assunto']) ?></dd>
                </div>
            </dl>

            <div class="st-fila-sol-queue" id="stFilaQueue">
                <ol class="st-fila-sol-steps" aria-label="Progresso do atendimento">
                    <li class="st-fila-sol-steps__item is-active" data-step="wait">
                        <span class="st-fila-sol-steps__dot" aria-hidden="true"></span>
                        <span class="st-fila-sol-steps__label">Na fila</span>
                    </li>
                    <li class="st-fila-sol-steps__item" data-step="next">
                        <span class="st-fila-sol-steps__dot" aria-hidden="true"></span>
                        <span class="st-fila-sol-steps__label">Próximo</span>
                    </li>
                    <li class="st-fila-sol-steps__item" data-step="connect">
                        <span class="st-fila-sol-steps__dot" aria-hidden="true"></span>
                        <span class="st-fila-sol-steps__label">Conectando</span>
                    </li>
                </ol>

                <div class="st-fila-sol-position">
                    <span class="st-fila-sol-position__label">Sua posição na fila</span>
                    <div class="st-fila-sol-position__ring" id="posit_ring">
                        <div class="st-fila-sol-position__value" id="posit">--</div>
                    </div>
                    <p class="st-fila-sol-position__hint" id="posit_hint">Consultando sua posição...</p>
                    <input type="hidden" value="1" id="iptPosit">

                    <div class="st-fila-sol-queue-stats">
                        <div class="st-fila-sol-queue-stat">
                            <span class="st-fila-sol-queue-stat__val" id="fila_a_frente">—</span>
                            <span class="st-fila-sol-queue-stat__lbl">À frente</span>
                        </div>
                        <div class="st-fila-sol-queue-stat">
                            <span class="st-fila-sol-queue-stat__val" id="fila_total">—</span>
                            <span class="st-fila-sol-queue-stat__lbl">Na fila</span>
                        </div>
                        <div class="st-fila-sol-queue-stat">
                            <span class="st-fila-sol-queue-stat__val" id="tempo_espera">00:00</span>
                            <span class="st-fila-sol-queue-stat__lbl">Tempo de espera</span>
                        </div>
                    </div>

                    <div id="load_gif" class="st-fila-sol-loading st-fila-sol-loading--connect">
                        <div class="st-chat-open__spinner" aria-hidden="true"></div>
                        <span id="st_fila_load_label">Atualizando posição...</span>
                    </div>
                </div>
            </div>

            <div class="st-fila-sol-actions">
                <button type="button" id="cancel_fila" class="btn btn-outline-danger st-fila-sol-cancel">
                    <i class="fas fa-times-circle" aria-hidden="true"></i>
                    Cancelar chamada
                </button>
            </div>
        </div>
    </div>

    <?php if ($hasSidePanel) { ?>
    <div id="div_info" class="st-fila-sol-side">
        <header class="st-fila-sol-side__head">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            <span>Informações enquanto aguarda</span>
        </header>
        <ul class="nav nav-tabs st-fila-sol-tabs" id="tabChat" role="tablist">
            <?php if($infoAssunto['procedimento']!=''){ ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="proc-tab" data-bs-toggle="tab" data-bs-target="#proc" type="button"
                    role="tab" aria-controls="proc" aria-selected="true"><i class="fas fa-bars"></i>
                    Procedimento</button>
            </li>
            <?php } ?>
            <?php if(count($infoFaq)>0){ ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?=$activeTabFaq?>" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq"
                    type="button" role="tab" aria-controls="faq" aria-selected="true"><i
                        class="fas fa-question-circle"></i> FAQ</button>
            </li>
            <?php } ?>

        </ul>

        <div class="tab-content" id="myTab">
            <?php if($infoAssunto['procedimento']!=''){ ?>
            <div class="tab-pane fade show active st-fila-sol-pane" id="proc" role="tabpanel" aria-labelledby="proc-tab">
                <div class="st-fila-sol-proc-head">
                    <h4><i class="fas fa-book-open" aria-hidden="true"></i> <?= htmlspecialchars($infoAssunto['titulo_assunto']) ?></h4>
                    <?php
                    $data_ver = date('Y-m-d', strtotime('+5 days', strtotime($infoAssunto['data_ver'])));
                    $badge = (date('Y-m-d') > $data_ver) ? 'secondary' : 'danger';
                    ?>
                    <p class="st-fila-sol-proc-meta">
                        <strong>Procedimento</strong>
                        <span class="badge bg-<?= $badge ?>">Última atualização: <?= htmlspecialchars($infoAssunto['data_alt']) ?></span>
                    </p>
                </div>
                <div id="proced" class="st-fila-sol-proc-body"><?= $infoAssunto['procedimento'] ?></div>
            </div>
            <?php } ?>

            <?php if(count($infoFaq)>0){ ?>
            <div class="tab-pane fade <?= $activeDivFaq ?> st-fila-sol-pane" id="faq" role="tabpanel" aria-labelledby="faq-tab">
                <div class="accordion accordion-flush st-fila-sol-faq" id="faq_accordion">
                    <?php for($x=0;$x<count($infoFaq);$x++){ ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne-<?=$infoFaq[$x]['id_faq']?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-<?=$infoFaq[$x]['id_faq']?>" aria-expanded="false"
                                aria-controls="flush-<?=$infoFaq[$x]['id_faq']?>">
                                <?=$infoFaq[$x]['titulo_faq']?>
                            </button>
                        </h2>
                        <div id="flush-<?=$infoFaq[$x]['id_faq']?>" class="accordion-collapse collapse"
                            aria-labelledby="flush-headingOne-<?=$infoFaq[$x]['id_faq']?>"
                            data-bs-parent="#faq_accordion">
                            <div class="accordion-body"><?=$infoFaq[$x]['txt']?></div>
                        </div>
                    </div>
                    <?php } ?>
                </div>

            </div>
            <?php } ?>

        </div>
    </div>
    <?php } ?>
</div>

<script type="text/javascript">
var timeLoadQtd = null;
var loadAtendIniciado = false;
var loadAtendTimer = null;
var sendBkoTimer = null;
var ultimaPosicao = null;
var redirecionandoAtendimento = false;
var cancelamentoManualEmAndamento = false;
var abandonoFilaRegistrado = false;
var stFilaDashChaRedirected = false;
var stFilaEntrada = <?= json_encode($dataEntradaFila) ?>;
var stFilaEsperaTimer = null;
window.stFilaSolIdFila = <?= (int)$infoFila['id_fila_chat'] ?>;
window.stFilaSolFilaId = <?= (int)$infoFila['fila_id'] ?>;
window.loadChatIn = '';
var ST_FILA_ATEND_POLL_MS = 2000;
var ST_FILA_QTD_POLL_MS = 1500;

function stFilaSolStopAllPolling() {
    if (loadAtendTimer) {
        clearTimeout(loadAtendTimer);
        loadAtendTimer = null;
    }
    if (timeLoadQtd) {
        clearTimeout(timeLoadQtd);
        timeLoadQtd = null;
    }
    stFilaSolPararNotificarBko();
}

function stFilaSolGoDashChaOnce() {
    if (stFilaDashChaRedirected) {
        return;
    }
    if ($('#action-page .st-chat-open').length || (window.stChatOpen && stChatOpen.isOpeningAte && stChatOpen.isOpeningAte())) {
        return;
    }
    if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) {
        return;
    }
    stFilaDashChaRedirected = true;
    stFilaSolStopAllPolling();
    redirecionandoAtendimento = false;
    window.stFilaSolChatAteLaunched = false;
    if (window.stChatOpen && typeof stChatOpen.resetOpeningAte === 'function') {
        stChatOpen.resetOpeningAte();
    }
    if (typeof window.actionPageNav === 'function') {
        window.actionPageNav('dash-cha', 'idx');
    } else {
        actionPage('dash-cha', 'idx');
    }
}

function stFilaSolIsOnFilaPage() {
    return $('#action-page .st-fila-sol-workspace').length > 0;
}

function stFilaSolResetOpenFlags() {
    window.redirecionandoAtendimento = false;
    window.stChatSolOpeningAte = false;
    window.stFilaSolChatAteLaunched = false;
    if (window.stChatOpen && typeof window.stChatOpen.resetOpeningAte === 'function') {
        window.stChatOpen.resetOpeningAte();
    }
}
window.stFilaSolResetOpenFlags = stFilaSolResetOpenFlags;
window.stFilaSolStopAllPolling = stFilaSolStopAllPolling;
window.stFilaSolGoDashChaOnce = stFilaSolGoDashChaOnce;

function stFilaSolCanPoll() {
    if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
        return false;
    }
    if ($('#action-page .st-chat-open').length) {
        return false;
    }
    if (window.stFilaSolChatAteLaunched) {
        return false;
    }
    if (stFilaSolIsOnFilaPage()) {
        if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
            return false;
        }
        return true;
    }
    if (redirecionandoAtendimento) {
        return false;
    }
    if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
        return false;
    }
    return true;
}

function stFilaSolOpenChatAte() {
    if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
        return;
    }
    if (typeof window.stChatSolIsEnteringChat === 'function' && window.stChatSolIsEnteringChat()) {
        return;
    }
    if (typeof window.stChatResetForNewAttendimento === 'function') {
        window.stChatResetForNewAttendimento();
    }
    stFilaSolStopAllPolling();
    window.stFilaSolChatAteLaunched = true;
    redirecionandoAtendimento = true;
    if (window.stChatOpen && typeof stChatOpen.openChatAteFast === 'function') {
        stChatOpen.openChatAteFast(false);
        return;
    }
    actionPage('chat-ate', 'idx');
}
window.stFilaSolOpenChatAte = stFilaSolOpenChatAte;

var stFilaSolPollAtendDebounce = null;
window.stFilaSolPollAtendimentoAgora = function() {
    if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
        return;
    }
    if (!stFilaSolIsOnFilaPage()) {
        return;
    }
    if (window.stFilaSolChatAteLaunched) {
        return;
    }
    if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
        return;
    }
    if (stFilaSolPollAtendDebounce) {
        return;
    }
    stFilaSolPollAtendDebounce = setTimeout(function() {
        stFilaSolPollAtendDebounce = null;
    }, 400);
    if (loadAtendTimer) {
        clearTimeout(loadAtendTimer);
        loadAtendTimer = null;
    }
    if (!loadAtendIniciado) {
        loadAtendIniciado = true;
        stFilaSolSetNextState();
        stFilaSolPararNotificarBko();
    }
    loadAtend(window.stFilaSolIdFila);
};

function stFilaSolNotificarBko() {
    if (typeof sendBko !== 'function') {
        return;
    }
    if (!stFilaSolIsOnFilaPage()) {
        stFilaSolPararNotificarBko();
        return;
    }
    if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) {
        stFilaSolPararNotificarBko();
        return;
    }
    if (window.stFilaSolChatAteLaunched || redirecionandoAtendimento) {
        stFilaSolPararNotificarBko();
        return;
    }
    sendBko();
    if (sendBkoTimer) {
        clearTimeout(sendBkoTimer);
    }
    sendBkoTimer = setTimeout(stFilaSolNotificarBko, 3000);
}

function stFilaSolPararNotificarBko() {
    if (sendBkoTimer) {
        clearTimeout(sendBkoTimer);
        sendBkoTimer = null;
    }
}

function stFilaSolSetStatus(texto) {
    $('.st-fila-sol-status__text').text(texto);
}

function stFilaSolSetStep(step) {
    $('.st-fila-sol-steps__item').removeClass('is-active is-done');
    var $items = $('.st-fila-sol-steps__item');
    var reached = false;
    $items.each(function() {
        var $item = $(this);
        if ($item.data('step') === step) {
            $item.addClass('is-active');
            reached = true;
        } else if (!reached) {
            $item.addClass('is-done');
        }
    });
}

function stFilaSolFormatTempo(totalSeg) {
    if (totalSeg < 0) totalSeg = 0;
    var h = Math.floor(totalSeg / 3600);
    var m = Math.floor((totalSeg % 3600) / 60);
    var s = totalSeg % 60;
    if (h > 0) {
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }
    return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
}

function stFilaSolTickEspera() {
    if (!stFilaEntrada) return;
    var inicio = new Date(stFilaEntrada);
    if (isNaN(inicio.getTime())) return;
    var diff = Math.floor((Date.now() - inicio.getTime()) / 1000);
    $('#tempo_espera').text(stFilaSolFormatTempo(diff));
}

function stFilaSolHint(posicao, aFrente) {
    if (posicao === 1 || aFrente === 0) {
        return 'Você é o próximo da fila. Aguarde o atendente.';
    }
    if (aFrente === 1) {
        return 'Falta apenas 1 pessoa para chegar a sua vez.';
    }
    return 'Há ' + aFrente + ' pessoas à sua frente. Aguarde sua vez.';
}

function stFilaSolUpdatePosition(posicao, aFrente, totalFila) {
    var $posit = $('#posit');
    var $ring = $('#posit_ring');
    var mudou = ultimaPosicao !== null && ultimaPosicao !== posicao;

    posicao = parseInt(posicao, 10) || 0;
    if (typeof aFrente === 'undefined' || aFrente === null || aFrente === '') {
        aFrente = Math.max(0, posicao - 1);
    } else {
        aFrente = parseInt(aFrente, 10);
        if (isNaN(aFrente)) {
            aFrente = Math.max(0, posicao - 1);
        }
    }
    if (typeof totalFila === 'undefined' || totalFila === null) {
        totalFila = posicao;
    } else {
        totalFila = parseInt(totalFila, 10) || posicao;
    }

    var isProximo = posicao === 1 || aFrente === 0;

    $posit.removeClass('st-fila-sol-position__value--next').text(posicao + 'º');
    $ring.removeClass('st-fila-sol-position__ring--next st-fila-sol-position__ring--pulse');
    $('#posit_hint').text(stFilaSolHint(posicao, aFrente));
    $('#fila_a_frente').text(aFrente);
    $('#fila_total').text(totalFila);

    if (loadAtendIniciado) {
        stFilaSolSetStep('connect');
        $posit.addClass('st-fila-sol-position__value--next');
        $ring.addClass('st-fila-sol-position__ring--next');
        stFilaSolSetStatus('Conectando você ao atendente');
    } else if (isProximo) {
        stFilaSolSetStep('next');
        $posit.addClass('st-fila-sol-position__value--next');
        $ring.addClass('st-fila-sol-position__ring--next');
        stFilaSolSetStatus('Você é o próximo — aguarde o atendente');
        if (!loadAtendIniciado && typeof stFilaSolNotificarBko === 'function') {
            stFilaSolNotificarBko();
        }
    } else {
        stFilaSolSetStep('wait');
        stFilaSolSetStatus('Na fila de espera');
    }

    if (mudou) {
        $ring.addClass('st-fila-sol-position__ring--pulse');
        setTimeout(function() {
            $ring.removeClass('st-fila-sol-position__ring--pulse');
        }, 700);
    }

    ultimaPosicao = posicao;
}

function stFilaSolSetNextState() {
    var $posit = $('#posit');
    var $ring = $('#posit_ring');
    $posit.addClass('st-fila-sol-position__value--next').text('1º');
    $ring.addClass('st-fila-sol-position__ring--next');
    $('#posit_hint').text('O atendente está chamando você para o chat...');
    $('#fila_a_frente').text('0');
    stFilaSolSetStep('connect');
    stFilaSolSetStatus('Aguardando atendimento');
}

stFilaSolTickEspera();
stFilaEsperaTimer = setInterval(stFilaSolTickEspera, 1000);

(function stFilaSolPollLoop() {
    if (!stFilaSolIsOnFilaPage()) {
        setTimeout(stFilaSolPollLoop, 2000);
        return;
    }
    if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
        setTimeout(stFilaSolPollLoop, 2000);
        return;
    }
    if (window.stFilaSolChatAteLaunched || redirecionandoAtendimento) {
        setTimeout(stFilaSolPollLoop, 800);
        return;
    }
    if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
        setTimeout(stFilaSolPollLoop, 800);
        return;
    }
    loadAtend(window.stFilaSolIdFila);
    if (!loadAtendIniciado) {
        loadQtd(window.stFilaSolIdFila, window.stFilaSolFilaId);
    }
    setTimeout(stFilaSolPollLoop, loadAtendIniciado ? 1200 : 2000);
})();

setTimeout(function() {
    //console.log("SendBko");
    sendBko();
}, 0);

loadQtd(<?php echo $infoFila['id_fila_chat']; ?>, <?php echo $infoFila['fila_id']; ?>);
//setTimeout(function(){
//    loadQtd(<?php echo $infoFila['id_fila_chat']; ?>, <?php echo $infoFila['fila_id']; ?>);
//}, 1000);

function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min) + min);
}


function load() {
    if (loadAtendIniciado) {
        return;
    }

    var posicao = parseInt($("#iptPosit").val(), 10) || 1;
    var temp;

    if (posicao <= 1) {
        temp = loadAtendIniciado ? ST_FILA_QTD_POLL_MS : 80;
    } else if (posicao < 5) {
        temp = 500;
    } else if (posicao < 10) {
        temp = posicao * 300;
    } else if (posicao < 20) {
        temp = getRandomInt(60, 120) * 100;
    } else {
        temp = getRandomInt(120, 280) * 100;
    }

    if (timeLoadQtd === null) {
        timeLoadQtd = setTimeout(function() {
            timeLoadQtd = null;
            loadQtd(<?php echo $infoFila['id_fila_chat']; ?>, <?php echo $infoFila['fila_id']; ?>);
        }, temp);
    }
}


function loadQtd(idFila, fila) {
    if (!stFilaSolCanPoll()) {
        return;
    }

    $.ajax({
        url: "staff/load_chat_ate.php",
        type: "POST",
        dataType: "json",
        data: {
            idFila,
            fila
        }
    }).done(function(retorno) {
            if (!retorno || typeof retorno !== 'object') {
                load();
                return;
            }

            if (retorno.redirect === 'chat-ate') {
                stFilaSolOpenChatAte();
                return;
            }

            if (retorno.redirect === 'chat-fila') {
                if ($('#action-page .st-chat-open').length || (window.stChatOpen && stChatOpen.isOpeningAte && stChatOpen.isOpeningAte())) {
                    return;
                }
                if (stFilaSolIsOnFilaPage()) {
                    return;
                }
                redirecionandoAtendimento = true;
                actionPage('chat-fila', 'idx');
                return;
            }

            if (retorno.redirect === 'dash-cha') {
                stFilaSolGoDashChaOnce();
                return;
            }

            if (typeof retorno.posicao !== 'undefined') {
                stFilaSolUpdatePosition(
                    retorno.posicao,
                    retorno.aFrente,
                    retorno.totalFila
                );
                $("#iptPosit").val(retorno.posicao);
                $('#load_gif').hide();
            }

            if (retorno.chamarAtendimento && !loadAtendIniciado) {
                loadAtendIniciado = true;
                stFilaSolSetNextState();
                stFilaSolNotificarBko();
                $('#load_gif').show();
                $('#st_fila_load_label').text('Conectando com o atendente...');
                loadAtend(idFila);
                return;
            }

            load();
        }).fail(function() {
            if (ultimaPosicao === null) {
                $("#posit").text('--');
            }
            load();
        });
}

function loadAtend(idFila) {
    if (!stFilaSolCanPoll()) {
        return;
    }

    if (loadAtendTimer) {
        clearTimeout(loadAtendTimer);
        loadAtendTimer = null;
    }

    $.ajax({
        url: "staff/load_chat_ate_fila.php",
        type: "POST",
        dataType: "json",
        data: {
            idFila
        }
    }).done(function(retorno) {
            if (!retorno || typeof retorno !== 'object') {
                loadAtendTimer = setTimeout(function() {
                    loadAtend(idFila);
                }, ST_FILA_ATEND_POLL_MS);
                return;
            }

            if (retorno.redirect === 'chat-ate') {
                stFilaSolOpenChatAte();
                return;
            }

            if (retorno.redirect === 'chat-fila') {
                if ($('#action-page .st-chat-open').length || (window.stChatOpen && stChatOpen.isOpeningAte && stChatOpen.isOpeningAte())) {
                    return;
                }
                if (stFilaSolIsOnFilaPage()) {
                    return;
                }
                stFilaSolPararNotificarBko();
                redirecionandoAtendimento = true;
                actionPage('chat-fila', 'idx');
                return;
            }

            if (retorno.redirect === 'dash-cha') {
                stFilaSolGoDashChaOnce();
                return;
            }

            if (loadAtendIniciado && window.stFilaSolFilaId && !redirecionandoAtendimento) {
                loadQtd(idFila, window.stFilaSolFilaId);
            }

            if (redirecionandoAtendimento && !stFilaSolIsOnFilaPage()) {
                return;
            }

            if (window.stChatOpen && stChatOpen.isOpeningAte && stChatOpen.isOpeningAte()) {
                return;
            }

            loadAtendTimer = setTimeout(function() {
                loadAtend(idFila);
            }, ST_FILA_ATEND_POLL_MS);
        }).fail(function() {
            loadAtendTimer = setTimeout(function() {
                loadAtend(idFila);
            }, 120);
        });
}

function actionPage(action, sec) {
    if (action === 'chat-ate' && typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
        return;
    }
    if (action === 'chat-fila' && stFilaSolIsOnFilaPage()) {
        return;
    }
    if (action === 'dash-cha' && $('#dashboard.st-dash-cha-workspace').length) {
        return;
    }
    if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) {
        return;
    }
    if (action === 'chat-ate' && window.stChatOpen && typeof stChatOpen.openChatAteFast === 'function') {
        window.stFilaSolChatAteLaunched = true;
        redirecionandoAtendimento = true;
        stChatOpen.openChatAteFast(false);
        return;
    }
    var title = 'Carregando';
    var sub = 'Aguarde um instante...';
    if (action === 'chat-ate') {
        title = 'Abrindo chat';
        sub = 'Conectando você ao atendente...';
    } else if (action === 'chat-fila') {
        title = 'Atualizando fila';
        sub = 'Verificando sua posição...';
    }
    if (window.stChatOpen) {
        $("#action-page").html(stChatOpen.loaderHtml(title, sub));
    } else {
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="120"></div>');
    }
    $.post("action.php", {
            action: action,
            sec: sec
        },
        function(valor) {
            if (typeof valor === 'string') {
                valor = valor.replace(/ƒ/g, 'f');
            }
            if (typeof stInjectActionPageHtml === 'function') {
                stInjectActionPageHtml(valor);
            } else {
                $("#action-page").html(valor);
            }
        });
}

function cancel(idFila, fila) {
    $('#posit').html('--');
    var motivo_cancela = $('#motivo_cancela').val();
    $.post("staff/load_cancel_fila.php", {
            idFila,
            fila,
            motivo_cancela
        },
        function(valor) {
            $("#posit").html(valor);
            //console.log(valor);
        });
}

function registraAbandonoFila(idFila, fila) {
    if (cancelamentoManualEmAndamento || redirecionandoAtendimento || abandonoFilaRegistrado) {
        return;
    }

    abandonoFilaRegistrado = true;

    var motivo_cancela = 'Abandono de fila';

    var dados = new FormData();
    dados.append('idFila', idFila);
    dados.append('fila', fila);
    dados.append('motivo_cancela', motivo_cancela);
    dados.append('auto_abandono', '1');
    if (window.ST_CSRF) {
        dados.append('st_csrf', window.ST_CSRF);
    }

    if (navigator.sendBeacon) {
        navigator.sendBeacon('staff/load_cancel_fila.php', dados);
        return;
    }

    if (window.fetch) {
        fetch('staff/load_cancel_fila.php', {
            method: 'POST',
            body: dados,
            keepalive: true
        });
        return;
    }

    $.post('staff/load_cancel_fila.php', {
        idFila: idFila,
        fila: fila,
        motivo_cancela: motivo_cancela,
        auto_abandono: 1
    });
}

$('#cancel_fila').click(function() {
    $('#fundo_mod_cancela').fadeIn();
});

$('#motivo_cancela').keyup(function() {
    var val = $(this).val();
    if (val != '' && val.length >= 5) {
        $('#real_cancel').attr('disabled', false);
    } else {
        $('#real_cancel').attr('disabled', true);
    }
});


$('#real_cancel').click(function() {
    cancelamentoManualEmAndamento = true;
    cancel(<?php echo $infoFila['id_fila_chat']; ?>, <?php echo $infoFila['fila_id']; ?>);
    sendBko();
});

function tratarSaidaFila() {
    stFilaSolPararNotificarBko();
    if (timeLoadQtd) {
        clearTimeout(timeLoadQtd);
        timeLoadQtd = null;
    }
    if (loadAtendTimer) {
        clearTimeout(loadAtendTimer);
        loadAtendTimer = null;
    }
    if (stFilaEsperaTimer) {
        clearInterval(stFilaEsperaTimer);
        stFilaEsperaTimer = null;
    }
    var idFila = '<?php echo $infoFila['id_fila_chat']; ?>';
    var fila = '<?php echo $infoFila['fila_id']; ?>';
    registraAbandonoFila(idFila, fila);
    sendBko();
}

window.addEventListener('pagehide', tratarSaidaFila);
window.addEventListener('beforeunload', tratarSaidaFila);
</script>
<div id="fundo_mod_cancela" class="gw-modal-fundo">
    <div id="mod_cancela" class="gw-modal-small">
        <h5>Cancelamento de Protocolo</h5>
        <div class="st-form cnf-form mt-2">
            <div class="st-field input-container">
                <label class="st-label" for="motivo_cancela">Motivo do cancelamento <span class="st-required">*</span></label>
                <input id="motivo_cancela" class="input" type="text" pattern=".+" required />
            </div>
        </div>
        <button id="real_cancel" class="btn btn-danger mt-2" disabled>Realizar cancelamento</button>
        <div class="close"><span>&times;</span></div>
    </div>
</div>


<script>
$("#fundo_mod_cancela, .close").click(function() {
    $("#fundo_mod_cancela").hide();
});

$("#mod_cancela").click(function(e) {
    e.stopPropagation();
});
</script>
