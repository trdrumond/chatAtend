
//console.log(window.location.hostname);
//WebSocket

var hostname = window.location.hostname;

if (hostname == 'localhost') {
    var prot = 'ws';
    var port = ':8085';
} else {
    var prot = 'wss';
    var port = '/enelce/';
    //var prot = 'ws';
    //var port = ':8081';}
}
//var hostname = 'localhost';

var host = prot + '://' + hostname + port;
var host = 'wss://solvetask-mt.logos-ma.com.br/celpe';
console.log(host);
var conn = new WebSocket(host);

function stEscapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function stFormatChatPlainText(str) {
    return stEscapeHtml(str).replace(/\r?\n/g, '<br>');
}

function stSafeChatHtml(html) {
    var tmp = document.createElement('div');
    tmp.innerHTML = String(html || '');
    tmp.querySelectorAll('script,iframe,object,embed,link,meta,style').forEach(function (n) {
        n.remove();
    });
    tmp.querySelectorAll('*').forEach(function (el) {
        Array.from(el.attributes).forEach(function (attr) {
            var name = String(attr.name || '');
            var val = String(attr.value || '');
            if (/^on/i.test(name) || ((name === 'href' || name === 'src') && /^\s*javascript:/i.test(val))) {
                el.removeAttribute(name);
            }
        });
    });
    return tmp.innerHTML;
}

window.stEscapeHtml = stEscapeHtml;
window.stFormatChatPlainText = stFormatChatPlainText;
window.stSafeChatHtml = stSafeChatHtml;

function stChatSolFilaActive() {
    return $('#action-page .st-fila-sol-workspace').length > 0;
}

function stChatSolWorkspaceActive() {
    return $('#action-page .st-chat-workspace--sol').length > 0;
}

function stChatResolveIndiceForChat(chatId) {
    chatId = String(chatId || '');
    if (typeof window.stBkoGetIndiceByChatId === 'function' && $('#content-bko').length) {
        var fromRegistry = window.stBkoGetIndiceByChatId(chatId);
        if (fromRegistry) {
            return fromRegistry;
        }
    }
    var $inp = $('#indice_' + chatId);
    if ($inp.length) {
        var val = parseInt($inp.val(), 10);
        if (val) {
            return val;
        }
    }
    return 0;
}

window.stChatEndedMap = window.stChatEndedMap || {};
window.stChatEndedTokens = window.stChatEndedTokens || {};
window.stChatPosOpened = window.stChatPosOpened || {};

function stChatResetForNewAttendimento() {
    window.stChatSolEnded = false;
    window.stChatSolClassModalOpen = false;
    window.stChatPosOpened = {};
}
window.stChatResetForNewAttendimento = stChatResetForNewAttendimento;

function stChatSolIsEnteringChat() {
    return !!(window.stChatSolEnterLock || $('#action-page .st-chat-open').length);
}
window.stChatSolIsEnteringChat = stChatSolIsEnteringChat;

function stChatSolSetEnterLock(active) {
    window.stChatSolEnterLock = !!active;
}
window.stChatSolSetEnterLock = stChatSolSetEnterLock;

function stChatMarkEnded(chatId, tokenChat) {
    chatId = String(chatId || '');
    tokenChat = String(tokenChat || '');
    if (chatId) {
        window.stChatEndedMap[chatId] = true;
        try { sessionStorage.setItem('stChatEnded:' + chatId, '1'); } catch (e) { }
    }
    if (tokenChat) {
        window.stChatEndedTokens[tokenChat] = true;
        try { sessionStorage.setItem('stChatEndedToken:' + tokenChat, '1'); } catch (e) { }
    }
    window.stChatSolEnded = true;
}

function stChatIsEnded(chatId, tokenChat) {
    chatId = String(chatId || '');
    tokenChat = String(tokenChat || '');
    if (chatId && window.stChatEndedMap[chatId]) {
        return true;
    }
    if (tokenChat && window.stChatEndedTokens[tokenChat]) {
        return true;
    }
    try {
        if (chatId && sessionStorage.getItem('stChatEnded:' + chatId) === '1') {
            return true;
        }
        if (tokenChat && sessionStorage.getItem('stChatEndedToken:' + tokenChat) === '1') {
            return true;
        }
    } catch (e) { }
    return false;
}

function stChatConfirmFinalize(chatId, destinatario, contrato, token, mensagem, indice, isBko) {
    if (stChatIsEnded(chatId, token)) {
        return;
    }
    var title = 'Finalizar atendimento?';
    var text = isBko
        ? 'O atendimento será encerrado para ambas as partes. Você seguirá para o pós-atendimento e o solicitante poderá classificar o atendimento.'
        : 'O atendimento será encerrado. Em seguida você poderá classificar o atendimento.';
    var doFinalize = function () {
        stChatMarkEnded(chatId, token);
        chatFim(chatId, destinatario, contrato, token, mensagem, indice);
    };
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, finalizar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) {
                doFinalize();
            }
        });
        return;
    }
    if (window.confirm(title + '\n\n' + text)) {
        doFinalize();
    }
}
window.stChatMarkEnded = stChatMarkEnded;
window.stChatIsEnded = stChatIsEnded;
window.stChatConfirmFinalize = stChatConfirmFinalize;

function stChatGetAdminContentEl(chatId) {
    chatId = String(chatId || '');
    var id0 = 'chat-content_0_' + chatId;
    if (document.getElementById(id0)) {
        return { area: document.getElementById(id0), div: '#chat-content_0_' + chatId, txt: id0 };
    }
    return null;
}

/** Exibe mensagem de chat em um único painel (padrão ou admin), evitando duplicata. */
function stChatShowMsg(chatId, how, data, destinatario, remetente) {
    if (stChatGetAdminContentEl(chatId)) {
        if ($('#action-page .dash-fila-workspace').length && window.stDashAcomp && typeof window.stDashAcomp.pollNow === 'function') {
            window.stDashAcomp.pollNow();
            return;
        }
        showMessagesAdmin(chatId, how, data, destinatario, remetente);
        return;
    }
    if ($('#chat-content_' + chatId).length) {
        var destStd = (how === 'other' && remetente != null && remetente !== '') ? remetente : destinatario;
        showMessages(chatId, how, data, destStd);
    }
}

/** Exibe mensagem de sistema em um único painel (padrão ou admin), evitando duplicata. */
function stChatShowSysMsg(chatId, how, data, destinatario, indice) {
    if (stChatGetAdminContentEl(chatId)) {
        if ($('#action-page .dash-fila-workspace').length && window.stDashAcomp && typeof window.stDashAcomp.pollNow === 'function') {
            window.stDashAcomp.pollNow();
            return;
        }
        showMessagesSysAdmin(chatId, how, data, indice);
        return;
    }
    if ($('#chat-content_' + chatId).length) {
        showMessagesSys(chatId, how, data, destinatario, indice);
    }
}

function newMessage(chatId) {
    chatId = String(chatId || '');
    if ($('#content-bko').length && typeof newMessageAba === 'function') {
        var indiceTab = stChatResolveIndiceForChat(chatId);
        if (indiceTab) {
            newMessageAba(indiceTab);
        }
    }
}
window.newMessage = newMessage;

function stChatSolFilaPageActive() {
    return $('#action-page .st-fila-sol-workspace').length > 0;
}

/** Exibe o formulário da dash-cha e oculta "Aguardando conexão..." após injetar a página. */
function stDashChaRevealAfterLoad() {
    if (!$('#load_conn').length || !$('#dashboard.st-dash-cha-workspace').length) {
        return;
    }
    if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) {
        return;
    }
    if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
        return;
    }

    var now = Date.now();
    window._stDashChaRevealLast = window._stDashChaRevealLast || 0;
    if (now - window._stDashChaRevealLast < 2500) {
        return;
    }
    window._stDashChaRevealLast = now;

    function revealHome() {
        $('#div_ope').show();
        if ($('#div_pend').length) {
            $('#div_pend').show();
        }
        $('#load_conn').hide();
        if (typeof window.stDashChaBotInit === 'function') {
            window.stDashChaBotInit();
        }
        window.stChatSolClassModalOpen = false;
        window.redirecionandoAtendimento = false;
        window.stChatSolOpeningAte = false;
        if (window.stChatSolEnded) {
            window.stChatSolEnded = false;
        }
    }

    if (window.stChatSolEnded) {
        revealHome();
        return;
    }

    function runLoadChatIn() {
        if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) {
            revealHome();
            return;
        }
        if (stChatSolFilaPageActive()) {
            revealHome();
            return;
        }
        if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
            revealHome();
            return;
        }
        if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
            revealHome();
            return;
        }
        if (typeof loadChatIn === 'function' && loadChatIn !== '') {
            loadChatIn();
            return;
        }
        revealHome();
    }

    if (typeof conn !== 'undefined' && conn.readyState === 1) {
        runLoadChatIn();
        return;
    }

    if (window.stChatOpen && typeof window.stChatOpen.whenConnReady === 'function') {
        window.stChatOpen.whenConnReady(runLoadChatIn);
        return;
    }

    setTimeout(runLoadChatIn, 400);
}
window.stDashChaRevealAfterLoad = stDashChaRevealAfterLoad;

