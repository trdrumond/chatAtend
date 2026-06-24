/**

 * Backoffice — distribuição de fila e abertura de chat.

 * Um único intervalo JS faz o polling; PHP só dispara actionPageChat quando há fila.

 */

(function (window, $) {

    'use strict';



    if (!$) {

        return;

    }



    var pollTimers = {};

    var pageInited = false;

    var bootedIndices = {};

    var tabFlight = {};

    var POLL_MS = 2500;

    var BKO_OPEN_RETRY_MS = 1500;

    var BKO_WAIT_READY_MS = 2000;

    var BKO_MAX_WAIT_RETRIES = 50;

    var bkoChatOpen = {};
    var bkoPauseReq = {};

    function tabKey(indice) {
        return String(parseInt(indice, 10) || 1);
    }

    function getFlight(indice) {
        var k = tabKey(indice);
        if (!tabFlight[k]) {
            tabFlight[k] = { bko: false, bkoForce: false, chat: false, pos: false, openingProtocolo: '', filaPanel: false };
        }
        return tabFlight[k];
    }

    window.stBkoTabRegistry = window.stBkoTabRegistry || { byChat: {}, byIndice: {} };

    window.stBkoRegisterTab = function (indice, chatId, protocolo) {
        indice = tabKey(indice);
        chatId = String(chatId || '');
        window.stBkoTabRegistry.byIndice[indice] = {
            chatId: chatId,
            protocolo: protocolo || ''
        };
        if (chatId) {
            window.stBkoTabRegistry.byChat[chatId] = indice;
        }
    };

    window.stBkoUnregisterTab = function (indice) {
        indice = tabKey(indice);
        var row = window.stBkoTabRegistry.byIndice[indice];
        if (row && row.chatId) {
            delete window.stBkoTabRegistry.byChat[row.chatId];
        }
        delete window.stBkoTabRegistry.byIndice[indice];
    };

    window.stBkoGetIndiceByChatId = function (chatId) {
        chatId = String(chatId || '');
        if (window.stBkoTabRegistry.byChat[chatId]) {
            return parseInt(window.stBkoTabRegistry.byChat[chatId], 10) || 0;
        }
        var $inp = $('#indice_' + chatId);
        if ($inp.length) {
            return parseInt($inp.val(), 10) || 0;
        }
        return 0;
    };

    window.stBkoCloseTab = function (indice) {
        indice = parseInt(indice, 10) || 1;
        stopPoll(indice);
        clearChatTimers(indice);
        delete bootedIndices[indice];
        window.stBkoUnregisterTab(indice);
        var f = getFlight(indice);
        f.bko = false;
        f.bkoForce = false;
        f.chat = false;
        f.openingProtocolo = '';
        f.filaPanel = false;
        clearBkoOpenWait(indice);
        if (bkoChatOpen[tabKey(indice)]) {
            bkoChatOpen[tabKey(indice)].inFlight = false;
            bkoChatOpen[tabKey(indice)].protocolo = '';
            bkoChatOpen[tabKey(indice)].waitRetries = 0;
        }
    };

    function getBkoOpen(indice) {
        var k = tabKey(indice);
        if (!bkoChatOpen[k]) {
            bkoChatOpen[k] = { inFlight: false, xhr: null, waitTimer: null, waitRetries: 0, protocolo: '' };
        }
        return bkoChatOpen[k];
    }

    function clearBkoOpenWait(indice) {
        var open = getBkoOpen(indice);
        if (open.waitTimer) {
            clearTimeout(open.waitTimer);
            open.waitTimer = null;
        }
    }

    function resetBkoOpen(indice) {
        var open = getBkoOpen(indice);
        clearBkoOpenWait(indice);
        if (open.xhr && open.xhr.readyState !== 0 && open.xhr.readyState !== 4) {
            try {
                open.xhr.abort();
            } catch (e) {}
        }
        open.inFlight = false;
        open.xhr = null;
        open.waitRetries = 0;
        open.protocolo = '';
        var f = getFlight(indice);
        f.chat = false;
        f.openingProtocolo = '';
    }

    function bkoLoaderHtml(message) {
        message = message || 'Abrindo chat...';
        if (window.stChatOpen && typeof window.stChatOpen.loaderHtml === 'function') {
            return window.stChatOpen.loaderHtml('Abrindo chat', message);
        }
        return '<div id="load_gif"><img src="img/loading.gif" width="120" alt=""><br>' + message + '</div>';
    }

    function stBkoResolveProtocolo(indice, hint) {
        indice = parseInt(indice, 10) || 1;
        hint = String(hint || '').trim();
        if (hint) {
            return hint;
        }
        var cfg = parseCfg();
        var list = cfg.activeChats || [];
        var i;
        for (i = 0; i < list.length; i++) {
            if (parseInt(list[i].indice, 10) === indice && list[i].protocolo) {
                return String(list[i].protocolo);
            }
        }
        if (list.length === 1 && list[0].protocolo) {
            return String(list[0].protocolo);
        }
        if (indice === 1 && list.length > 0 && list[0].protocolo) {
            return String(list[0].protocolo);
        }
        return '';
    }

    function stBkoHandlePollHtml(indice, html) {
        if (!html || html.indexOf('data-st-bko-active') === -1) {
            return false;
        }
        var proto = '';
        var match = html.match(/data-protocolo=["']([^"']+)["']/i);
        if (match && match[1]) {
            proto = match[1];
        }
        proto = stBkoResolveProtocolo(indice, proto);
        if (proto) {
            openChat(indice, proto);
        }
        return true;
    }

    function stBkoPulseWaitingTabs() {
        if (!$('#content-bko').length) {
            return;
        }
        var now = Date.now();
        window._stBkoPulseLast = window._stBkoPulseLast || 0;
        if (now - window._stBkoPulseLast < 4500) {
            return;
        }
        window._stBkoPulseLast = now;

        var cfg = parseCfg();
        var maxTabs = parseInt(window.qtdMax, 10) || 3;
        var i;
        for (i = 1; i <= maxTabs; i++) {
            if (!$('#title-' + i).length) {
                continue;
            }
            if (divBlocksPoll(i)) {
                continue;
            }
            if (!$('#feed-' + i).length && !$('#load-' + i).length) {
                continue;
            }
            var f = getFlight(i);
            if (f.chat || f.bko || f.bkoForce || getBkoOpen(i).inFlight) {
                continue;
            }
            var idFila = cfg.filaId || 0;
            var dateDisp = $('#load-' + i).attr('data-date-disp') || '';
            var filaAttr = parseInt($('#load-' + i).attr('data-fila-id'), 10);
            if (!isNaN(filaAttr) && filaAttr > 0) {
                idFila = filaAttr;
            }
            window.loadBko_force(i, idFila, dateDisp);
        }
    }



    function resetInit() {

        pageInited = false;

        bootedIndices = {};

    }



    function parseCfg() {

        var fallback = window.stBkoCfg || {};
        var $root = $('#content-bko');

        if (!$root.length) {
            return {
                userId: parseInt(fallback.userId, 10) || 0,
                filaId: parseInt(fallback.filaId, 10) || 0,
                contratoId: parseInt(fallback.contratoId, 10) || 0,
                hasActiveAte: !!fallback.hasActiveAte,
                activeChats: fallback.activeChats || []
            };
        }

        return {

            userId: parseInt($root.attr('data-user-id'), 10) || parseInt(fallback.userId, 10) || 0,

            filaId: parseInt($root.attr('data-fila-id'), 10) || parseInt(fallback.filaId, 10) || 0,

            contratoId: parseInt($root.attr('data-contrato-id'), 10) || parseInt(fallback.contratoId, 10) || 0,

            hasActiveAte: $root.attr('data-has-active') === '1' || !!fallback.hasActiveAte,

            activeChats: fallback.activeChats || []

        };

    }



    function divHasChat(indice) {

        return $('#div-' + indice).find('.st-chat-workspace--bko, .st-chat-bko-body, .st-chat-bko-header').length > 0;

    }

    function divHasPosForm(indice) {

        return $('#div-' + indice).find('.st-pos-page').length > 0;

    }

    function divBlocksPoll(indice) {

        return divHasChat(indice) || divHasPosForm(indice);

    }



    function clearLegacyTimers() {

        if (typeof window.timeAtend !== 'undefined') {

            clearTimeout(window.timeAtend);

            window.timeAtend = undefined;

        }

        if (typeof window.timeEspera !== 'undefined') {

            clearTimeout(window.timeEspera);

            window.timeEspera = undefined;

        }

    }



    function injectScripts($root) {

        $root.find('script').each(function () {

            if (this.src) {

                return;

            }

            var code = this.textContent || this.innerText || '';

            if (code.trim()) {

                try {

                    $.globalEval(code);

                } catch (err) {

                    console.error('st-bko script:', err);

                }

            }

        });

    }

    function stBkoGetChatMsgHtml(chatId, indice) {
        chatId = String(chatId || '');
        indice = parseInt(indice, 10) || 1;
        var readFn = window['stChatReadComposer_' + chatId];
        if (typeof readFn === 'function') {
            return readFn().html || '';
        }
        var html = '';
        var $ta = $('#div_message_' + chatId + ' textarea#msg_' + chatId);
        if (!$ta.length) {
            $ta = $('#msg_' + chatId).first();
        }
        if ($ta.length) {
            html = $ta.val() || '';
        }
        if (!String(html).replace(/<[^>]*>/g, '').trim() && typeof tinymce !== 'undefined') {
            var ed = tinymce.get('msg_' + chatId);
            if (ed && !ed.removed && (typeof ed.initialized === 'undefined' || ed.initialized)) {
                try {
                    if (ed.getBody && ed.getBody()) {
                        ed.save();
                        html = ed.getContent({ save: true }) || ed.getContent() || '';
                    }
                } catch (errEd) {
                    html = '';
                }
            }
        }
        return html;
    }

    function stBkoSendMessageFallback(chatId, indice) {
        chatId = String(chatId || '');
        indice = parseInt(indice, 10) || 1;
        if (typeof chatMsg !== 'function') {
            return false;
        }
        var plain = stBkoGetChatMsgHtml(chatId, indice);
        if (!plain || !String(plain).replace(/<[^>]*>/g, '').trim()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Mensagem vazia',
                    text: 'Digite uma mensagem antes de enviar.'
                });
            }
            return false;
        }
        var $ws = $('#div-' + indice).find('.st-chat-workspace--bko').first();
        var destinatario = $('#id_user_destinatario_' + chatId).val();
        var contrato = parseInt($ws.attr('data-contrato-id'), 10) || (window.stBkoCfg && window.stBkoCfg.contratoId) || 0;
        var token = $ws.attr('data-token-chat') || '';
        if (typeof conn === 'undefined' || !conn || conn.readyState !== 1) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Conexão', text: 'Aguardando conexão com o servidor. Tente novamente em instantes.' });
            }
            return false;
        }
        $('#message_' + chatId).val(stBkoGetChatMsgHtml(chatId, indice));
        try {
            chatMsg(chatId, destinatario, contrato, token);
            if (typeof window['setChatInput_' + chatId] === 'function') {
                window['setChatInput_' + chatId]('');
            } else {
                $('#msg_' + chatId).val('');
            }
            $('#select_in_chat_' + chatId).val('');
            return true;
        } catch (err) {
            console.error('stBkoSendMessageFallback:', err);
            return false;
        }
    }

    function stBkoInvokeSend(chatId, indice) {
        chatId = String(chatId || '');
        indice = parseInt(indice, 10) || 1;
        var fn = window['sendChatMessage_' + chatId];
        if (typeof fn === 'function') {
            try {
                return fn() === true;
            } catch (err) {
                console.error('stBkoInvokeSend:', err);
            }
        }
        return stBkoSendMessageFallback(chatId, indice);
    }

    function stBkoBindTinyEnter(chatId, indice) {
        return false;
    }

    function stBkoBindChatSend(chatId, indice) {
        // Enter e clique são tratados em chat_ind.php (stChatHandleEnterSend / sendChatMessage).
    }

    function stBkoBindChatSendRetry(chatId, indice, retry) {
        retry = retry || 0;
        if ($('#btn1_' + chatId).length) {
            stBkoBindChatSend(chatId, indice);
        }
        if (retry < 20) {
            setTimeout(function () {
                stBkoBindChatSendRetry(chatId, indice, retry + 1);
            }, 300);
        }
    }

    function stBkoExtractChatId(html) {
        var m = String(html || '').match(/chatId_(\d+)\s*=\s*(\d+)/);
        return m ? m[2] : '';
    }



    function injectHtmlWithScripts($target, html) {

        $target.html(html);

        injectScripts($target);

    }



    function showPanel() {

        $('#content-bko').show();

        $('#load_conn').hide();

    }



    function stopPoll(indice) {

        indice = parseInt(indice, 10) || 1;

        if (pollTimers[indice]) {

            clearInterval(pollTimers[indice]);

            pollTimers[indice] = null;

        }

    }



    function stBkoFormatTa(seconds) {
        seconds = Math.max(0, parseInt(seconds, 10) || 0);
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function stBkoParseHoraInicio(str) {
        if (!str) {
            return Date.now();
        }
        var normalized = String(str).trim().replace(' ', 'T');
        var ts = Date.parse(normalized);
        return isNaN(ts) ? Date.now() : ts;
    }

    function stBkoStopTa(chatId) {
        chatId = String(chatId || '');
        if (!chatId) {
            return;
        }
        window.stBkoTaTimers = window.stBkoTaTimers || {};
        var key = 'c' + chatId;
        if (window.stBkoTaTimers[key]) {
            clearInterval(window.stBkoTaTimers[key]);
            window.stBkoTaTimers[key] = null;
        }
    }

    function stBkoStopAllTa() {
        window.stBkoTaTimers = window.stBkoTaTimers || {};
        Object.keys(window.stBkoTaTimers).forEach(function (key) {
            if (window.stBkoTaTimers[key]) {
                clearInterval(window.stBkoTaTimers[key]);
            }
        });
        window.stBkoTaTimers = {};
        window.stBkoTaByIndice = {};
    }

    function stBkoStartTa(chatId, horaInicio, indice, taElapsed) {
        chatId = String(chatId || '');
        indice = parseInt(indice, 10) || 1;
        if (!chatId) {
            return;
        }
        window.stBkoTaByIndice = window.stBkoTaByIndice || {};
        var prevChat = window.stBkoTaByIndice[indice];
        if (prevChat && prevChat !== chatId) {
            stBkoStopTa(prevChat);
        }
        window.stBkoTaByIndice[indice] = chatId;
        stBkoStopTa(chatId);

        var elapsedSec = parseInt(taElapsed, 10);
        if (isNaN(elapsedSec) || elapsedSec < 0) {
            elapsedSec = Math.max(0, Math.floor((Date.now() - stBkoParseHoraInicio(horaInicio)) / 1000));
        }
        var startMs = Date.now() - elapsedSec * 1000;
        var $div = $('#div_ta_' + chatId);
        if (!$div.length) {
            return;
        }

        function tick() {
            if (!$div.closest('body').length) {
                stBkoStopTa(chatId);
                return;
            }
            var sec = Math.floor((Date.now() - startMs) / 1000);
            $div.html("<i class='far fa-clock'></i> TA: " + stBkoFormatTa(sec));
        }

        tick();
        window.stBkoTaTimers = window.stBkoTaTimers || {};
        window.stBkoTaTimers['c' + chatId] = setInterval(tick, 1000);
    }

    function stBkoStopFilaPoll(indice) {
        indice = parseInt(indice, 10) || 1;
        window.stBkoFilaTimers = window.stBkoFilaTimers || {};
        if (window.stBkoFilaTimers[indice]) {
            clearInterval(window.stBkoFilaTimers[indice]);
            window.stBkoFilaTimers[indice] = null;
        }
    }

    function stBkoStopAllFilaPolls() {
        window.stBkoFilaTimers = window.stBkoFilaTimers || {};
        Object.keys(window.stBkoFilaTimers).forEach(function (key) {
            if (window.stBkoFilaTimers[key]) {
                clearInterval(window.stBkoFilaTimers[key]);
            }
        });
        window.stBkoFilaTimers = {};
    }

    function stBkoStartFilaPoll(chatId, filaId, indice) {
        indice = parseInt(indice, 10) || 1;
        stBkoStopFilaPoll(indice);
        window.stBkoFilaTimers = window.stBkoFilaTimers || {};
        window.stBkoFilaTimers[indice] = setInterval(function () {
            if (!$('#fila_ativa_' + chatId).length) {
                stBkoStopFilaPoll(indice);
                return;
            }
            $.post('staff/load_fila_ativa.php', { fila: filaId }, function (valor) {
                $('#fila_ativa_' + chatId).html(valor);
            });
        }, 30000);
        $.post('staff/load_fila_ativa.php', { fila: filaId }, function (valor) {
            $('#fila_ativa_' + chatId).html(valor);
        });
    }

    function clearChatTimers(indice) {
        indice = parseInt(indice, 10) || 1;
        window.stBkoTaByIndice = window.stBkoTaByIndice || {};
        if (window.stBkoTaByIndice[indice]) {
            stBkoStopTa(window.stBkoTaByIndice[indice]);
            delete window.stBkoTaByIndice[indice];
        }
        stBkoStopFilaPoll(indice);
    }

    function stopAllPolls() {

        Object.keys(pollTimers).forEach(function (key) {

            stopPoll(key);

        });

        bootedIndices = {};

        stBkoStopAllTa();
        stBkoStopAllFilaPolls();

        Object.keys(bkoChatOpen).forEach(function (key) {
            resetBkoOpen(parseInt(key, 10) || 1);
        });

        tabFlight = {};

    }

    function startPoll(indice, idFila, dateDisp) {

        indice = parseInt(indice, 10) || 1;

        if (pollTimers[indice]) {

            return;

        }

        pollTimers[indice] = setInterval(function () {

            if (divBlocksPoll(indice) || getFlight(indice).pos || getFlight(indice).chat || getBkoOpen(indice).inFlight) {

                stopPoll(indice);

                return;

            }

            window.loadBko_force(indice, idFila, dateDisp);

        }, POLL_MS);

    }



    function stBkoWaitShellReady(indice) {
        return $('#load-' + indice).length > 0 && $('#feed-' + indice).length > 0;
    }

    function showWaitPanel(indice) {

        indice = parseInt(indice, 10) || 1;

        var $load = $('#load-' + indice);

        if ($load.length) {

            $load.show();

            if (stBkoWaitShellReady(indice)) {

                bootWait(indice, false);

            }

            return;

        }

        loadFilaBko(indice);

    }



    function openChat(indice, protocolo) {

        indice = parseInt(indice, 10) || 1;

        protocolo = String(protocolo || '');

        if (!protocolo) {

            return;

        }

        if (divHasChat(indice)) {

            stopPoll(indice);

            return;

        }

        var flight = getFlight(indice);

        var open = getBkoOpen(indice);

        if (open.inFlight && open.protocolo === protocolo) {

            return;

        }

        if (flight.chat && flight.openingProtocolo === protocolo && open.inFlight) {

            return;

        }



        stopPoll(indice);

        clearChatTimers(indice);

        clearBkoOpenWait(indice);



        flight.chat = true;

        flight.openingProtocolo = protocolo;

        open.inFlight = true;

        open.protocolo = protocolo;

        open.waitRetries = 0;



        if ($('#title-' + indice).hasClass('active-tab')) {

            window.stBkoIndiceAtivo = indice;

        }



        var $div = $('#div-' + indice);

        $div.html(bkoLoaderHtml('Abrindo chat...'));



        var failBack = function () {

            resetBkoOpen(indice);

            if (divHasChat(indice)) {

                return;

            }

            showWaitPanel(indice);

            var cfg = parseCfg();

            var idFila = cfg.filaId || 0;

            var dateDisp = $('#load-' + indice).attr('data-date-disp') || '';

            var filaAttr = parseInt($('#load-' + indice).attr('data-fila-id'), 10);

            if (!isNaN(filaAttr) && filaAttr > 0) {

                idFila = filaAttr;

            }

            startPoll(indice, idFila, dateDisp);

        };



        var safetyTimer = setTimeout(function () {

            resetBkoOpen(indice);

            failBack();

        }, 35000);



        var finishChatHtml = function (html) {

            clearTimeout(safetyTimer);

            if (!html || html.indexOf('st-chat-workspace--bko') === -1) {

                if (html && html.indexOf('data-st-bko-wait') !== -1) {

                    open.waitRetries += 1;

                    if (open.waitRetries < BKO_MAX_WAIT_RETRIES) {

                        $div.html(bkoLoaderHtml('Preparando sala de chat...'));

                        open.waitTimer = setTimeout(pollReady, BKO_WAIT_READY_MS);

                        return;

                    }

                }

                failBack();

                return;

            }

            $div.html(html);

            injectScripts($div);

            setTimeout(function () {

                if (typeof window.inChat === 'function') {

                    window.inChat();

                }

            }, 80);

            var boundChatId = stBkoExtractChatId(html) || ($div.find('.st-chat-workspace--bko').attr('data-chat-id') || '');

            if (boundChatId) {

                stBkoBindChatSendRetry(boundChatId, indice, 0);

                if (typeof window.stBkoRegisterTab === 'function') {

                    window.stBkoRegisterTab(indice, boundChatId, protocolo);

                }

            }

            resetBkoOpen(indice);

            if (!divHasChat(indice)) {

                failBack();

            } else {

                stopPoll(indice);

                bootedIndices[indice] = true;

            }

        };



        var loadChatBkoHtml = function (idFilaChat) {

            var postData = { indice: indice, protocolo: protocolo };

            if (idFilaChat) {

                postData.id_fila_chat = idFilaChat;

            }

            open.xhr = $.ajax({

                url: 'staff/chat-bko.php',

                type: 'POST',

                data: postData,

                timeout: 30000

            }).done(function (html) {

                open.xhr = null;

                finishChatHtml(html);

            }).fail(function () {

                open.xhr = null;

                open.waitRetries += 1;

                if (open.waitRetries < BKO_MAX_WAIT_RETRIES) {

                    open.waitTimer = setTimeout(pollReady, BKO_OPEN_RETRY_MS);

                } else {

                    clearTimeout(safetyTimer);

                    failBack();

                }

            });

        };



        var pollReady = function () {

            clearBkoOpenWait(indice);

            var readyData = { indice: indice, protocolo: protocolo };

            var cfgReady = parseCfg();

            (cfgReady.activeChats || []).forEach(function (item) {

                if (parseInt(item.indice, 10) === indice && item.id_fila_chat) {

                    readyData.id_fila_chat = item.id_fila_chat;

                }

            });

            open.xhr = $.ajax({

                url: 'staff/chat_bko_ready.php',

                type: 'POST',

                data: readyData,

                dataType: 'json',

                timeout: 30000

            }).done(function (status) {

                open.xhr = null;

                if (!status || typeof status !== 'object') {

                    open.waitRetries += 1;

                    if (open.waitRetries < BKO_MAX_WAIT_RETRIES) {

                        open.waitTimer = setTimeout(pollReady, BKO_OPEN_RETRY_MS);

                    } else {

                        clearTimeout(safetyTimer);

                        failBack();

                    }

                    return;

                }

                if (status.state === 'closed' || status.state === 'no_fila' || status.state === 'taken') {

                    clearTimeout(safetyTimer);

                    failBack();

                    return;

                }

                if (!status.ready) {

                    open.waitRetries += 1;

                    var sub = status.message || 'Preparando sala de chat...';

                    $div.html(bkoLoaderHtml(sub));

                    if (open.waitRetries >= BKO_MAX_WAIT_RETRIES) {

                        clearTimeout(safetyTimer);

                        failBack();

                        return;

                    }

                    open.waitTimer = setTimeout(pollReady, BKO_WAIT_READY_MS);

                    return;

                }

                if (status.protocolo) {

                    protocolo = String(status.protocolo);

                    flight.openingProtocolo = protocolo;

                    open.protocolo = protocolo;

                }

                loadChatBkoHtml(status.id_fila_chat || 0);

            }).fail(function () {

                open.xhr = null;

                open.waitRetries += 1;

                if (open.waitRetries < BKO_MAX_WAIT_RETRIES) {

                    open.waitTimer = setTimeout(pollReady, BKO_OPEN_RETRY_MS);

                } else {

                    clearTimeout(safetyTimer);

                    failBack();

                }

            });

        };



        pollReady();

    }



    function loadBko(indice, idFila, date_disp) {

        indice = parseInt(indice, 10) || 1;

        if (divBlocksPoll(indice) || getFlight(indice).pos || getFlight(indice).chat || getBkoOpen(indice).inFlight) {

            stopPoll(indice);

            return;

        }

        if (getFlight(indice).bko) {

            return;

        }

        if (!stBkoWaitShellReady(indice)) {

            if (!getFlight(indice).filaPanel) {

                loadFilaBko(indice);

            }

            return;

        }

        getFlight(indice).bko = true;

        $.post('staff/load_chat_bko.php', {

            indice: indice,

            idFila: idFila,

            date_disp: date_disp

        }, function (html) {

            getFlight(indice).bko = false;

            if (divHasChat(indice)) {

                return;

            }

            if (stBkoHandlePollHtml(indice, html)) {

                return;

            }

            if (getFlight(indice).chat) {

                return;

            }

            injectHtmlWithScripts($('#feed-' + indice), html);

        }).fail(function () {

            getFlight(indice).bko = false;

        });

    }



    function loadBko_force(indice, idFila, date_disp) {

        indice = parseInt(indice, 10) || 1;

        if (divBlocksPoll(indice) || getFlight(indice).pos || getFlight(indice).chat || getBkoOpen(indice).inFlight) {

            stopPoll(indice);

            return;

        }

        if (getFlight(indice).bkoForce || getFlight(indice).bko) {

            return;

        }

        if (!$('#feed-' + indice).length) {

            return;

        }

        getFlight(indice).bkoForce = true;

        $.post('staff/load_chat_bko_forcado.php', {

            indice: indice,

            idFila: idFila,

            date_disp: date_disp

        }, function (html) {

            getFlight(indice).bkoForce = false;

            if (divHasChat(indice)) {

                return;

            }

            if (stBkoHandlePollHtml(indice, html)) {

                return;

            }

            if (getFlight(indice).chat) {

                return;

            }

            injectHtmlWithScripts($('#feed-' + indice), html);

        }).fail(function () {

            getFlight(indice).bkoForce = false;

        });

    }



    function loadFilaBko(indice) {

        indice = parseInt(indice, 10) || 1;

        if (divHasChat(indice)) {

            return;

        }

        if (stBkoWaitShellReady(indice)) {

            bootWait(indice, false);

            return;

        }

        var flight = getFlight(indice);

        if (flight.filaPanel) {

            return;

        }

        flight.filaPanel = true;

        $.post('staff/load_fila_bko.php', { indice: indice }, function (html) {

            $('#div-' + indice).html(html);

            showPanel();

            bootWait(indice, false);

        }).fail(function () {

            bootWait(indice, false);

        }).always(function () {

            flight.filaPanel = false;

        });

    }



    function stBkoEnsureTabShell(indice) {

        indice = parseInt(indice, 10) || 1;

        var bloco = document.getElementById('bloco-bko');

        var principal = document.getElementById('principal');

        if (!bloco || !principal) {

            return false;

        }

        var titleId = 'title-' + indice;

        if (!document.getElementById(titleId)) {

            var tab = document.createElement('span');

            tab.id = titleId;

            tab.className = 'tab active-tab';

            tab.setAttribute('onclick', 'selAba(' + indice + ');');

            tab.textContent = 'Aguardando...';

            bloco.appendChild(tab);

        }

        var divId = 'div-' + indice;

        if (!document.getElementById(divId)) {

            var panel = document.createElement('div');

            panel.id = divId;

            panel.className = 'div show';

            principal.appendChild(panel);

        }

        window.stBkoIndiceAtivo = indice;

        if (typeof window.selAba === 'function') {

            window.selAba(indice);

        }

        $('#btn-add-tab').prop('disabled', false);

        return true;

    }



    function stBkoReturnToQueue(indice) {

        indice = parseInt(indice, 10) || 1;

        if (!$('#content-bko').length) {

            location.reload();

            return;

        }

        stopPoll(indice);

        delete bootedIndices[indice];

        var f = getFlight(indice);

        f.bko = false;

        f.bkoForce = false;

        f.chat = false;

        f.openingProtocolo = '';

        f.filaPanel = false;

        if (!stBkoEnsureTabShell(indice)) {

            location.reload();

            return;

        }

        showPanel();

        if (window.stBkoCfg) {

            window.stBkoCfg.hasActiveAte = false;

            window.stBkoCfg.activeChats = [];

        }

        $('#div-' + indice).empty();

        loadFilaBko(indice);

    }



    function bootWait(indice, force) {

        indice = parseInt(indice, 10) || 1;

        if (divHasChat(indice)) {

            return;

        }

        if (!force && bootedIndices[indice] && pollTimers[indice]) {

            return;

        }

        if (!force && bootedIndices[indice] && stBkoWaitShellReady(indice)) {

            if (!pollTimers[indice]) {

                var cfgIdle = parseCfg();

                var idFilaIdle = cfgIdle.filaId || 0;

                var dateDispIdle = $('#load-' + indice).attr('data-date-disp') || '';

                var filaAttrIdle = parseInt($('#load-' + indice).attr('data-fila-id'), 10);

                if (!isNaN(filaAttrIdle) && filaAttrIdle > 0) {

                    idFilaIdle = filaAttrIdle;

                }

                startPoll(indice, idFilaIdle, dateDispIdle);

            }

            return;

        }

        if (!force && bootedIndices[indice]) {

            return;

        }

        bootedIndices[indice] = true;



        var cfg = parseCfg();

        var idFila = cfg.filaId || 0;

        var dateDisp = $('#load-' + indice).attr('data-date-disp') || '';



        var filaAttr = parseInt($('#load-' + indice).attr('data-fila-id'), 10);

        if (!isNaN(filaAttr) && filaAttr > 0) {

            idFila = filaAttr;

        }



        if ($('#title-' + indice).hasClass('active-tab')) {
            window.stBkoIndiceAtivo = indice;
        }

        if (typeof window.stBkoQtdSpans === 'function') {

            stBkoQtdSpans(indice);

        }



        window.loadBko(indice, idFila, dateDisp);

        startPoll(indice, idFila, dateDisp);

    }



    function loadAtendAndamento(indice, protocolo) {

        openChat(indice, protocolo);

    }



    function loadChatIn() {

        if (!$('#content-bko').length && !window.stBkoCfg) {

            return;

        }

        var cfg = parseCfg();

        if (cfg.hasActiveAte && cfg.activeChats && cfg.activeChats.length) {

            cfg.activeChats.forEach(function (item, idx) {

                setTimeout(function () {

                    var tabInd = parseInt(item.indice, 10) || 1;

                    if (divHasChat(tabInd) || getBkoOpen(tabInd).inFlight) {

                        return;

                    }

                    var proto = stBkoResolveProtocolo(tabInd, item.protocolo);

                    if (proto) {

                        openChat(tabInd, proto);

                    }

                }, idx * 300);

            });

            return;

        }

        var indice = window.stBkoIndiceAtivo || 1;

        if (divHasChat(indice) || getFlight(indice).chat || getBkoOpen(indice).inFlight) {

            return;

        }

        if ($('#load-' + indice).length && bootedIndices[indice] && stBkoWaitShellReady(indice)) {

            return;

        }

        if ($('#load-' + indice).length) {

            bootWait(indice, false);

        } else {

            loadFilaBko(indice);

        }

    }



    function initPage() {

        if (pageInited || !$('#content-bko').length) {

            return;

        }

        pageInited = true;

        showPanel();

        var cfg = parseCfg();

        var indice = window.stBkoIndiceAtivo || 1;



        if (cfg.hasActiveAte && cfg.activeChats && cfg.activeChats.length) {

            cfg.activeChats.forEach(function (item, idx) {

                setTimeout(function () {

                    loadAtendAndamento(item.indice, item.protocolo);

                }, idx * 400);

            });

            return;

        }



        if ($('#load-' + indice).length) {

            bootWait(indice);

        } else {

            loadFilaBko(indice);

        }

    }



    window.stBkoDivHasChat = divHasChat;

    window.stBkoInjectScripts = injectScripts;

    window.stBkoStartEspera = bootWait;

    window.stBkoReturnToQueue = stBkoReturnToQueue;

    window.stBkoPulseWaitingTabs = stBkoPulseWaitingTabs;

    window.stBkoStopAll = stopAllPolls;
    window.stBkoStartTa = stBkoStartTa;
    window.stBkoStopTa = stBkoStopTa;
    window.stBkoStopAllTa = stBkoStopAllTa;
    window.stBkoStartFilaPoll = stBkoStartFilaPoll;
    window.stBkoStopAllFilaPolls = stBkoStopAllFilaPolls;
    window.stBkoBindChatSend = stBkoBindChatSend;
    window.stBkoBindChatSendRetry = stBkoBindChatSendRetry;
    window.stBkoInvokeSend = stBkoInvokeSend;

    window.loadBko = loadBko;

    window.loadBko_force = loadBko_force;

    window.loadFilaBko = loadFilaBko;

    window.actionPageChat = openChat;

    window.loadAtendAndamento = loadAtendAndamento;

    window.loadChatIn = loadChatIn;



    window.stBkoBtnPause = function (indice, acao) {
        indice = parseInt(indice, 10) || 1;
        acao = String(acao || 'pause');
        var key = tabKey(indice);
        if (bkoPauseReq[key]) {
            return;
        }

        var $btn = $('#btn_pausa_' + indice);
        var $feed = $('#div_feed_pausa_' + indice);
        bkoPauseReq[key] = true;

        $btn.prop('disabled', true).addClass('disabled');
        $feed.html('<center><i class="fas fa-spinner fa-2x"></i></center>');

        $.post('staff/save_pause.php', { acao: acao }, function () {
            if (typeof window.actionPageNav === 'function') {
                window.actionPageNav('dash-pause', 'idx');
            } else if (typeof window.actionPage === 'function') {
                window.actionPage('dash-pause', 'idx');
            } else {
                location.reload();
            }
        }).fail(function () {
            $feed.html('<div class="text-danger">Falha ao registrar pausa. Tente novamente.</div>');
            $btn.prop('disabled', false).removeClass('disabled');
            bkoPauseReq[key] = false;
        });
    };



    window.stBkoQtdSpans = function (indice) {

        var qtdTabs = $('.tab').length;

        var qtdFila = $('#dadosFila').text();

        if (qtdTabs <= 1) {

            $('#menu_bko, #dash_ate, #my-score, #hist_dash, #hist_pend, #sair').show();

        } else {

            $('#menu_bko, #dash_ate, #my-score, #hist_dash, #hist_pend, #sair').hide();

        }

        if (qtdTabs === 1 && qtdFila == 0) {

            $('#btn_pausa_' + indice).show();

        } else {

            $('#btn_pausa_' + indice).hide();

        }

    };



    window.actionPagePos = function (indice, chatId) {

        chatId = String(chatId || '');
        indice = window.stBkoGetIndiceByChatId(chatId) || parseInt(indice, 10) || 1;

        stopPoll(indice);
        clearChatTimers(indice);
        getFlight(indice).pos = true;

        var $div = $('#div-' + indice);

        if (window.stChatOpen) {

            stChatOpen.show($div, 'Pós-atendimento', 'Carregando formulário...');

        } else {

            $div.html('<div id="load_gif"><img src="img/loading.gif" width="120" alt=""><br>Carregando pós-atendimento...</div>');

        }

        $.post('staff/load_pos_bko.php', { indice: indice, chatId: chatId }, function (html) {

            $div.html(html);
            getFlight(indice).pos = $div.find('.st-pos-page').length > 0;

            if (typeof window.stBkoInjectScripts === 'function') {
                window.stBkoInjectScripts($div);
            }

        }).fail(function () {
            getFlight(indice).pos = false;
            $div.html('<p class="text-danger p-3">Não foi possível carregar o pós-atendimento. Tente novamente.</p>');
        });

    };



    window.StBkoDistrib = {

        init: initPage,

        loadWaitPanel: loadFilaBko,

        startEspera: bootWait,

        openChat: openChat,

        stopAll: stopAllPolls

    };



    window.stBkoOnPageReady = function () {

        resetInit();

        initPage();

    };



    $(function () {

        if ($('#content-bko').length) {

            initPage();

        }

    });



}(window, window.jQuery));

