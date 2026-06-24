/**
 * Assistente virtual da tela de fila (dash-cha).
 * Fluxo: saudação → fila → assunto → motivo → save_call → chat-fila
 */
(function (window, $) {
    'use strict';

    var BOT_REPLY_DELAY = 1000;
    var BOT_REDIRECT_DELAY = 3000;

    var state = {
        started: false,
        step: 'idle',
        nomeRobo: 'Robô Logos',
        nomeUsuario: '',
        filas: [],
        assuntos: [],
        filaId: 0,
        filaNome: '',
        assuntoId: 0,
        assuntoNome: '',
        motivo: ''
    };

    function esc(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function $messages() {
        return $('#st_dash_cha_bot_messages');
    }

    function $inputArea() {
        return $('#st_dash_cha_bot_input_area');
    }

    function scrollChat() {
        var $el = $messages();
        if ($el.length) {
            $el.scrollTop($el[0].scrollHeight);
        }
    }

    function addBotMessage(text, delayMs) {
        if (delayMs === undefined || delayMs === null) {
            delayMs = BOT_REPLY_DELAY;
        }
        showTyping();
        return new Promise(function (resolve) {
            setTimeout(function () {
                clearInput();
                $messages().append(
                    '<div class="st-dash-cha-bot__msg st-dash-cha-bot__msg--bot">'
                    + '<div class="st-dash-cha-bot__avatar" aria-hidden="true"><i class="fas fa-robot"></i></div>'
                    + '<div class="st-dash-cha-bot__bubble">' + esc(text) + '</div>'
                    + '</div>'
                );
                scrollChat();
                resolve();
            }, delayMs);
        });
    }

    function addUserMessage(text) {
        $messages().append(
            '<div class="st-dash-cha-bot__msg st-dash-cha-bot__msg--user">'
            + '<div class="st-dash-cha-bot__bubble">' + esc(text) + '</div>'
            + '</div>'
        );
        scrollChat();
    }

    function showTyping() {
        $inputArea().html(
            '<div class="st-dash-cha-bot__typing">'
            + '<span></span><span></span><span></span>'
            + '</div>'
        );
    }

    function clearInput() {
        $inputArea().empty();
    }

    function showOptionButtons(items, onSelect, hint) {
        hint = hint || 'Clique na opção desejada:';
        var html = '<p class="st-dash-cha-bot__opt-hint"><i class="fas fa-hand-pointer" aria-hidden="true"></i> ' + esc(hint) + '</p>';
        html += '<div class="st-dash-cha-bot__options">';
        items.forEach(function (item, idx) {
            html += '<button type="button" class="st-dash-cha-bot__opt" data-idx="' + idx + '">'
                + esc(item.nome) + '</button>';
        });
        html += '</div>';
        $inputArea().html(html);
        $inputArea().find('.st-dash-cha-bot__opt').on('click', function () {
            var idx = parseInt($(this).data('idx'), 10);
            var item = items[idx];
            if (!item) {
                return;
            }
            $inputArea().find('.st-dash-cha-bot__opt').prop('disabled', true);
            onSelect(item.id, item.nome);
        });
        scrollChat();
    }

    function showMotivoInput() {
        $inputArea().html(
            '<div class="st-dash-cha-bot__motivo-wrap">'
            + '<textarea id="st_dash_cha_bot_motivo" class="input form-control st-dash-cha-bot__motivo" rows="4" placeholder="Descreva o motivo do seu atendimento..."></textarea>'
            + '<button type="button" id="st_dash_cha_bot_send_motivo" class="btn btn-solvetask st-dash-cha-bot__send">'
            + '<i class="fas fa-paper-plane"></i> Enviar'
            + '</button>'
            + '</div>'
        );
        $('#st_dash_cha_bot_send_motivo').on('click', submitMotivo);
        $('#st_dash_cha_bot_motivo').on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                submitMotivo();
            }
        });
        scrollChat();
    }

    function askFila() {
        state.step = 'fila';
        if (!state.filas.length) {
            addBotMessage('No momento não há filas de atendimento disponíveis. Entre em contato com o suporte.');
            clearInput();
            return;
        }
        addBotMessage('Por favor, escolha a fila de atendimento:').then(function () {
            showOptionButtons(state.filas, onFilaSelected, 'Clique na fila desejada:');
        });
    }

    function onFilaSelected(id, nome) {
        state.filaId = id;
        state.filaNome = nome;
        addUserMessage(nome);
        showTyping();
        state.step = 'assunto';

        $.post('staff/load_ass_json.php', { fila: id }, function (resp) {
            var data = resp;
            if (typeof resp === 'string') {
                try { data = JSON.parse(resp); } catch (e) { data = {}; }
            }
            state.assuntos = (data && data.assuntos) ? data.assuntos : [];
            clearInput();

            if (!state.assuntos.length) {
                addBotMessage('Esta fila não possui assuntos cadastrados no momento. Escolha outra fila ou contate o suporte.');
                state.filaId = 0;
                state.filaNome = '';
                askFila();
                return;
            }

            addBotMessage('Ótimo! Agora selecione o assunto do seu atendimento:').then(function () {
                showOptionButtons(state.assuntos, onAssuntoSelected, 'Clique no assunto desejado:');
            });
        }).fail(function () {
            clearInput();
            addBotMessage('Não consegui carregar os assuntos. Tente novamente em instantes.');
            askFila();
        });
    }

    function onAssuntoSelected(id, nome) {
        state.assuntoId = id;
        state.assuntoNome = nome;
        addUserMessage(nome);
        state.step = 'motivo';
        addBotMessage('Por favor, descreva o motivo do seu atendimento:').then(function () {
            showMotivoInput();
        });
    }

    function submitMotivo() {
        var motivo = $.trim($('#st_dash_cha_bot_motivo').val() || '');
        if (!motivo) {
            $('#st_dash_cha_bot_motivo').focus();
            return;
        }
        state.motivo = motivo;
        addUserMessage(motivo);
        clearInput();
        state.step = 'submitting';
        finishAndEnqueue();
    }

    function finishAndEnqueue() {
        addBotMessage(
            'Obrigado pelas informações! Vou direcioná-lo para a fila de atendimento. '
            + 'Em breve você será atendido por nossos especialistas.'
        ).then(function () {
            showTyping();
            $.post('staff/save_call.php', {
                fila: state.filaId,
                assunto: state.assuntoId,
                motivo: state.motivo
            }, function (html) {
                var resp = typeof html === 'string' ? html : '';
                if (resp.indexOf('não esta mais ativa') !== -1 || resp.indexOf('não está mais ativa') !== -1) {
                    clearInput();
                    addBotMessage('A fila selecionada não está mais ativa. Vamos começar novamente.');
                    state.started = false;
                    state.step = 'idle';
                    state.filaId = 0;
                    state.assuntoId = 0;
                    setTimeout(function () {
                        state.started = true;
                        askFila();
                    }, BOT_REPLY_DELAY);
                    return;
                }
                if (typeof actionPage === 'function') {
                    setTimeout(function () {
                        actionPage('chat-fila', 'idx', 1);
                    }, BOT_REDIRECT_DELAY);
                } else if (resp.indexOf('actionPage') !== -1) {
                    setTimeout(function () {
                        $('#feed_call_bot').html(resp);
                    }, BOT_REDIRECT_DELAY);
                }
            }).fail(function () {
                clearInput();
                addBotMessage('Ocorreu um erro ao entrar na fila. Tente novamente.');
                state.step = 'motivo';
                showMotivoInput();
            });
        });
    }

    function runConversation() {
        if (state.started) {
            return;
        }
        state.started = true;
        $messages().empty();
        clearInput();
        showTyping();

        var saudacao = 'Olá ' + state.nomeUsuario + ', tudo bem? Eu sou o ' + state.nomeRobo
            + ' e vou te ajudar a iniciar o seu atendimento.';

        addBotMessage(saudacao).then(function () {
            askFila();
        });
    }

    function init() {
        if (!$('#dashboard.st-dash-cha-workspace').length || !$('#st_dash_cha_bot_messages').length) {
            return;
        }
        if (state.started) {
            return;
        }

        $.post('staff/load_dash_cha_bot_data.php', {}, function (resp) {
            var data = resp;
            if (typeof resp === 'string') {
                try { data = JSON.parse(resp); } catch (e) { data = {}; }
            }
            if (!data || !data.ok) {
                return;
            }
            state.nomeRobo = data.nome_robo || 'Robô Logos';
            state.nomeUsuario = data.nome_usuario || 'visitante';
            state.filas = data.filas || [];
            runConversation();
        }).fail(function () {
            state.nomeUsuario = 'visitante';
            state.nomeRobo = 'Robô Logos';
            state.filas = [];
            runConversation();
        });
    }

    window.stDashChaBotInit = init;
}(window, jQuery));