function stChatHideWaitingBanner(chatId) {
    chatId = String(chatId || '');
    if (!chatId) {
        return;
    }
    $('#chat-content_' + chatId).find('.st-chat-waiting').remove();
    var adminEl = stChatGetAdminContentEl(chatId);
    if (adminEl && adminEl.div) {
        $(adminEl.div).find('.st-chat-waiting').remove();
    }
}
window.stChatHideWaitingBanner = stChatHideWaitingBanner;

function stChatTryStartAtendimento(chatId, tokenChat) {
    chatId = String(chatId || '');
    tokenChat = String(tokenChat || '');
    if (!chatId && !tokenChat) {
        return;
    }
    if (typeof stChatIsEnded === 'function' && stChatIsEnded(chatId, tokenChat)) {
        return;
    }

    window.stChatSyncInFlight = window.stChatSyncInFlight || {};
    var syncKey = chatId || tokenChat;
    if (window.stChatSyncInFlight[syncKey]) {
        return;
    }

    function stChatApplyTe(ret) {
        if (!ret || !ret.te_updated || !ret.te) {
            return;
        }
        var indiceTab = stChatResolveIndiceForChat(chatId) || 1;
        var $te = $('#div_te_' + indiceTab);
        if (!$te.length) {
            $te = $('[data-chat-id="' + chatId + '"]').find('.st-chat-timer').not('.st-chat-timer--ta').first();
        }
        if (!$te.length) {
            $te = $('#div_te');
        }
        if ($te.length) {
            $te.data('st-te-frozen', 1);
            $te.html('<i class="fas fa-history" aria-hidden="true"></i> TE: ' + ret.te);
        }
    }

    function stChatApplyTaStart(ret) {
        if (!ret || !ret.started || !ret.hora_inicio) {
            return;
        }
        var $ta = $('#div_ta_' + chatId);
        if (!$ta.length) {
            return;
        }
        var taTimerKey = 'c' + chatId;
        var timerAtivo = window.stBkoTaTimers && window.stBkoTaTimers[taTimerKey];
        if ($ta.data('st-ta-running') && timerAtivo) {
            return;
        }
        var waitKey = 'stBkoTaWait_' + chatId;
        if (window[waitKey]) {
            clearInterval(window[waitKey]);
            window[waitKey] = null;
        }
        if (typeof window.stBkoStartTa === 'function' && chatId) {
            var indiceTab = stChatResolveIndiceForChat(chatId) || 1;
            $ta.data('st-ta-running', 1);
            window.stBkoStartTa(chatId, ret.hora_inicio, indiceTab, ret.ta_elapsed);
        }
    }

    function stChatSyncHoraInicio() {
        window.stChatSyncInFlight[syncKey] = true;
        $.ajax({
            url: 'staff/sync_hora_inicio_chat.php',
            type: 'POST',
            dataType: 'json',
            data: { chatId: chatId, tokenChat: tokenChat }
        }).always(function () {
            window.stChatSyncInFlight[syncKey] = false;
        }).done(function (ret) {
            if (!ret || !ret.ok) {
                return;
            }
            if (ret.te_updated) {
                stChatApplyTe(ret);
            }
            if (ret.both_joined || ret.started) {
                stChatHideWaitingBanner(chatId);
            }
            if (ret.started && ret.hora_inicio) {
                stChatApplyTaStart(ret);
            }
        });
    }

    stChatSyncHoraInicio();
}
window.stChatTryStartAtendimento = stChatTryStartAtendimento;

function stChatInlineBlobImages(html) {
    return new Promise(function (resolve) {
        var content = String(html || '');
        if (!content || content.indexOf('blob:') === -1) {
            resolve(content);
            return;
        }
        var root = document.createElement('div');
        root.innerHTML = content;
        var blobImgs = [];
        root.querySelectorAll('img').forEach(function (img) {
            var src = img.getAttribute('src') || '';
            if (src.indexOf('blob:') === 0) {
                blobImgs.push(img);
            }
        });
        if (!blobImgs.length) {
            resolve(content);
            return;
        }
        var done = 0;
        var finish = function () {
            resolve(root.innerHTML);
        };
        blobImgs.forEach(function (img) {
            var src = img.getAttribute('src');
            fetch(src).then(function (response) {
                return response.blob();
            }).then(function (blob) {
                return new Promise(function (res) {
                    var reader = new FileReader();
                    reader.onload = function () {
                        img.setAttribute('src', reader.result);
                        res();
                    };
                    reader.onerror = function () {
                        res();
                    };
                    reader.readAsDataURL(blob);
                });
            }).catch(function () {
                return null;
            }).then(function () {
                done++;
                if (done >= blobImgs.length) {
                    finish();
                }
            });
        });
    });
}
window.stChatInlineBlobImages = stChatInlineBlobImages;

function stChatSolAfterClassSave() {
    window.stChatSolEnded = true;
    window.redirecionandoAtendimento = false;
    window.stChatSolOpeningAte = false;
    if (typeof loadChatIn !== 'undefined') {
        loadChatIn = '';
    }
    if (typeof window.load === 'function') {
        window.load = function () { };
    }
    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('.modal.show').forEach(function (modalEl) {
            var modalInst = bootstrap.Modal.getInstance(modalEl);
            if (modalInst) {
                modalInst.hide();
            }
        });
    }
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
    $('#dash-cha').show();
    $('#hist-dash').show();
    $('#hist-pend').show();
    $('#com-idx').show();
    $('#sair').show();
    if (typeof window.actionPageNav === 'function') {
        window.actionPageNav('dash-cha', 'idx');
        return;
    }
    if (typeof window.actionPage === 'function') {
        window.actionPage('dash-cha', 'idx');
        return;
    }
    location.reload();
}

function stChatSolOpenClassModal(chatId) {
    if (window.stChatSolClassModalOpen) {
        return;
    }
    window.stChatSolEnded = true;
    window.stChatSolClassModalOpen = true;
    var $trigger = $('#btn_pos_' + chatId);
    if (!$trigger.length) {
        return;
    }
    if (typeof bootstrap !== 'undefined') {
        var modalEl = document.getElementById('div_pos_' + chatId);
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: 'static',
                keyboard: false
            }).show();
            return;
        }
    }
    $trigger.trigger('click');
}

function loadPos(indice, chatId) {
    chatId = String(chatId || '');
    if (window.stChatPosOpened && window.stChatPosOpened[chatId]) {
        return;
    }
    indice = stChatResolveIndiceForChat(chatId) || parseInt(indice, 10) || 0;
    window.stChatPosOpened = window.stChatPosOpened || {};
    window.stChatPosOpened[chatId] = true;
    if ($('#content-bko').length && typeof window.actionPagePos === 'function') {
        if (typeof window.stChatTypingUnbind === 'function') {
            window.stChatTypingUnbind(chatId);
        }
        window.actionPagePos(indice, chatId);
        return;
    }
    if (typeof stChatSolOpenClassModal === 'function') {
        stChatSolOpenClassModal(chatId);
        return;
    }
    if ($('#btn_pos_' + chatId).length) {
        $('#btn_pos_' + chatId).trigger('click');
    }
}
window.loadPos = loadPos;

function fim(rawData, userDestinatario, userRemetente) {
    var data;
    try {
        data = JSON.parse(rawData);
    } catch (e) {
        return;
    }
    if (!data || !data.chatId) {
        return;
    }
    if (data.flagFim === 'true' && typeof stChatMarkEnded === 'function') {
        stChatMarkEnded(String(data.chatId), data.tokenChat || '');
    }
    if (data.flagFim === 'true' && data.tokenChat && typeof stChatPersistFim === 'function') {
        stChatPersistFim(String(data.chatId), data.tokenChat, data.contrato || 0, data.msg || '');
    }
    if ($('#chat-content_' + data.chatId).length && typeof showMessagesSys === 'function') {
        var indiceVal = stChatResolveIndiceForChat(data.chatId);
        if (!indiceVal) {
            var $ind = $('#indice_' + data.chatId);
            if ($ind.length) {
                indiceVal = parseInt($ind.val(), 10) || 0;
            }
        }
        showMessagesSys(data.chatId, 'sys', rawData, userDestinatario, indiceVal);
    }
}

function stChatSolBkoContext() {
    return $('#content-bko').length > 0;
}

