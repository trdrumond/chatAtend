/**

 * Abertura de chat — loader unificado e abertura single-flight.

 */

(function (window, $) {

    'use strict';



    var RETRY_MS = 320;

    var LOAD_CHAT_IN_DELAY = 30;

    var OPEN_RETRY_MS = 1500;

    var WAIT_NOT_READY_MS = 2000;

    var MAX_WAIT_RETRIES = 45;

    var chatAteOpen = { inFlight: false, xhr: null, retried: false, waitTimer: null, launched: false, waitRetries: 0 };

    var chatAtePrefetch = { html: null, loading: false, xhr: null, attempted: false };



    function setEnterLock(on) {

        window.stChatSolEnterLock = !!on;

        if (typeof window.stChatSolSetEnterLock === 'function') {

            window.stChatSolSetEnterLock(!!on);

        }

    }



    function isConnReady() {

        return typeof conn !== 'undefined' && conn && conn.readyState === 1;

    }



    function whenConnReady(fn, retryMs) {

        if (typeof fn !== 'function') {

            return;

        }

        if (isConnReady()) {

            fn();

            return;

        }

        setTimeout(function () {

            whenConnReady(fn, retryMs);

        }, typeof retryMs === 'number' ? retryMs : RETRY_MS);

    }



    function escapeHtml(text) {

        return String(text || '')

            .replace(/&/g, '&amp;')

            .replace(/</g, '&lt;')

            .replace(/>/g, '&gt;')

            .replace(/"/g, '&quot;');

    }



    function loaderHtml(title, subtitle) {

        var t = escapeHtml(title || 'Abrindo chat');

        var s = escapeHtml(subtitle || 'Preparando sua conversa...');

        return (

            '<div class="st-chat-open" role="status" aria-live="polite">' +

            '<div class="st-chat-open__panel">' +

            '<div class="st-chat-open__spinner" aria-hidden="true"></div>' +

            '<p class="st-chat-open__title">' + t + '</p>' +

            '<p class="st-chat-open__sub">' + s + '</p>' +

            '</div></div>'

        );

    }



    function ensureOpenLoader(title, subtitle) {

        var $page = $('#action-page');

        if (!$page.length) {

            return;

        }

        if ($page.find('.st-chat-open').length) {

            $page.find('.st-chat-open__title').text(title || 'Abrindo chat');

            $page.find('.st-chat-open__sub').text(subtitle || 'Conectando você ao atendente...');

            return;

        }

        $page.html(loaderHtml(title, subtitle));

    }



    function show($target, title, subtitle) {

        ensureOpenLoader(title, subtitle);

        return loaderHtml(title, subtitle);

    }



    function isSolWorkspaceActive() {

        return typeof window.stChatSolWorkspaceActive === 'function' && window.stChatSolWorkspaceActive();

    }



    function isOpeningAte() {

        return !!(window.stChatSolEnterLock || window.stChatSolOpeningAte || chatAteOpen.inFlight);

    }



    function xhrActive(xhr) {

        return xhr && xhr.readyState !== 0 && xhr.readyState !== 4;

    }



    function setOpeningAte(active) {

        window.stChatSolOpeningAte = !!active;

        if (active) {

            window.redirecionandoAtendimento = true;

            setEnterLock(true);

        }

    }



    function clearWaitTimer() {

        if (chatAteOpen.waitTimer) {

            clearTimeout(chatAteOpen.waitTimer);

            chatAteOpen.waitTimer = null;

        }

    }



    function buildChatAtePostData() {

        var data = { action: 'chat-ate', sec: 'idx' };

        if (window.stFilaSolIdFila) {

            data.id_fila_chat = window.stFilaSolIdFila;

        }

        return data;

    }



    function buildChatAteReadyData() {

        var data = {};

        if (window.stFilaSolIdFila) {

            data.id_fila_chat = window.stFilaSolIdFila;

            data.idFila = window.stFilaSolIdFila;

        }

        return data;

    }



    function fetchChatAteHtml(done, fail) {

        chatAteOpen.xhr = $.ajax({

            url: 'action.php',

            type: 'POST',

            data: buildChatAtePostData(),

            timeout: 60000

        });

        chatAteOpen.xhr.done(done).fail(fail);

        return chatAteOpen.xhr;

    }



    function invokeLoadChatIn() {

        if (window.stChatSolEnded || window.stChatSolEnterLock) {

            return;

        }

        if (isSolWorkspaceActive() || isOpeningAte()) {

            return;

        }

        if (typeof window.redirecionandoAtendimento !== 'undefined' && window.redirecionandoAtendimento) {

            return;

        }

        if ($('#content-bko').length) {

            return;

        }

        if (typeof loadChatIn === 'function') {

            loadChatIn();

        }

    }



    function scheduleLoadChatIn(delay) {

        setTimeout(invokeLoadChatIn, typeof delay === 'number' ? delay : LOAD_CHAT_IN_DELAY);

    }



    function postWhenReady(url, data, done, fail) {

        whenConnReady(function () {

            $.post(url, data, done).fail(fail || function () {});

        });

    }



    function isValidChatAteHtml(html) {

        return typeof html === 'string' && html.indexOf('st-chat-workspace--sol') !== -1;

    }



    function isChatAteNotReadyHtml(html) {

        return typeof html === 'string'

            && html.indexOf('st-chat-workspace--sol') === -1

            && (html.indexOf('st-chat-open') !== -1 || html.indexOf('load_gif') !== -1);

    }



    function markChatAteLaunched() {

        chatAteOpen.launched = true;

        window.stFilaSolChatAteLaunched = true;

    }



    function clearChatAteLaunched() {

        chatAteOpen.launched = false;

        window.stFilaSolChatAteLaunched = false;

    }



    function scheduleWaitRetry(delayMs) {

        if (chatAteOpen.waitTimer || isSolWorkspaceActive()) {

            return;

        }



        chatAteOpen.waitRetries += 1;

        if (chatAteOpen.waitRetries >= MAX_WAIT_RETRIES) {

            chatAteOpen.waitRetries = 0;

            showOpenError();

            return;

        }



        chatAteOpen.inFlight = false;

        chatAteOpen.xhr = null;



        chatAteOpen.waitTimer = setTimeout(function () {

            chatAteOpen.waitTimer = null;

            openChatAteFast(false);

        }, typeof delayMs === 'number' ? delayMs : WAIT_NOT_READY_MS);

    }



    function prefetchChatAteOnce() {

        if (chatAtePrefetch.html || chatAtePrefetch.loading || chatAtePrefetch.attempted) {

            return;

        }

        if (isSolWorkspaceActive() || isOpeningAte()) {

            return;

        }

        if (typeof window.redirecionandoAtendimento !== 'undefined' && window.redirecionandoAtendimento) {

            return;

        }

        chatAtePrefetch.attempted = true;

        chatAtePrefetch.loading = true;

        chatAtePrefetch.xhr = $.ajax({

            url: 'action.php',

            type: 'POST',

            data: buildChatAtePostData(),

            timeout: 60000

        }).done(function (valor) {

            if (isValidChatAteHtml(valor)) {

                chatAtePrefetch.html = String(valor).replace(/ƒ/g, 'f');

            }

        }).always(function () {

            chatAtePrefetch.loading = false;

            chatAtePrefetch.xhr = null;

        });

    }



    function applyChatAteHtml(html) {

        if (typeof html === 'string') {

            html = html.replace(/ƒ/g, 'f');

        }

        if (!isValidChatAteHtml(html)) {

            return false;

        }

        clearWaitTimer();

        clearChatAteLaunched();

        chatAteOpen.waitRetries = 0;

        setEnterLock(false);

        if (typeof window.stInjectActionPageHtml === 'function') {

            window.stInjectActionPageHtml(html);

        } else if ($) {

            $('#action-page').html(html);

        }

        if (typeof window.setMenuActive === 'function') {

            window.setMenuActive('chat-ate', 'idx');

        }

        window.redirecionandoAtendimento = true;

        window.stChatSolOpeningAte = false;

        chatAteOpen.inFlight = false;

        chatAteOpen.xhr = null;

        chatAteOpen.retried = false;

        chatAtePrefetch.html = null;

        if (typeof window.isActionLoading !== 'undefined') {

            window.isActionLoading = false;

        }

        if (typeof window.setMenuEnabled === 'function') {

            window.setMenuEnabled(true);

        }

        return true;

    }



    function resetOpeningAte() {

        clearWaitTimer();

        clearChatAteLaunched();

        chatAteOpen.waitRetries = 0;

        window.stChatSolOpeningAte = false;

        chatAteOpen.inFlight = false;

        chatAteOpen.retried = false;

        setEnterLock(false);

        if (xhrActive(chatAteOpen.xhr)) {

            try {

                chatAteOpen.xhr.abort();

            } catch (e) {}

        }

        chatAteOpen.xhr = null;

        chatAtePrefetch.attempted = false;

        chatAtePrefetch.html = null;

        if (xhrActive(chatAtePrefetch.xhr)) {

            try {

                chatAtePrefetch.xhr.abort();

            } catch (e) {}

            chatAtePrefetch.loading = false;

            chatAtePrefetch.xhr = null;

        }

    }



    function showOpenError() {

        resetOpeningAte();

        window.redirecionandoAtendimento = false;

        window.stFilaSolChatAteLaunched = false;

        if (typeof window.isActionLoading !== 'undefined') {

            window.isActionLoading = false;

        }

        if (typeof window.setMenuEnabled === 'function') {

            window.setMenuEnabled(true);

        }

        if ($) {

            $('#action-page').html(

                '<div style="padding:24px;text-align:center;">' +

                '<p>Não foi possível abrir o chat agora.<br>Aguarde o atendente ou atualize a página.</p>' +

                '<button type="button" class="btn btn-secondary" id="st_chat_ate_retry">Tentar novamente</button>' +

                '</div>'

            );

            $('#st_chat_ate_retry').on('click', function () {

                chatAtePrefetch.attempted = false;

                chatAtePrefetch.html = null;

                if (typeof window.stFilaSolResetOpenFlags === 'function') {

                    window.stFilaSolResetOpenFlags();

                }

                if (typeof window.stChatResetForNewAttendimento === 'function') {

                    window.stChatResetForNewAttendimento();

                }

                openChatAteFast(true);

            });

        }

    }



    function beginChatAteRequest() {

        if (xhrActive(chatAteOpen.xhr)) {

            return chatAteOpen.xhr;

        }

        markChatAteLaunched();

        setOpeningAte(true);

        chatAteOpen.inFlight = true;

        if (typeof window.isActionLoading !== 'undefined') {

            window.isActionLoading = true;

        }

        if (typeof window.setMenuEnabled === 'function') {

            window.setMenuEnabled(false);

        }

        ensureOpenLoader('Abrindo chat', 'Conectando você ao atendente...');

        chatAteOpen.xhr = $.ajax({

            url: 'staff/chat_ate_ready.php',

            type: 'POST',

            data: buildChatAteReadyData(),

            dataType: 'json',

            timeout: 30000

        });

        chatAteOpen.xhr.done(function (status) {

            if (!status || typeof status !== 'object') {

                fetchChatAteHtml(finishRequest, showOpenError);

                return;

            }

            if (status.id_fila_chat) {

                window.stFilaSolIdFila = status.id_fila_chat;

            } else if (status.state === 'wait_fila' && window.stFilaSolIdFila) {

                window.stFilaSolIdFila = 0;

            }

            if (status.state === 'closed') {

                chatAteOpen.xhr = null;

                handleChatAteRedirect('');

                return;

            }

            if (!status.ready) {

                chatAteOpen.xhr = null;

                var sub = status.message || 'Conectando você ao atendente...';

                if (status.state === 'wait_fila') {

                    sub = status.message || 'Aguardando o atendente...';

                } else if (status.state === 'wait_bko') {

                    sub = status.message || 'Aguardando o atendente aceitar...';

                } else if (status.state === 'wait_chat') {

                    sub = status.message || 'Preparando sua conversa...';

                }

                ensureOpenLoader('Abrindo chat', sub);

                finishRequest(loaderHtml('Abrindo chat', sub));

                return;

            }

            fetchChatAteHtml(finishRequest, showOpenError);

        }).fail(function () {

            fetchChatAteHtml(finishRequest, showOpenError);

        });

        return chatAteOpen.xhr;

    }



    function isChatAteDashChaRedirect(html) {

        return typeof html === 'string'

            && (html.indexOf('actionPage("dash-cha"') !== -1

                || html.indexOf("actionPage('dash-cha'") !== -1

                || html.indexOf('actionPageNav("dash-cha"') !== -1

                || html.indexOf("actionPageNav('dash-cha'") !== -1);

    }



    function handleChatAteRedirect(valor) {

        var now = Date.now();

        window._stChatAteDashChaLast = window._stChatAteDashChaLast || 0;

        if (now - window._stChatAteDashChaLast < 3000) {

            return;

        }

        window._stChatAteDashChaLast = now;



        clearWaitTimer();

        chatAteOpen.inFlight = false;

        chatAteOpen.xhr = null;

        chatAteOpen.waitRetries = 0;

        setEnterLock(false);

        window.stChatSolOpeningAte = false;

        window.redirecionandoAtendimento = false;

        window.stFilaSolChatAteLaunched = false;



        if (typeof window.stFilaSolStopAllPolling === 'function') {

            window.stFilaSolStopAllPolling();

        }



        if (typeof window.actionPageNav === 'function') {

            window.actionPageNav('dash-cha', 'idx');

        } else if (typeof window.actionPage === 'function') {

            window.actionPage('dash-cha', 'idx');

        }

    }



    function finishRequest(valor) {

        chatAteOpen.xhr = null;



        if (applyChatAteHtml(valor)) {

            chatAteOpen.retried = false;

            return;

        }



        if (isChatAteDashChaRedirect(valor)) {

            handleChatAteRedirect(valor);

            return;

        }



        ensureOpenLoader('Abrindo chat', 'Conectando você ao atendente...');

        chatAteOpen.inFlight = false;

        chatAteOpen.xhr = null;



        if (isChatAteNotReadyHtml(valor) || !chatAteOpen.retried) {

            if (!isChatAteNotReadyHtml(valor)) {

                chatAteOpen.retried = true;

            }

            scheduleWaitRetry(isChatAteNotReadyHtml(valor) ? WAIT_NOT_READY_MS : OPEN_RETRY_MS);

            return;

        }



        showOpenError();

    }



    function openChatAteFast(force) {

        if (window.stChatSolEnded && isSolWorkspaceActive()) {

            return;

        }

        if (isSolWorkspaceActive()) {

            return;

        }



        if (!force) {

            if (chatAteOpen.inFlight || xhrActive(chatAteOpen.xhr)) {

                return;

            }

            if (chatAteOpen.waitTimer) {

                return;

            }

            if (chatAtePrefetch.html && isValidChatAteHtml(chatAtePrefetch.html)) {

                markChatAteLaunched();

                setOpeningAte(true);

                chatAteOpen.inFlight = true;

                applyChatAteHtml(chatAtePrefetch.html);

                chatAtePrefetch.html = null;

                return;

            }

        } else {

            clearWaitTimer();

            chatAteOpen.retried = false;

            chatAteOpen.waitRetries = 0;

        }



        if (xhrActive(chatAteOpen.xhr)) {

            if (!force) {

                return;

            }

            try {

                chatAteOpen.xhr.abort();

            } catch (e) {}

            chatAteOpen.xhr = null;

            chatAteOpen.inFlight = false;

            window.stChatSolOpeningAte = false;

        }



        if (typeof window.stopDashboardProcessing === 'function') {

            window.stopDashboardProcessing();

        }



        beginChatAteRequest();

    }



    window.stChatOpen = {

        RETRY_MS: RETRY_MS,

        LOAD_CHAT_IN_DELAY: LOAD_CHAT_IN_DELAY,

        OPEN_RETRY_MS: OPEN_RETRY_MS,

        isConnReady: isConnReady,

        whenConnReady: whenConnReady,

        loaderHtml: loaderHtml,

        show: show,

        scheduleLoadChatIn: scheduleLoadChatIn,

        invokeLoadChatIn: invokeLoadChatIn,

        postWhenReady: postWhenReady,

        prefetchChatAteOnce: prefetchChatAteOnce,

        openChatAteFast: openChatAteFast,

        isOpeningAte: isOpeningAte,

        resetOpeningAte: resetOpeningAte

    };

}(window, window.jQuery));

