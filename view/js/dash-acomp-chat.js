/**
 * Chat de acompanhamento no dashboard (dash-fila) — scroll e novas mensagens.
 */
(function (window, $) {
    'use strict';

    var POLL_MS = 3000;
    var state = {
        timerId: null,
        paneKey: null,
        bkoId: 0,
        xhr: null
    };

    function escHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function scrollBottom($el, force) {
        if (!$el || !$el.length) return;
        var node = $el[0];
        var nearBottom = (node.scrollHeight - node.scrollTop - node.clientHeight) < 80;
        if (!force && !nearBottom) return;

        function go() {
            node.scrollTop = node.scrollHeight;
        }
        go();
        if (window.requestAnimationFrame) {
            requestAnimationFrame(go);
        }
        setTimeout(go, 50);
        setTimeout(go, 200);
    }

    function getActiveChatId($root) {
        var $pane = $root.find('.tab-content > .tab-pane.active.show[id^="chat_ativo_"]').first();
        if (!$pane.length) {
            $pane = $root.find('.tab-content > .tab-pane.active[id^="chat_ativo_"]').first();
        }
        if (!$pane.length) return 0;
        var id = String($pane.attr('id') || '');
        var m = id.match(/^chat_ativo_(\d+)$/);
        return m ? parseInt(m[1], 10) : 0;
    }

    function getLastMsgId($content) {
        var max = 0;
        $content.find('[data-msg-id]').each(function () {
            var id = parseInt($(this).attr('data-msg-id'), 10) || 0;
            if (id > max) max = id;
        });
        return max;
    }

    function buildBubble(msg) {
        var how = msg.how || 'other';
        var html = '<div class="' + escHtml(how) + '" data-msg-id="' + escHtml(msg.id_msg) + '">';
        if (how !== 'sys') {
            html += '<img src="' + escHtml(msg.img || '') + '" alt="">';
        }
        html += '<div class="text">';
        if (how !== 'sys' && msg.name) {
            html += '<h5>' + escHtml(msg.name) + '</h5>';
        }
        html += '<div class="paragrafo">' + (msg.msg || '') + '</div>';
        html += '<div class="dataHora">' + escHtml(msg.hora_msg || '') + '</div>';
        html += '</div></div>';
        return html;
    }

    function appendMessages($content, messages) {
        if (!messages || !messages.length) return false;
        var added = false;
        messages.forEach(function (msg) {
            var id = parseInt(msg.id_msg, 10) || 0;
            if (id <= 0) return;
            if ($content.find('[data-msg-id="' + id + '"]').length) return;
            $content.append(buildBubble(msg));
            added = true;
        });
        return added;
    }

    function updateDig($root) {
        var chatId = getActiveChatId($root);
        if (!chatId) return;
        var $dig = $root.find('#dig_0_' + chatId);
        if ($dig.length) {
            var now = new Date();
            var ts = ('0' + now.getHours()).slice(-2) + ':' +
                ('0' + now.getMinutes()).slice(-2) + ':' +
                ('0' + now.getSeconds()).slice(-2);
            $dig.text('Ao vivo · ' + ts);
        }
    }

    function pollChat($host, chatId) {
        var $content = $host.find('#chat-content_0_' + chatId);
        if (!$content.length) return;

        var sinceId = getLastMsgId($content);

        $.ajax({
            url: 'staff/load_dash_acomp_msgs.php',
            type: 'POST',
            dataType: 'json',
            data: {
                chat_id: chatId,
                user_id: state.bkoId,
                since_id: sinceId
            },
            success: function (res) {
                if (!res || !res.ok) {
                    if (res && res.closed) {
                        updateDig($host);
                    }
                    return;
                }
                if (appendMessages($content, res.messages)) {
                    var isActive = $content.closest('.tab-pane').hasClass('active');
                    scrollBottom($content, isActive);
                }
                updateDig($host);
            }
        });
    }

    function pollOnce() {
        if (!state.paneKey || !state.bkoId) return;
        var $host = $('#div_info_' + state.paneKey);
        if (!$host.is(':visible') || !$host.find('.st-dash-acomp-chat').length) {
            stop();
            return;
        }

        if (state.xhr && state.xhr.readyState !== 4) {
            state.xhr.abort();
        }

        var chatIds = [];
        $host.find('[data-st-chat-id]').each(function () {
            var id = parseInt($(this).attr('data-st-chat-id'), 10) || 0;
            if (id > 0 && chatIds.indexOf(id) === -1) {
                chatIds.push(id);
            }
        });

        if (!chatIds.length) return;

        chatIds.forEach(function (chatId) {
            pollChat($host, chatId);
        });
    }

    function scrollAllVisible($root) {
        $root.find('.tab-pane.active .chat-content').each(function () {
            scrollBottom($(this), true);
        });
    }

    function bindTabEvents($root) {
        $root.off('shown.bs.tab.stDashAcomp', '#tabChat [data-bs-toggle="tab"]');
        $root.on('shown.bs.tab.stDashAcomp', '#tabChat [data-bs-toggle="tab"]', function () {
            scrollAllVisible($root);
            pollOnce();
        });
    }

    function start(paneKey, bkoId) {
        stop();
        state.paneKey = String(paneKey || '');
        state.bkoId = parseInt(bkoId, 10) || 0;
        if (!state.paneKey || !state.bkoId) return;

        window.stDashAcompBkoId = state.bkoId;

        var $root = $('#div_info_' + state.paneKey);
        bindTabEvents($root);
        scrollAllVisible($root);
        pollOnce();
        state.timerId = window.setInterval(pollOnce, POLL_MS);
    }

    function stop() {
        if (state.timerId) {
            clearInterval(state.timerId);
            state.timerId = null;
        }
        if (state.xhr && state.xhr.readyState !== 4) {
            state.xhr.abort();
        }
        state.xhr = null;
        state.paneKey = null;
        state.bkoId = 0;
        window.stDashAcompBkoId = 0;
    }

    function initFromPanel($host, paneKey, bkoId) {
        if (!$host || !$host.length) return;
        start(paneKey, bkoId);
        scrollAllVisible($host);
        $host.find('.chat-content img').off('load.stDashAcomp').on('load.stDashAcomp', function () {
            scrollBottom($(this).closest('.chat-content'), true);
        });
        $host.find('.chat-content').each(function () {
            var $c = $(this);
            if (typeof ResizeObserver !== 'undefined') {
                if ($c.data('stDashAcompRo')) return;
                var ro = new ResizeObserver(function () {
                    scrollBottom($c, false);
                });
                ro.observe(this);
                $c.data('stDashAcompRo', ro);
            }
        });
        setTimeout(function () { scrollAllVisible($host); }, 100);
        setTimeout(function () { scrollAllVisible($host); }, 400);
    }

    window.stDashAcomp = {
        start: start,
        stop: stop,
        initFromPanel: initFromPanel,
        scrollBottom: scrollBottom,
        pollNow: pollOnce
    };
}(window, jQuery));