function stChatInvokeLoadChatIn() {
    if (stChatSolBkoContext()) {
        return;
    }
    if (window.stChatSolEnded || window.stChatSolClassModalOpen) {
        return;
    }
    if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) {
        return;
    }
    if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) {
        return;
    }
    if (typeof window.redirecionandoAtendimento !== 'undefined' && window.redirecionandoAtendimento) {
        return;
    }
    if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) {
        return;
    }

    var now = Date.now();
    window._stLoadChatInLast = window._stLoadChatInLast || 0;
    if (now - window._stLoadChatInLast < 2000) {
        return;
    }
    window._stLoadChatInLast = now;

    if (stChatSolFilaPageActive()) {
        if (typeof window.stFilaSolPollAtendimentoAgora === 'function') {
            window.stFilaSolPollAtendimentoAgora();
        }
        return;
    }

    if (typeof loadChatIn !== 'function' || loadChatIn === '') {
        return;
    }

    if (window.stChatOpen && typeof stChatOpen.scheduleLoadChatIn === 'function') {
        stChatOpen.scheduleLoadChatIn(stChatOpen.LOAD_CHAT_IN_DELAY);
    } else {
        setTimeout(function () { loadChatIn(); }, 120);
    }
}

function stChatScheduleFilaPoll(delayMs) {
    if (stChatSolBkoContext()) {
        return;
    }
    if (stChatSolWorkspaceActive()) {
        return;
    }
    if (window.stChatSolEnded || window.stChatSolClassModalOpen) {
        return;
    }
    if (!stChatSolFilaPageActive() && typeof window.redirecionandoAtendimento !== 'undefined' && window.redirecionandoAtendimento) {
        return;
    }
    if (stChatSolFilaPageActive() && typeof window.stFilaSolPollAtendimentoAgora === 'function') {
        setTimeout(function () {
            window.stFilaSolPollAtendimentoAgora();
        }, typeof delayMs === 'number' ? delayMs : 120);
        return;
    }
    if (typeof load !== 'function') {
        return;
    }
    setTimeout(function () { load(); }, typeof delayMs === 'number' ? delayMs : 500);
}

function setServerStatus(state) {
    var labels = {
        online: 'Sistema online',
        offline: 'Sistema offline',
        neutro: 'Conectando...'
    };
    var cls = 'signal status_' + state;
    $('#sinal_server, #sinal_server_footer').attr('class', cls);
    if ($('#footer-status-label').length) {
        $('#footer-status-label').text(labels[state] || labels.neutro);
    }
}

function stChatHideById(id) {
    var el = document.getElementById(id);
    if (el) {
        el.style.display = 'none';
    }
}

function stChatDisableById(id) {
    var el = document.getElementById(id);
    if (!el) {
        return;
    }
    el.disabled = true;
    el.style.display = 'none';
}

function stChatLockComposerOnEnd(chatId) {
    stChatDisableById('message_' + chatId);
    stChatHideById('div_message_' + chatId);
    stChatDisableById('msg_' + chatId);
    stChatHideById('btn1_' + chatId);
    stChatHideById('btn_file_' + chatId);
    stChatHideById('btn_file_responsive_' + chatId);
    stChatHideById('btn_fin_' + chatId);
    stChatHideById('btn_fin_responsive_' + chatId);
    stChatHideById('btn_atent_' + chatId);
    stChatHideById('btn_atent_responsive_' + chatId);
    stChatHideById('trasnferir_' + chatId);
    stChatHideById('file_' + chatId);
    var sel = document.getElementById('select_in_chat_' + chatId);
    if (sel) {
        sel.disabled = true;
        sel.style.display = 'none';
    }
    stChatHideById('div_tempo_' + chatId);
}

function stChatSysMsgText(inp_message) {
    if (inp_message == null) {
        return '';
    }
    if (typeof inp_message === 'string') {
        return inp_message;
    }
    if (inp_message.value != null && inp_message.value !== '') {
        return String(inp_message.value);
    }
    return String(inp_message);
}

function stChatHasSysMsg(chatId, text) {
    if (!text || !$('#chat-content_' + chatId).length) {
        return false;
    }
    var found = false;
    $('#chat-content_' + chatId).find('.sys .paragrafo').each(function () {
        if ($(this).text() === text) {
            found = true;
            return false;
        }
    });
    return found;
}

function connect(conn, status, host) {

    if (conn.readyState === 3) {
        var conn = new WebSocket(host);
        console.log(host);
    }

    conn.onopen = function (e) {
        //console.log("Estamos Online!");
        console.log("1000");
        console.log(host);

        if (typeof recon !== 'undefined') {
            //console.log('apaga recon');
            clearTimeout(recon);
        }
        //console.log(conn.readyState);
        if (typeof load === 'function' && !stChatSolBkoContext()) {
            if (conn.readyState === 1) {
                stChatScheduleFilaPoll(120);
            }
        }

        if (typeof loadContent === 'function') {
            //console.log('Tem func loadContent');
            setTimeout(function () { loadContent(); }, 120);
        }

        if (!stChatSolBkoContext()) {
            stChatInvokeLoadChatIn();
        }

        if ($('#content-bko .st-chat-workspace--bko').length && typeof sendAtend === 'function') {
            sendAtend();
        }
        if (stChatSolFilaPageActive() && typeof window.stFilaSolPollAtendimentoAgora === 'function') {
            window.stFilaSolPollAtendimentoAgora();
        }

        if (typeof loadIdx !== 'undefined') {
            //console.log('Carrega IDX');
            setTimeout(function () { loadIdx(); }, 500);
        }

        //console.log(host);
        //console.log(status);
        if (status === 1) {
            //console.log("Teste span 1");
            document.location.reload(true);
        }


        setServerStatus('online');
        //$("input").prop('disabled', false);
    };

    conn.onmessage = function (e) {
        data = JSON.parse(e.data);
        //console.log(data.count);
        $('#dadosLogados').html(data.count);
    }

    conn.onclose = function (e) {
        //console.log('Estamos Offline, o sistema tentará se reconectar em 20 segundos');
        console.log("9999");
        //console.log('Estamos Offline');
        //console.log(conn.readyState);
        conn.close();
        $('#dadosLogados').html('---');
        setServerStatus('offline');
        //$("input").prop('disabled', true);
        //console.log("Teste span 2");
        var recon = setTimeout(function () {
            //connect(conn, 0, host);
            //console.log('Tentando conectar...');

            document.location.reload(true);
        }, 3000);
    }

    conn.onerror = function (err) {
        //console.error('Erro encontrado ->', err.message, ' O sistema tentará se reconectar em 10 segundos');
        //console.log('indic. 1');
        console.log("8888");
        conn.close();
        $('#dadosLogados').html('---');
        setServerStatus('offline');
        //$("input").prop('disabled', true);
        //setTimeout(function() { connect(conn, 1, host); }, 10000);
    };
}

connect(conn, 0, host);

//console.log(conn.readyState);




function chatMsg(chatId, destinatario, contrato, token) {


    var form_txt = 'form_' + chatId;
    var message_txt = 'message_' + chatId;
    var name_txt = 'name_' + chatId;
    var id_user_remetente_txt = 'id_user_remetente_' + chatId;
    var id_user_destinatario_txt = 'id_user_destinatario_' + chatId;
    var img_txt = 'img_' + chatId;
    var btn1_txt = 'btn1_' + chatId;
    var chat_content_txt = 'chat-content_' + chatId;

    var form = document.getElementById(form_txt);
    var inp_message = document.getElementById(message_txt);
    var inp_name = document.getElementById(name_txt);
    var userRemetente = document.getElementById(id_user_remetente_txt);
    var userDestinatario = document.getElementById(id_user_destinatario_txt);
    var inp_img = document.getElementById(img_txt);
    var area_content = document.getElementById(chat_content_txt);

    var contrato = contrato;
    var tokenChat = token;


    saveMsg(chatId, inp_message, userDestinatario, userRemetente, tokenChat, contrato);
    sendMessage(chatId, inp_message, inp_name, userDestinatario, userRemetente, inp_img);



}

function chatFim(chatId, destinatario, contrato, token, mensagem, indice) {

    if (typeof stChatIsEnded === 'function' && stChatIsEnded(chatId, token) && window.stChatPosOpened && window.stChatPosOpened[String(chatId)]) {
        return;
    }
    var form_txt = 'form_' + chatId;
    var id_user_remetente_txt = 'id_user_remetente_' + chatId;
    var id_user_destinatario_txt = 'id_user_destinatario_' + chatId;
    var btn1_txt = 'btn1_' + chatId;
    var chat_content_txt = 'chat-content_' + chatId;

    var form = document.getElementById(form_txt);
    var inp_message = mensagem;
    var inp_name = 'Solvetask';
    var userRemetente = document.getElementById(id_user_remetente_txt);
    var userDestinatario = document.getElementById(id_user_destinatario_txt);
    var area_content = document.getElementById(chat_content_txt);

    var contrato = contrato;
    var tokenChat = token;

    //console.log('chatFim: '+ indice);


    saveMsgFim(chatId, inp_message, tokenChat, contrato);
    sendMessageFim(chatId, inp_message, userDestinatario, userRemetente, indice, tokenChat, contrato);
    if (typeof load === 'function') { stChatScheduleFilaPoll(500); }

}

function chatTransfer(chatId, destinatario, contrato, token, mensagem) {


    var form_txt = 'form_' + chatId;
    var id_user_remetente_txt = 'id_user_remetente_' + chatId;
    var fila_txt = 'fila_' + chatId;
    var assunto_txt = 'assunto_' + chatId;
    var id_user_destinatario_txt = 'id_user_destinatario_' + chatId;
    var btn1_txt = 'btn1_' + chatId;
    var chat_content_txt = 'chat-content_' + chatId;

    var form = document.getElementById(form_txt);
    var inp_message = mensagem;
    var inp_name = 'Solvetask';
    var userRemetente = document.getElementById(id_user_remetente_txt);
    var userDestinatario = document.getElementById(id_user_destinatario_txt);
    var ipt_fila = document.getElementById(fila_txt);
    var ipt_assunto = document.getElementById(assunto_txt);
    var area_content = document.getElementById(chat_content_txt);

    var contrato = contrato;
    var tokenChat = token;

    var fila = ipt_fila.value;
    var assunto = ipt_assunto.value;


    saveMsgTransfer(chatId, inp_message, tokenChat, contrato, fila, assunto);
    sendMessageTransfer(chatId, inp_message, userDestinatario, userRemetente);

}

function chatIn(chatId, destinatario, contrato, token, mensagem, indice) {


    var form_txt = 'form_' + chatId;
    var id_user_remetente_txt = 'id_user_remetente_' + chatId;
    var id_user_destinatario_txt = 'id_user_destinatario_' + chatId;
    var btn1_txt = 'btn1_' + chatId;
    var chat_content_txt = 'chat-content_' + chatId;

    var form = document.getElementById(form_txt);
    var inp_message = mensagem;
    var inp_name = 'Solvetask';
    var userRemetente = document.getElementById(id_user_remetente_txt);
    var userDestinatario = document.getElementById(id_user_destinatario_txt);
    var area_content = document.getElementById(chat_content_txt);

    var contrato = contrato;
    var tokenChat = token;

    saveMsgSys(chatId, inp_message, tokenChat, contrato, userRemetente);
    sendMessageSys(chatId, inp_message, userDestinatario, userRemetente, indice);
    if (typeof stChatTryStartAtendimento === 'function') {
        setTimeout(function () {
            stChatTryStartAtendimento(chatId, token);
        }, 400);
    }

}

function chatAtent(chatId, destinatario, contrato, token, mensagem) {


    var form_txt = 'form_' + chatId;
    var id_user_remetente_txt = 'id_user_remetente_' + chatId;
    var id_user_destinatario_txt = 'id_user_destinatario_' + chatId;
    var btn1_txt = 'btn1_' + chatId;
    var chat_content_txt = 'chat-content_' + chatId;

    var form = document.getElementById(form_txt);
    var inp_message = mensagem;
    var inp_name = 'Solvetask';
    var userRemetente = document.getElementById(id_user_remetente_txt);
    var userDestinatario = document.getElementById(id_user_destinatario_txt);
    var area_content = document.getElementById(chat_content_txt);

    var contrato = contrato;
    var tokenChat = token;

    saveMsgAtent(chatId, inp_message, tokenChat, contrato);
    sendAtent(chatId, inp_message, userDestinatario, userRemetente);

}



function sendMessage(chatId, inp_message, inp_name, userDestinatario, userRemetente, inp_img) {
    var data = new Date();
    var dia = data.getDate();
    var mes = data.getMonth();
    var ano4 = data.getFullYear();
    var hora = data.getHours();
    var min = data.getMinutes();
    var seg = data.getSeconds();
    mes = (mes + 1);
    if (mes < 10) { mes = '0' + mes; }
    var str_hora = hora + ':' + min;
    var str_hora_sql = hora + ':' + min + ':' + seg;
    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;


    if (chatMsgHasContent(inp_message.value)) {
        var msg = {
            'flagMsg': 'true',
            'chatId': chatId,
            'userRemetente': userRemetente.value,
            'userDestinatario': userDestinatario.value,
            'name': inp_name.value,
            'msg': inp_message.value,
            'dataHora': str_data,
            'img': inp_img.value
        };

        msg = JSON.stringify(msg);
        //console.log(msg);
        //conn.send(msg);
        if (conn.send(msg)) {
            //console.log('Mensagem enviada!');
        }

        //console.log(msg);
        //console.log("Teste 1");

        stChatShowMsg(chatId, 'me', msg, userDestinatario.value, userRemetente.value);
        inp_message.value = '';

    }
}

function sendMessageSys(chatId, inp_message, userDestinatario, userRemetente, indice) {
    var data = new Date();
    var dia = data.getDate();
    var mes = data.getMonth();
    var ano4 = data.getFullYear();
    var hora = data.getHours();
    var min = data.getMinutes();
    var seg = data.getSeconds();
    mes = (mes + 1);
    if (mes < 10) { mes = '0' + mes; }
    var str_hora = hora + ':' + min;
    var str_hora_sql = hora + ':' + min + ':' + seg;
    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;


    if (stChatSysMsgText(inp_message) !== '') {
        var msgText = stChatSysMsgText(inp_message);
        if (!msgText) {
            return;
        }
        var msg = {
            'flagSys': 'true',
            'chatId': chatId,
            'userRemetente': userRemetente.value,
            'userDestinatario': userDestinatario.value,
            'name': 'Solvetask',
            'msg': msgText,
            'dataHora': str_data
        };

        msg = JSON.stringify(msg);
        //console.log(msg);
        //conn.send(msg);
        if (conn.send(msg)) {
            //console.log('Mensagem enviada!');
        }
        //console.log(msg);
        if (!stChatHasSysMsg(chatId, msgText)) {
            stChatShowSysMsg(chatId, 'sys', msg, userDestinatario.value, indice);
        }


    }
}

function sendAtent(chatId, inp_message, userDestinatario, userRemetente) {
    var data = new Date();
    var dia = data.getDate();
    var mes = data.getMonth();
    var ano4 = data.getFullYear();
    var hora = data.getHours();
    var min = data.getMinutes();
    var seg = data.getSeconds();
    mes = (mes + 1);
    if (mes < 10) { mes = '0' + mes; }
    var str_hora = hora + ':' + min;
    var str_hora_sql = hora + ':' + min + ':' + seg;
    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;


    if (inp_message.value != '') {
        var msg = {
            'flagSys': 'Atent',
            'chatId': chatId,
            'userRemetente': userRemetente.value,
            'userDestinatario': userDestinatario.value,
            'name': 'Solvetask',
            'msg': inp_message,
            'dataHora': str_data
        };

        msg = JSON.stringify(msg);
        //console.log(msg);
        //conn.send(msg);
        if (conn.send(msg)) {
            //console.log('Mensagem enviada!');
        }
        //console.log(msg);
        //showMessagesSys('sys', msg, userDestinatario.value);
        stChatShowSysMsg(chatId, 'sys', msg, userDestinatario.value, stChatResolveIndiceForChat(chatId));


    }
}

function sendMessageFim(chatId, inp_message, userDestinatario, userRemetente, indice, tokenChat, contrato) {
    var data = new Date();
    var dia = data.getDate();
    var mes = data.getMonth();
    var ano4 = data.getFullYear();
    var hora = data.getHours();
    var min = data.getMinutes();
    var seg = data.getSeconds();
    mes = (mes + 1);
    if (mes < 10) { mes = '0' + mes; }
    var str_hora = hora + ':' + min;
    var str_hora_sql = hora + ':' + min + ':' + seg;
    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;

    var msgText = stChatSysMsgText(inp_message);
    if (!msgText) {
        return;
    }

    var msg = {
        'flagSys': 'false',
        'flagFim': 'true',
        'chatId': chatId,
        'tokenChat': tokenChat || '',
        'contrato': contrato || 0,
        'userRemetente': userRemetente.value,
        'userDestinatario': userDestinatario.value,
        'name': 'Solvetask',
        'msg': msgText,
        'dataHora': str_data
    };

    msg = JSON.stringify(msg);
    if (conn.send(msg)) {
    }
    if (typeof stChatMarkEnded === 'function') {
        stChatMarkEnded(String(chatId), tokenChat || '');
    }
    stChatShowSysMsg(chatId, 'sys', msg, userDestinatario.value, indice);
}

function sendMessageTransfer(chatId, inp_message, userDestinatario, userRemetente) {
    var data = new Date();
    var dia = data.getDate();
    var mes = data.getMonth();
    var ano4 = data.getFullYear();
    var hora = data.getHours();
    var min = data.getMinutes();
    var seg = data.getSeconds();
    mes = (mes + 1);
    if (mes < 10) { mes = '0' + mes; }
    var str_hora = hora + ':' + min;
    var str_hora_sql = hora + ':' + min + ':' + seg;
    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;


    if (inp_message.value != '') {
        var msg = {
            'flagSys': 'transfer',
            'chatId': chatId,
            'userRemetente': userRemetente.value,
            'userDestinatario': userDestinatario.value,
            'name': 'Solvetask',
            'msg': inp_message,
            'dataHora': str_data
        };

        msg = JSON.stringify(msg);
        //console.log(msg);
        //conn.send(msg);
        if (conn.send(msg)) {
            //console.log('Mensagem enviada!');
        }
        //console.log(msg);
        stChatShowSysMsg(chatId, 'sys', msg, userDestinatario.value, indice);


    }
}

window.stChatTypingState = window.stChatTypingState || {};
window.stChatTypingDisplay = window.stChatTypingDisplay || {};

var ST_CHAT_TYPING_EMIT_MS = 2000;
var ST_CHAT_TYPING_SHOW_MS = 3000;

function stChatComposerHasText(chatId) {
    chatId = String(chatId || '');
    var readPlain = window['getChatPlainText_' + chatId];
    if (typeof readPlain === 'function') {
        return !!readPlain();
    }
    var $visual = $('#div_message_' + chatId + ' .st-chat-composer-visual');
    if ($visual.length) {
        var visualHtml = $visual.html() || '';
        if (/<img\b/i.test(visualHtml)) {
            return true;
        }
        return !!String($visual.text() || '').replace(/\u00a0/g, ' ').trim();
    }
    var $ta = $('#msg_' + chatId);
    if ($ta.length) {
        return !!String($ta.val() || '').replace(/\u00a0/g, ' ').trim();
    }
    return false;
}

function stChatTypingHide(chatId) {
    chatId = String(chatId || '');
    if (!chatId) {
        return;
    }
    ['chat:', 'admin:'].forEach(function (prefix) {
        var key = prefix + chatId;
        if (window.stChatTypingDisplay[key]) {
            clearTimeout(window.stChatTypingDisplay[key]);
            delete window.stChatTypingDisplay[key];
        }
    });
    $('#dig_' + chatId).html('');
    $('#dig_0_' + chatId).html('');
}

function stChatTypingShow(chatId, rawData, admin) {
    var data;
    try {
        data = (typeof rawData === 'string') ? JSON.parse(rawData) : rawData;
    } catch (errParse) {
        return;
    }
    if (!data || !data.chatId) {
        return;
    }
    if (typeof userRem !== 'undefined') {
        if (String(data.userRemetente) === String(userRem)) {
            return;
        }
        if (!admin && String(data.userDestinatario) !== String(userRem)) {
            return;
        }
    }

    chatId = String(data.chatId);
    var name = data.name || 'Usuário';
    var label = name + ' está digitando...';
    var selector = admin ? '#dig_0_' + chatId : '#dig_' + chatId;
    var $el = $(selector);
    if (!$el.length) {
        return;
    }

    $el.html('<span class="st-chat-typing-indicator">' + $('<span>').text(label).html() + '</span>');

    var key = (admin ? 'admin:' : 'chat:') + chatId;
    if (window.stChatTypingDisplay[key]) {
        clearTimeout(window.stChatTypingDisplay[key]);
    }
    window.stChatTypingDisplay[key] = setTimeout(function () {
        $el.html('');
        delete window.stChatTypingDisplay[key];
    }, ST_CHAT_TYPING_SHOW_MS);
}

function stChatTypingPulse(chatId, contrato, token) {
    chatId = String(chatId || '');
    if (!chatId || !stChatComposerHasText(chatId)) {
        return;
    }
    if (typeof conn === 'undefined' || !conn || conn.readyState !== 1) {
        return;
    }

    var state = window.stChatTypingState[chatId];
    if (!state) {
        window.stChatTypingState[chatId] = { lastEmit: 0, pollId: null, bound: false };
        state = window.stChatTypingState[chatId];
    }
    if (contrato != null) {
        state.contrato = contrato;
    }
    if (token != null) {
        state.token = token;
    }

    var now = Date.now();
    if (now - (state.lastEmit || 0) < ST_CHAT_TYPING_EMIT_MS) {
        return;
    }
    state.lastEmit = now;

    var dest = $('#id_user_destinatario_' + chatId).val();
    keyMsg(chatId, dest, state.contrato, state.token);
}

function stChatBindTypingIndicator(chatId, contrato, token) {
    chatId = String(chatId || '');
    if (!chatId) {
        return;
    }

    var state = window.stChatTypingState[chatId];
    if (!state) {
        window.stChatTypingState[chatId] = { lastEmit: 0, pollId: null, bound: false };
        state = window.stChatTypingState[chatId];
    }
    if (state.bound) {
        state.contrato = contrato;
        state.token = token;
        return;
    }

    state.bound = true;
    state.contrato = contrato;
    state.token = token;

    var $ta = $('#msg_' + chatId);
    var $visual = $('#div_message_' + chatId + ' .st-chat-composer-visual');
    var pulse = function () {
        stChatTypingPulse(chatId);
    };
    $ta.off('input.stTyping keyup.stTyping').on('input.stTyping keyup.stTyping', pulse);
    $visual.off('input.stTyping keyup.stTyping').on('input.stTyping keyup.stTyping', pulse);

    if (state.pollId) {
        clearInterval(state.pollId);
    }
    state.pollId = setInterval(function () {
        if (!$('#div_message_' + chatId).length && !$ta.length && !$visual.length) {
            stChatTypingUnbind(chatId);
            return;
        }
        if (stChatComposerHasText(chatId)) {
            stChatTypingPulse(chatId);
        }
    }, 500);
}

function stChatTypingUnbind(chatId) {
    chatId = String(chatId || '');
    var state = window.stChatTypingState[chatId];
    if (!state) {
        return;
    }
    if (state.pollId) {
        clearInterval(state.pollId);
        state.pollId = null;
    }
    $('#msg_' + chatId).off('input.stTyping keyup.stTyping');
    $('#div_message_' + chatId + ' .st-chat-composer-visual').off('input.stTyping keyup.stTyping');
    state.bound = false;
    state.lastEmit = 0;
}

window.stChatBindTypingIndicator = stChatBindTypingIndicator;
window.stChatTypingPulse = stChatTypingPulse;
window.stChatTypingUnbind = stChatTypingUnbind;
window.stChatTypingHide = stChatTypingHide;

function keyMsg(chatId, destinatario, contrato, token) {


    var form_txt = 'form_' + chatId;
    var message_txt = 'message_' + chatId;
    var name_txt = 'name_' + chatId;
    var id_user_remetente_txt = 'id_user_remetente_' + chatId;
    var id_user_destinatario_txt = 'id_user_destinatario_' + chatId;
    var img_txt = 'img_' + chatId;
    var btn1_txt = 'btn1_' + chatId;
    var chat_content_txt = 'chat-content_' + chatId;

    var form = document.getElementById(form_txt);
    var inp_message = document.getElementById(message_txt);
    var inp_name = document.getElementById(name_txt);
    var userRemetente = document.getElementById(id_user_remetente_txt);
    var userDestinatario = document.getElementById(id_user_destinatario_txt);
    var inp_img = document.getElementById(img_txt);
    var area_content = document.getElementById(chat_content_txt);

    var contrato = contrato;
    var tokenChat = token;


    sendKey(chatId, inp_name, userDestinatario, userRemetente);



}


function sendAtend() {
    var now = Date.now();
    if (!window._stSendAtendLast) {
        window._stSendAtendLast = 0;
    }
    if (now - window._stSendAtendLast < 1800) {
        return;
    }
    window._stSendAtendLast = now;
    var msg = { "flagAtend": "true" };
    msg = JSON.stringify(msg);
    conn.send(msg);
    if (typeof load === 'function') {
        stChatScheduleFilaPoll(500);
    }
}

function sendBko() {
    var msg = { "flagBko": "true" };
    msg = JSON.stringify(msg);
    //console.log(msg);
    conn.send(msg);
    //setTimeout(function(){ load(); }, 500);
    if (typeof load === 'function') {
        stChatScheduleFilaPoll(120);
    }

    stChatInvokeLoadChatIn();

}

function sendFile(chatId) {

    var msg = {
        "flagFile": "true",
        "chatId": chatId
    };
    msg = JSON.stringify(msg);
    //console.log(msg);
    conn.send(msg);
    if (typeof loadFileDiv === 'function') {
        loadFileDiv(chatId);
    }
}


function sendKey(chatId, inp_name, userDestinatario, userRemetente) {

    var data = new Date();
    var dia = data.getDate();
    var mes = data.getMonth();
    var ano4 = data.getFullYear();
    var hora = data.getHours();
    var min = data.getMinutes();
    var seg = data.getSeconds();
    mes = (mes + 1);
    if (mes < 10) { mes = '0' + mes; }
    var str_hora = hora + ':' + min;
    var str_hora_sql = hora + ':' + min + ':' + seg;
    var str_data = dia + '/' + mes + '/' + ano4 + ' ' + str_hora;
    var str_data_sql = ano4 + '-' + mes + '-' + dia + ' ' + str_hora_sql;

    if (userRemetente != null) {


        var msg = {
            'flagDig': 'true',
            'chatId': chatId,
            'userRemetente': userRemetente.value,
            'userDestinatario': userDestinatario.value,
            'name': inp_name.value,
            'dataHora': str_data
        };

        msg = JSON.stringify(msg);

        //console.log(msg);
        //conn.send(msg);
        if (conn.send(msg)) {
            //console.log('Mensagem enviada!');
        }
        //console.log(msg);
        //showDig('me', msg, userDestinatario.value);
        //inp_message.value = '';
    }



}



function saveMsg(chatId, inp_message, userDestinatario, userRemetente, tokenChat, contrato) {

    var msg = inp_message.value;
    var dest = userDestinatario.value;
    var rem = userRemetente.value;
    var tokenChat = tokenChat;
    var contrato = contrato;
    var feed = '#feed_' + dest;


    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
    $.post("staff/save_msg.php",
        {
            msg, dest, rem, tokenChat, contrato
        },
        function (valor) {
            $(feed).html(stSafeChatHtml(valor));
        });


}

function saveMsgFim(chatId, inp_message, tokenChat, contrato) {

    var msg = stChatSysMsgText(inp_message);
    var dest = 0;
    var rem = 0;
    var feed = '#feed_' + chatId;

    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
    $.ajax({
        url: 'staff/save_msg_fim.php',
        type: 'POST',
        dataType: 'json',
        data: { msg: msg, dest: dest, rem: rem, tokenChat: tokenChat, contrato: contrato }
    }).done(function (ret) {
        window.stChatFimPersisted = window.stChatFimPersisted || {};
        window.stChatFimPersisted[tokenChat] = true;
        if (ret && ret.ok && typeof stChatMarkEnded === 'function') {
            stChatMarkEnded(String(ret.chatId || chatId), tokenChat);
        }
        $(feed).html('');
    }).fail(function () {
        $(feed).html('');
    });
}

function stChatPersistFim(chatId, tokenChat, contrato, mensagem) {
    chatId = String(chatId || '');
    tokenChat = String(tokenChat || '');
    if (!tokenChat) {
        return;
    }
    if (typeof stChatIsEnded === 'function' && stChatIsEnded(chatId, tokenChat) && window.stChatFimPersisted && window.stChatFimPersisted[tokenChat]) {
        return;
    }
    window.stChatFimPersisted = window.stChatFimPersisted || {};
    window.stChatFimPersisted[tokenChat] = true;
    $.ajax({
        url: 'staff/save_msg_fim.php',
        type: 'POST',
        dataType: 'json',
        data: {
            msg: stChatSysMsgText(mensagem),
            dest: 0,
            rem: 0,
            tokenChat: tokenChat,
            contrato: contrato || 0
        }
    }).done(function (ret) {
        if (ret && ret.ok && typeof stChatMarkEnded === 'function') {
            stChatMarkEnded(String(ret.chatId || chatId), tokenChat);
        }
    });
}
window.stChatPersistFim = stChatPersistFim;

function saveMsgTransfer(chatId, inp_message, tokenChat, contrato, fila, assunto) {

    var msg = inp_message;
    var dest = 0;
    var rem = 0;
    var tokenChat = tokenChat;
    var contrato = contrato;
    var fila = fila;
    var assunto = assunto;
    var feed = '#feed_' + dest;

    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
    $.post("staff/save_msg_transfer.php",
        {
            chatId, msg, dest, rem, tokenChat, contrato, fila, assunto
        },
        function (valor) {
            $(feed).html(stSafeChatHtml(valor));
            //console.log(valor);
        });


}

function saveMsgSys(chatId, inp_message, tokenChat, contrato, userRemetente) {

    var msg = inp_message;
    var dest = 0;
    var rem = 0;
    var flagUser = userRemetente && userRemetente.value ? userRemetente.value : 0;
    var tokenChat = tokenChat;
    var contrato = contrato;
    var feed = '#feed_' + dest;


    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
    $.post("staff/save_msg.php",
        {
            msg: msg, dest: dest, rem: rem, tokenChat: tokenChat, contrato: contrato, flag: flagUser
        },
        function (valor) {
            $(feed).html(stSafeChatHtml(valor));
            var msgText = String(msg || '');
            if (typeof stChatTryStartAtendimento === 'function' &&
                (msgText.indexOf('entrou no chat') !== -1 || msgText.indexOf('voltou para o chat') !== -1)) {
                setTimeout(function () {
                    stChatTryStartAtendimento(String(chatId), tokenChat);
                }, 300);
            }
        });


}

function saveMsgAtent(chatId, inp_message, tokenChat, contrato) {

    var msg = inp_message;
    var dest = 0;
    var rem = 0;
    var tokenChat = tokenChat;
    var contrato = contrato;
    var feed = '#feed_' + dest;


    $(feed).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
    $.post("staff/save_msg.php",
        {
            msg, dest, rem, tokenChat, contrato
        },
        function (valor) {
            $(feed).html(stSafeChatHtml(valor));
        });


}

//console.log(conn);
if (typeof conn !== "undefined") {
    conn.onmessage = function (e) {

        data = JSON.parse(e.data);
        //console.log(data);
        if (data.count != '') {
            var ddCount = data.count;
            //console.log(ddCount);
            setTimeout(function () {
                $('#dadosLogados').html(ddCount);
                //console.log(ddCount);
            }, 3000);
        }

        if (data.flagMsg) {
            if (data.chatId && typeof stChatTypingHide === 'function') {
                stChatTypingHide(data.chatId);
            }
            if (data.userDestinatario) {
                //console.log(data);
                if (typeof userRem !== "undefined" && data.userDestinatario == userRem) {
                    stChatShowMsg(data.chatId, 'other', e.data, data.userDestinatario, data.userRemetente);
                    if (typeof newMessage === 'function') {
                        newMessage(data.chatId);
                    }


                    //setTimeout(function(){ load(); }, 500);
                    stChatScheduleFilaPoll(500);
                } else {
                    stChatShowMsg(data.chatId, 'me', e.data, data.userDestinatario, data.userRemetente);
                    stChatScheduleFilaPoll(500);
                }
            }
        }

        if (data.flagSys) {
            if (typeof userRem !== "undefined") {
                var sysFromSelf = String(data.userRemetente) === String(userRem);
                var sysJoin = data.flagSys === 'true' || data.flagSys === true;
                if (sysJoin && sysFromSelf) {
                    stChatScheduleFilaPoll(500);
                } else if (data.userDestinatario == userRem) {
                    if (!stChatHasSysMsg(data.chatId, data.msg)) {
                        var indiceTab = stChatResolveIndiceForChat(data.chatId);
                        stChatShowSysMsg(data.chatId, 'sys', e.data, data.userRemetente, indiceTab);
                    }
                    stChatScheduleFilaPoll(500);
                } else {
                    var indiceAdmin = stChatResolveIndiceForChat(data.chatId);
                    stChatShowSysMsg(data.chatId, 'sys', e.data, data.userDestinatario, indiceAdmin);
                    stChatScheduleFilaPoll(500);
                }
            }

        }


        if (data.flagBko == 'true') {
            if (stChatSolBkoContext() && typeof window.stBkoPulseWaitingTabs === 'function') {
                window.stBkoPulseWaitingTabs();
            } else if (!stChatSolFilaPageActive()) {
                stChatScheduleFilaPoll(500);
                stChatInvokeLoadChatIn();
            }
        }

        if (data.flagAtend == 'true') {
            if (stChatSolBkoContext()) {
                return;
            }
            if (typeof window.stFilaSolPollAtendimentoAgora === 'function') {
                window.stFilaSolPollAtendimentoAgora();
            } else {
                stChatScheduleFilaPoll(120);
            }
        }


        if (data.flagDig === 'true' || data.flagDig === true) {
            showDig(data.chatId, e.data);
            showDigAdmin(data.chatId, e.data);
            //setTimeout(function(){ load(); }, 500);
            //if(typeof load() !== "undefined"){
            //    setTimeout(function(){ load(); }, 500);
            //}
        }

        if (data.flagFim == 'true') {
            if (typeof fim === 'function') {
                fim(e.data, data.userDestinatario, data.userRemetente);
            }
        }

        if (data.flagFile == 'true') {
            var chatId = data.chatId;
            if (typeof loadFileDiv === 'function') {
                loadFileDiv(chatId);
            }
            stChatScheduleFilaPoll(500);
        }


    };
}

function nome_div() {
    min = Math.ceil(0);
    max = Math.floor(10000);
    return 'elMsg_' + Math.floor(Math.random() * (max - min)) + min;
}

(function () {
    var stAudioPrimed = false;

    function stGetChatAudio(id) {
        return document.getElementById(id);
    }

    window.safePlayMen = function () {
        var men_audio = stGetChatAudio('audio_men');
        if (!men_audio) {
            return;
        }
        try {
            var playPromise = men_audio.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () { });
            }
        } catch (errPlay) { }
    };

    window.safePlayAtent = function () {
        var audio_atent = stGetChatAudio('audio_atent');
        if (!audio_atent) {
            return;
        }
        try {
            var playPromise = audio_atent.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () { });
            }
        } catch (errPlay) { }
    };

    function primeChatAudio() {
        if (stAudioPrimed) {
            return;
        }
        stAudioPrimed = true;
        ['audio_men', 'audio_atent'].forEach(function (id) {
            var audio = stGetChatAudio(id);
            if (!audio) {
                return;
            }
            audio.muted = true;
            try {
                var playPromise = audio.play();
                if (playPromise && typeof playPromise.then === 'function') {
                    playPromise.then(function () {
                        audio.pause();
                        audio.currentTime = 0;
                        audio.muted = false;
                    }).catch(function () {
                        audio.muted = false;
                    });
                } else {
                    audio.muted = false;
                }
            } catch (errPrime) {
                audio.muted = false;
            }
        });
    }

    if (typeof document !== 'undefined') {
        document.addEventListener('click', primeChatAudio, { once: true, capture: true });
        document.addEventListener('keydown', primeChatAudio, { once: true, capture: true });
    }
})();

function chatMsgHasContent(value) {
    var raw = (value == null) ? '' : String(value);
    if (!raw) {
        return false;
    }
    var plain = raw.replace(/<[^>]+>/g, '').replace(/\u00a0/g, ' ').trim();
    return plain !== '' || /<img\b/i.test(raw);
}

function normalizeChatDisplayMsg(msg) {
    if (msg == null || msg === undefined) {
        return '';
    }
    var html = String(msg);
    if (!html) {
        return '';
    }
    if (!chatMsgHasContent(html)) {
        return '';
    }
    return html;
}

function renderChatParagrafo(elId, msg, chatId, how) {
    var html = normalizeChatDisplayMsg(msg);
    if (!html) {
        return false;
    }
    var el = document.getElementById(elId);
    if (!el) {
        return false;
    }
    if (/<img\s/i.test(html)) {
        $.ajax({
            url: 'staff/loadText.php',
            type: 'post',
            data: {
                msg: html,
                chat_id: chatId
            },
            beforeSend: function () {
                $('#' + elId).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
            }
        }).done(function (valor) {
            $('#' + elId).html(stSafeChatHtml(valor));
            if (how === 'other') {
                if (typeof safePlayMen === 'function') {
                    safePlayMen();
                } else if (typeof play_men === 'function') {
                    play_men();
                }
            }
        }).fail(function () {
            renderChatParagrafo(elId, html, chatId, how);
        });
        return true;
    }
    el.innerHTML = stSafeChatHtml(html);
    if (how === 'other') {
        if (typeof safePlayMen === 'function') {
            safePlayMen();
        } else if (typeof play_men === 'function') {
            play_men();
        }
    }
    return true;
}

function play_men() {
    if (typeof safePlayMen === 'function') {
        safePlayMen();
        return;
    }
    var men_audio = document.getElementById('audio_men');
    if (!men_audio) {
        return;
    }
    try {
        var playPromise = men_audio.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function () { });
        }
    } catch (errPlay) { }
}

/*
function showMessages(chatId, how, data, destinatario) {
    if($('#chat-content_' + chatId).length){
        var div_area = '#chat-content_' + chatId;
        var chat_content_txt = 'chat-content_' + chatId;
        var area_content = document.getElementById(chat_content_txt);

        var elMsg = nome_div();

        //console.log(elMsg);

        data = JSON.parse(data);
        //console.log(data);
        //console.log(chat_id);

        var load = '';

        //$('#dig_' + chatId).html('');

        //newMessage();

        if(how !=='sys'){
            var div = document.createElement('div');
            div.setAttribute('class', how);

            var img = document.createElement('img');
            img.setAttribute('src', data.img);

            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            var h5 = document.createElement('h5');
            h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.setAttribute('name', elMsg);
            p.setAttribute('id', elMsg);
            //p.textContent = data.msg;
            p.textContent = load;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent =  data.dataHora;

            div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(img);
            div.appendChild(div_txt);

            area_content.appendChild(div);


            var div = document.createElement('div');
            div.setAttribute('class', how);

            var msg = data.msg;
            //<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>
            //$('#'+elMsg).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
            var testLoad=0;
            loadText(msg, chat_id);
            function loadText(msg, chat_id){
                $.ajax({
                    url : "staff/loadText.php",
                    type : 'post',
                    data : {
                        msg, chat_id
                    },
                    beforeSend : function(){
                        $('#'+elMsg).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                    }
                })
                .done(function(valor){
                    $('#'+elMsg).html(stSafeChatHtml(valor));
                    testLoad=1;
                    if(how === 'other'){
                        play_men();
                    }
                })
                .fail(function(jqXHR, textStatus, valor){
                        //console.log(textStatus);
                        loadText(msg, chat_id);
                });
            }
            //console.log(testLoad);


            area_content.appendChild(div);
        } else {
            var div = document.createElement('div');
            div.setAttribute('class', how);


            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            //var h5 = document.createElement('h5');
            //h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.textContent = data.msg;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent = data.dataHora;

            //div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(div_txt);

            area_content.appendChild(div);
        }



        $(div_area).animate({scrollTop: 100000}, 'slow');


    }
}
*/
function showMessages(chatId, how, data, destinatario) {
    if ($('#chat-content_' + chatId).length) {
        var div_area = '#chat-content_' + chatId;
        var chat_content_txt = 'chat-content_' + chatId;
        var area_content = document.getElementById(chat_content_txt);

        var elMsg = nome_div();

        data = JSON.parse(data);
        $(div_area).find('.st-chat-waiting').remove();

        if (how !== 'sys') {
            var msg = data.msg;
            var div = document.createElement('div');
            div.setAttribute('class', how);

            var img = document.createElement('img');
            img.setAttribute('src', data.img);

            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            var h5 = document.createElement('h5');
            h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.setAttribute('name', elMsg);
            p.setAttribute('id', elMsg);

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent = data.dataHora;

            div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(img);
            div.appendChild(div_txt);

            area_content.appendChild(div);
            if (!renderChatParagrafo(elMsg, msg, chatId, how)) {
                div.remove();
            }
        } else {
            var div = document.createElement('div');
            div.setAttribute('class', how);


            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            //var h5 = document.createElement('h5');
            //h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.textContent = data.msg;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent = data.dataHora;

            //div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(div_txt);

            area_content.appendChild(div);
        }



        $(div_area).animate({ scrollTop: 100000 }, 'slow');


    }
}

/*
function showMessagesAdmin(chatId, how, data, destinatario, remetente) {
    var chat_id = chatId;
    if($('#chat-content_0_' + chatId).length){
        var div_area = '#chat-content_0_' + chatId;
        var chat_content_txt = 'chat-content_0_' + chatId;
        var area_content = document.getElementById(chat_content_txt);

        var elMsg = nome_div();

        //console.log(elMsg);

        data = JSON.parse(data);
        //console.log(data);

        var load = '';

        //$('#dig_' + chatId).html('');

        if(how !=='sys'){
            var div = document.createElement('div');
            div.setAttribute('class', how);

            var img = document.createElement('img');
            img.setAttribute('src', data.img);

            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            var h5 = document.createElement('h5');
            h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.setAttribute('name', elMsg);
            p.setAttribute('id', elMsg);
            //p.textContent = data.msg;
            p.textContent = load;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent =  data.dataHora;

            div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(img);
            div.appendChild(div_txt);

            area_content.appendChild(div);


            var div = document.createElement('div');
            div.setAttribute('class', how);

            var msg = data.msg;
            var testLoad=0;
            loadText(msg, chat_id);
            function loadText(msg, chat_id){
                //console.log('executa: ' + elMsg);
                $.ajax({
                    url : "staff/loadText.php",
                    type : 'post',
                    data : {
                        msg, chat_id
                    },
                    beforeSend : function(){
                        $('#'+elMsg).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                    }
                })
                .done(function(valor){
                    $('#'+elMsg).html(stSafeChatHtml(valor));

                })
                .fail(function(jqXHR, textStatus, valor){
                        //console.log(textStatus);
                        loadText(msg, chat_id);
                });
            }
            //console.log(testLoad);


            area_content.appendChild(div);
        } else {
            var div = document.createElement('div');
            div.setAttribute('class', how);


            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            //var h5 = document.createElement('h5');
            //h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.textContent = data.msg;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent = data.dataHora;

            //div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(div_txt);

            area_content.appendChild(div);
        }



        $(div_area).animate({scrollTop: 100000}, 'slow');


    }
}
*/

function showMessagesAdmin(chatId, how, data, destinatario, remetente) {
    var chat_id = chatId;
    var contentEl = stChatGetAdminContentEl(chatId);
    if (contentEl) {
        var div_area = contentEl.div;
        var chat_content_txt = contentEl.txt;
        var area_content = contentEl.area;

        var elMsg = nome_div();

        //console.log(elMsg);

        data = JSON.parse(data);
        //console.log(data);

        var load = '';

        //$('#dig_' + chatId).html('');

        if (how !== 'sys') {
            var div = document.createElement('div');
            div.setAttribute('class', how);

            var img = document.createElement('img');
            img.setAttribute('src', data.img);

            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            var h5 = document.createElement('h5');
            h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.setAttribute('name', elMsg);
            p.setAttribute('id', elMsg);
            //p.textContent = data.msg;
            //p.textContent = load;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent = data.dataHora;

            div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(img);
            div.appendChild(div_txt);

            area_content.appendChild(div);

            var msg = data.msg;

            if (msg.match(/<img /)) {
                loadText(msg, chat_id);
                function loadText(msg, chat_id) {
                    $.ajax({
                        url: "staff/loadText.php",
                        type: 'post',
                        data: {
                            msg, chat_id
                        },
                        beforeSend: function () {
                            $('#' + elMsg).html('<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>');
                        }
                    })
                        .done(function (valor) {
                            $('#' + elMsg).html(stSafeChatHtml(valor));
                            if (window.stDashAcomp && typeof window.stDashAcomp.scrollBottom === 'function') {
                                window.stDashAcomp.scrollBottom($(div_area), true);
                            }
                        })
                        .fail(function (jqXHR, textStatus, valor) {
                            loadText(msg, chat_id);
                        });
                }
            } else {
                var divMsg = '#' + elMsg;
                setTimeout(function () {
                    $(divMsg).html(stFormatChatPlainText(msg));
                    if (window.stDashAcomp && typeof window.stDashAcomp.scrollBottom === 'function') {
                        window.stDashAcomp.scrollBottom($(div_area), true);
                    }
                }, 0);
            }
        } else {
            var div = document.createElement('div');
            div.setAttribute('class', how);


            var div_txt = document.createElement('div');
            div_txt.setAttribute('class', 'text');

            //var h5 = document.createElement('h5');
            //h5.textContent = data.name;

            var p = document.createElement('div');
            p.setAttribute('class', 'paragrafo');
            p.textContent = data.msg;

            var p_dataHora = document.createElement('div');
            p_dataHora.setAttribute('class', 'dataHora');
            p_dataHora.textContent = data.dataHora;

            //div_txt.appendChild(h5);
            div_txt.appendChild(p);
            div_txt.appendChild(p_dataHora);

            div.appendChild(div_txt);

            area_content.appendChild(div);
        }



        if (window.stDashAcomp && typeof window.stDashAcomp.scrollBottom === 'function') {
            window.stDashAcomp.scrollBottom($(div_area), true);
        } else {
            $(div_area).animate({ scrollTop: 100000 }, 'slow');
        }


    }
}



function showMessagesSys(chatId, how, data, destinatario, indice) {
    if ($('#chat-content_' + chatId).length) {
        var div_area = '#chat-content_' + chatId;
        var chat_content_txt = 'chat-content_' + chatId;
        var area_content = document.getElementById(chat_content_txt);
        var $indEl = document.getElementById('indice_' + chatId);
        var indiceTab = ($indEl && $indEl.value) ? parseInt($indEl.value, 10) : (parseInt(indice, 10) || stChatResolveIndiceForChat(chatId));

        //console.log('Ind. 0.0: ' + indice);
        data = JSON.parse(data);
        //console.log(data);

        if (stChatHasSysMsg(chatId, data.msg)) {
            return;
        }

        var div = document.createElement('div');
        div.setAttribute('class', how);


        var div_txt = document.createElement('div');
        div_txt.setAttribute('class', 'text');

        //var h5 = document.createElement('h5');
        //h5.textContent = data.name;

        var p = document.createElement('div');
        p.setAttribute('class', 'paragrafo');
        p.textContent = data.msg;

        var p_dataHora = document.createElement('div');
        p_dataHora.setAttribute('class', 'dataHora');
        p_dataHora.textContent = data.dataHora;

        //div_txt.appendChild(h5);
        div_txt.appendChild(p);
        div_txt.appendChild(p_dataHora);

        div.appendChild(div_txt);

        area_content.appendChild(div);
        $(div_area).animate({ scrollTop: 100000 }, 'slow');

        if (typeof stChatTypingHide === 'function') {
            stChatTypingHide(chatId);
        }
        if (data.flagSys == 'false') {
            if (typeof finder !== "undefined") { clearInterval(finder); }
            if (typeof stChatMarkEnded === 'function') {
                stChatMarkEnded(String(chatId), data.tokenChat || '');
            }
            stChatLockComposerOnEnd(chatId);
            if (window.stChatPosOpened && window.stChatPosOpened[String(chatId)]) {
                return;
            }
            if (typeof loadPos === 'function') {
                setTimeout(function () { loadPos(indiceTab, chatId); }, 350);
            } else if (typeof stChatSolOpenClassModal === 'function') {
                setTimeout(function () { stChatSolOpenClassModal(chatId); }, 350);
            }

        }

        if (data.flagSys == 'transfer') {
            stChatLockComposerOnEnd(chatId);
            if (document.getElementById('trasnferir_' + chatId)) {
                if (typeof loadPos === 'function') {
                    setTimeout(function () { loadPos(indiceTab, chatId); }, 500);
                }
            } else {
                setTimeout(function () { actionPage('dash-cha', 'idx'); }, 500);
            }
        }

        if (data.flagSys == 'Atent') {
            play_atent(data.msg);
        }

        if (data.msg && (data.msg.indexOf('entrou no chat') !== -1 || data.msg.indexOf('voltou para o chat') !== -1)) {
            if (typeof stChatIsEnded === 'function' && stChatIsEnded(String(chatId), data.tokenChat || '')) {
                return;
            }
            setTimeout(function () {
                if (typeof stChatTryStartAtendimento === 'function') {
                    stChatTryStartAtendimento(String(chatId), '');
                }
            }, 400);
        }


    }
}

function showMessagesSysAdmin(chatId, how, data, indice) {
    var contentEl = stChatGetAdminContentEl(chatId);
    if (contentEl) {
        var div_area = contentEl.div;
        var chat_content_txt = contentEl.txt;
        var area_content = contentEl.area;
        var indiceTab = stChatResolveIndiceForChat(chatId) || parseInt(indice, 10) || 0;

        data = JSON.parse(data);

        if ($(div_area).find('.sys .paragrafo').filter(function () {
            return $(this).text() === data.msg;
        }).length) {
            return;
        }

        var div = document.createElement('div');
        div.setAttribute('class', how);


        var div_txt = document.createElement('div');
        div_txt.setAttribute('class', 'text');

        //var h5 = document.createElement('h5');
        //h5.textContent = data.name;

        var p = document.createElement('div');
        p.setAttribute('class', 'paragrafo');
        p.textContent = data.msg;

        var p_dataHora = document.createElement('div');
        p_dataHora.setAttribute('class', 'dataHora');
        p_dataHora.textContent = data.dataHora;

        //div_txt.appendChild(h5);
        div_txt.appendChild(p);
        div_txt.appendChild(p_dataHora);

        div.appendChild(div_txt);

        area_content.appendChild(div);
        $(div_area).animate({ scrollTop: 100000 }, 'slow');
        if (typeof stChatTypingHide === 'function') {
            stChatTypingHide(chatId);
        }

        if (data.flagSys == 'false') {
            if (typeof stChatMarkEnded === 'function') {
                stChatMarkEnded(String(chatId), data.tokenChat || '');
            }
            stChatLockComposerOnEnd(chatId);
            if (window.stChatPosOpened && window.stChatPosOpened[String(chatId)]) {
                return;
            }
            if (typeof loadPos === 'function') {
                setTimeout(function () { loadPos(indiceTab, chatId); }, 500);
            }
        }

        if (data.flagSys == 'transfer') {
            if (typeof loadPos === 'function') {
                setTimeout(function () { loadPos(indiceTab, chatId); }, 500);
            }

        }

        if (data.msg && (data.msg.indexOf('entrou no chat') !== -1 || data.msg.indexOf('voltou para o chat') !== -1)) {
            if (typeof stChatIsEnded === 'function' && stChatIsEnded(String(chatId), data.tokenChat || '')) {
                return;
            }
            setTimeout(function () {
                if (typeof stChatTryStartAtendimento === 'function') {
                    stChatTryStartAtendimento(String(chatId), '');
                }
            }, 400);
        }


    }
}


function showDig(chatId, data) {
    stChatTypingShow(chatId, data, false);
}

function showDigAdmin(chatId, data) {
    stChatTypingShow(chatId, data, true);
}



