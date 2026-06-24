/**
 * Dashboard operacional (dash-fila) — atualização em tempo real.
 */
(function (window, $) {
    'use strict';

    var STATUS_LABELS = {
        online: 'Livre',
        indisp: 'Indisp.',
        atendimento: 'Em Atendimento',
        pos: 'Pós',
        pausa: 'Em Pausa',
        logout: 'Logout',
        offline: 'Offline'
    };

    var state = {
        intervalId: null,
        tickId: null,
        polling: false,
        enabled: false
    };

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatTempo(segundos) {
        if (segundos < 0) segundos = 0;
        var h = Math.floor(segundos / 3600);
        var m = Math.floor((segundos % 3600) / 60);
        var s = segundos % 60;
        return (h < 10 ? '0' : '') + h + ':' +
            (m < 10 ? '0' : '') + m + ':' +
            (s < 10 ? '0' : '') + s;
    }

    function parseDataHora(texto) {
        if (!texto) return null;
        var d = new Date(String(texto).replace(' ', 'T'));
        return isNaN(d.getTime()) ? null : d;
    }

    function tickTempos() {
        var agora = Date.now();
        var hoje = new Date().toISOString().slice(0, 10);

        $('#dashboard.dash-fila-workspace [data-tempo-base]').each(function () {
            var inicio = parseDataHora($(this).data('tempo-base'));
            if (!inicio) return;
            $(this).text(formatTempo(Math.floor((agora - inicio.getTime()) / 1000)));
        });

        $('#dashboard.dash-fila-workspace [data-hora-registro]').each(function () {
            var horario = $(this).data('hora-registro');
            if (!horario) return;
            var texto = String(horario).length <= 8 ? hoje + ' ' + horario : horario;
            var inicio = parseDataHora(texto.replace(' ', 'T'));
            if (!inicio) return;
            $(this).text(formatTempo(Math.floor((agora - inicio.getTime()) / 1000)));
        });
    }

    function renderTeam(team, contratoId, filaId) {
        if (!team || !team.length) {
            return '<p class="cnf-live-empty">Nenhum backoffice nesta visão.</p>';
        }
        var order = { atendimento: 0, pos: 1, pausa: 2, online: 3, indisp: 4, logout: 5, offline: 6 };
        team = team.slice().sort(function (a, b) {
            var pa = order[a.status] != null ? order[a.status] : 99;
            var pb = order[b.status] != null ? order[b.status] : 99;
            if (pa !== pb) return pa - pb;
            return String(a.nome || '').localeCompare(String(b.nome || ''), 'pt-BR');
        });
        var html = '<div class="dash-users-grid">';
        team.forEach(function (u) {
            var uid = parseInt(u.id, 10) || 0;
            var ctt = parseInt(contratoId, 10) || 0;
            var fila = parseInt(filaId, 10) || 0;
            var badge = u.qtd_atend > 0
                ? '<span class="dash-user-tile__badge badge-count">' + u.qtd_atend + '</span>' : '';
            var tempoAttr = u.tempo_base ? ' data-tempo-base="' + esc(u.tempo_base) + '"' : '';
            var clickAttr = ' onclick="loadInfoUser(' + uid + ',' + ctt + ',' + fila + ')"';
            html += '<div class="dash-user-tile div_user pointer"' + clickAttr + ' title="' + esc(STATUS_LABELS[u.status] || u.status) + '">';
            html += badge;
            html += '<div class="dash-user-tile__avatar-wrap">';
            html += '<img src="' + esc(u.img) + '" class="dash-user-tile__avatar img_perfil_online rounded-circle ' + esc(u.status) + '" alt="">';
            html += '</div>';
            html += '<span class="dash-user-tile__name">' + esc(u.nome) + '</span>';
            html += '<span class="dash-user-tile__time"' + tempoAttr + '>' + esc(u.tempo) + '</span>';
            html += '<span class="dash-user-tile__star"><i class="fas fa-star" aria-hidden="true"></i> ' + esc(u.star || ' -.- ') + '</span>';
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    function renderFilaPanel(itens, showNomeFila, qtd) {
        var count = qtd != null ? qtd : (itens ? itens.length : 0);
        var meta = '<div class="dash-fila-panel"><p class="dash-fila-panel__meta">';
        meta += '<span>' + count + (count === 1 ? ' atendimento na fila' : ' atendimentos na fila') + '</span>';
        meta += '<span class="dash-fila-panel__meta-sep" aria-hidden="true">·</span>';
        meta += '<span>Atualização em tempo real</span></p>';

        if (!itens || !itens.length) {
            return meta + '<div class="dash-fila-empty"><i class="fas fa-check-circle" aria-hidden="true"></i><p class="dash-fila-empty__title">Fila vazia</p><p class="dash-fila-empty__sub">Nenhum atendimento aguardando no momento.</p></div></div>';
        }

        var tableClass = 'table table-hover table-striped table-sm dash-fila-table ' + (showNomeFila ? 'dash-fila-table--geral' : 'dash-fila-table--fila');
        var html = meta + '<div class="dash-fila-table-wrap"><table class="' + tableClass + '"><thead><tr>';
        html += '<th>Protocolo</th><th class="text-center">Local</th>';
        if (showNomeFila) html += '<th class="text-center">Fila</th>';
        html += '<th class="text-center">TE</th></tr></thead><tbody>';
        itens.forEach(function (item) {
            html += '<tr title="' + esc(item.assunto || '') + '" class="pointer">';
            html += '<td class="dash-fila-table__protocol">' + esc(item.protocolo) + '</td>';
            html += '<td class="text-center">' + esc(item.municipio) + '</td>';
            if (showNomeFila) html += '<td class="text-center">' + esc(item.nome_fila) + '</td>';
            html += '<td class="text-center"><span data-hora-registro="' + esc(item.hora_registro) + '">' + esc(item.tempo) + '</span></td>';
            html += '</tr>';
        });
        html += '</tbody></table></div></div>';
        return html;
    }

    function fmtNum(v) {
        return v == null || v === '' ? '0' : String(v);
    }

    function fmtTime(v) {
        return v == null || v === '' ? '--:--:--' : String(v);
    }

    var IND_METRICS = [
        { key: 'bko_online', label: 'BKO online', section: 'agora', mod: 'bko' },
        { key: 'em_fila', label: 'Na fila', section: 'agora', mod: 'fila' },
        { key: 'em_atend', label: 'Em Atend.', section: 'agora', mod: 'atend' },
        {
            key: 'acessos_unicos',
            label: 'Acessos',
            section: 'agora',
            mod: 'acesso',
            geralOnly: true,
            title: 'Pessoas diferentes que acessaram o sistema hoje, até o momento (todos os perfis)'
        },
        {
            key: 'pendencias_fila',
            label: 'Pend. na fila',
            section: 'agora',
            mod: 'pend-fila',
            filaOnly: true,
            title: 'Total de pendências ativas nesta fila'
        },
        {
            key: 'concluidos',
            label: 'Concluídos',
            section: 'hoje',
            mod: 'ok'
        },
        {
            key: 'pendencias',
            label: 'Pendências',
            section: 'hoje',
            mod: 'pend',
            title: 'Registros colocados em pendência hoje'
        },
        { key: 'tma', label: 'TMA', section: 'hoje', mod: 'tma', format: 'time' },
        { key: 'tme', label: 'TME', section: 'hoje', mod: 'tme', format: 'time' },
        { key: 'menor_espera', label: 'Menor Espera', section: 'hoje', mod: 'espera-min', format: 'time' },
        { key: 'maior_espera', label: 'Maior Espera', section: 'hoje', mod: 'espera-max', format: 'time' },
        { key: 'atend_rapido', label: 'Atend. + Rápido', section: 'hoje', mod: 'atend-rap', format: 'time' },
        { key: 'maior_atend', label: 'Maior Atend.', section: 'hoje', mod: 'atend-max', format: 'time' }
    ];

    var TEAM_BAR_ORDER = ['atendimento', 'pos', 'online', 'pausa', 'indisp', 'logout', 'offline'];
    var TEAM_BAR_LABELS = {
        atendimento: 'Em atendimento',
        pos: 'Pós',
        online: 'Livre',
        pausa: 'Pausa',
        indisp: 'Indisp.',
        logout: 'Logout',
        offline: 'Offline'
    };

    function metricValue(ind, spec) {
        var v = ind[spec.key];
        if (spec.format === 'time') {
            return fmtTime(v);
        }
        return fmtNum(v);
    }

    function isGeralPane(contratoId, filaId) {
        return (parseInt(contratoId, 10) || 0) === 0 && (parseInt(filaId, 10) || 0) === 0;
    }

    function metricsForPane(isGeral) {
        return IND_METRICS.filter(function (spec) {
            if (spec.geralOnly && !isGeral) return false;
            if (spec.filaOnly && isGeral) return false;
            return true;
        });
    }

    function buildIndicadoresShell(isGeral) {
        var metrics = metricsForPane(!!isGeral);
        var html = '<div class="dash-ind-panel">';
        html += '<div class="dash-ind-section" data-section="agora">';
        html += '<div class="dash-ind-section__title"><i class="fas fa-bolt" aria-hidden="true"></i> Agora</div>';
        html += '<div class="dash-ind-grid">';
        metrics.forEach(function (spec) {
            if (spec.section !== 'agora') return;
            var titleAttr = spec.title ? ' title="' + esc(spec.title) + '"' : '';
            html += '<div class="dash-ind-card dash-ind-card--' + spec.mod + '" data-metric="' + spec.key + '"' + titleAttr + '>';
            html += '<span class="dash-ind-card__label">' + spec.label + '</span>';
            html += '<span class="dash-ind-card__value">—</span></div>';
        });
        html += '</div></div>';
        html += '<div class="dash-ind-section" data-section="hoje">';
        html += '<div class="dash-ind-section__title"><i class="far fa-calendar-check" aria-hidden="true"></i> Hoje</div>';
        html += '<div class="dash-ind-grid">';
        metrics.forEach(function (spec) {
            if (spec.section !== 'hoje') return;
            var titleAttr = spec.title ? ' title="' + esc(spec.title) + '"' : '';
            html += '<div class="dash-ind-card dash-ind-card--' + spec.mod + '" data-metric="' + spec.key + '"' + titleAttr + '>';
            html += '<span class="dash-ind-card__label">' + spec.label + '</span>';
            html += '<span class="dash-ind-card__value">—</span></div>';
        });
        html += '</div></div>';
        html += '<div class="dash-ind-team">';
        html += '<div class="dash-ind-team__head"><span>Equipe por status</span><span class="dash-ind-team__total" data-team-total>0</span></div>';
        html += '<div class="dash-ind-team__bar" data-team-bar></div>';
        html += '<div class="dash-ind-team__legend" data-team-legend></div>';
        html += '</div></div>';
        return html;
    }

    function updateMetricCards($root, ind, isGeral) {
        ind = ind || {};
        metricsForPane(!!isGeral).forEach(function (spec) {
            var $card = $root.find('[data-metric="' + spec.key + '"]');
            if (!$card.length) return;
            var next = metricValue(ind, spec);
            var $val = $card.find('.dash-ind-card__value');
            if ($val.text() !== next) {
                $val.text(next);
            }
            if (spec.key === 'em_fila' || spec.key === 'pendencias' || spec.key === 'pendencias_fila') {
                $card.toggleClass('dash-ind-card--alert', parseInt(ind[spec.key], 10) > 0);
            }
        });
    }

    function updateTeamSummary($root, team) {
        var counts = {};
        var total = 0;
        (team || []).forEach(function (u) {
            var s = u.status || 'offline';
            counts[s] = (counts[s] || 0) + 1;
            total++;
        });

        var $total = $root.find('[data-team-total]');
        if ($total.text() !== String(total)) {
            $total.text(total);
        }

        var barHtml = '';
        var legendHtml = '';
        TEAM_BAR_ORDER.forEach(function (status) {
            var n = counts[status] || 0;
            if (!n) return;
            var pct = total > 0 ? Math.round((n / total) * 100) : 0;
            var label = TEAM_BAR_LABELS[status] || status;
            barHtml += '<span class="dash-ind-team__seg dash-ind-team__seg--' + status + '" style="flex-grow:' + n + '" title="' + esc(label) + ': ' + n + ' (' + pct + '%)"></span>';
            legendHtml += '<span class="dash-ind-team__chip dash-ind-team__chip--' + status + '"><span class="dash-ind-team__dot"></span>' + esc(label) + ' <strong>' + n + '</strong></span>';
        });

        if (!barHtml) {
            barHtml = '<span class="dash-ind-team__empty">Sem equipe nesta visão</span>';
            legendHtml = '';
        }

        var $bar = $root.find('[data-team-bar]');
        if ($bar.data('html') !== barHtml) {
            $bar.html(barHtml);
            $bar.data('html', barHtml);
        }

        var $legend = $root.find('[data-team-legend]');
        if ($legend.data('html') !== legendHtml) {
            $legend.html(legendHtml);
            $legend.data('html', legendHtml);
        }
    }

    function panelNeedsRebuild($chart, isGeral) {
        var $panel = $chart.find('.dash-ind-panel');
        if (!$panel.length) return true;
        if ($panel.find('[data-metric="espera_tempo"]').length) return true;
        var isGeralPane = !!isGeral;
        if (!isGeralPane && $panel.find('[data-metric="acessos_unicos"]').length) return true;
        if (isGeralPane && !$panel.find('[data-metric="acessos_unicos"]').length) return true;
        if (!isGeralPane && !$panel.find('[data-metric="pendencias_fila"]').length) return true;
        if (isGeralPane && $panel.find('[data-metric="pendencias_fila"]').length) return true;
        var metrics = metricsForPane(isGeralPane);
        for (var i = 0; i < metrics.length; i++) {
            if (!$panel.find('[data-metric="' + metrics[i].key + '"]').length) {
                return true;
            }
        }
        return false;
    }

    function updateIndicadores($chart, ind, team, isGeral) {
        if (!$chart.length) return;
        if (panelNeedsRebuild($chart, isGeral)) {
            $chart.html(buildIndicadoresShell(isGeral));
        }
        var $panel = $chart.find('.dash-ind-panel');
        updateMetricCards($panel, ind, isGeral);
        updateTeamSummary($panel, team);
    }

    function renderIndicadores(ind, team, isGeral) {
        var $wrap = $('<div></div>').html(buildIndicadoresShell(isGeral));
        updateMetricCards($wrap, ind || {}, isGeral);
        updateTeamSummary($wrap, team || []);
        return $wrap.html();
    }

    function paneKey(contratoId, filaId) {
        return parseInt(contratoId, 10) + '_' + parseInt(filaId, 10);
    }

    function updatePane(contratoId, filaId, team, filaItens, indicadores, showNomeFila, esperaQtd) {
        var key = paneKey(contratoId, filaId);
        var $team = $('#div_user_' + key);
        var $fila = $('#div_fila_' + key);
        var $chart = $('#div_chart_' + key);
        if (!$team.length && !$fila.length && !$chart.length) return;
        if ($team.length) $team.html(renderTeam(team, contratoId, filaId));
        if ($fila.length) $fila.html(renderFilaPanel(filaItens, showNomeFila, esperaQtd));
        if ($chart.length) updateIndicadores($chart, indicadores, team, isGeralPane(contratoId, filaId));
    }

    function applyPayload(data) {
        if (!data || !data.ok) return;

        var $badge = $('#dashFilaLiveTs');
        if ($badge.length) {
            $badge.text(data.ts || '—');
        }

        updatePane(0, 0, data.geral.team, data.geral.fila_itens, data.geral.indicadores, true, data.geral.espera_qtd);
        (data.filas || []).forEach(function (f) {
            updatePane(f.contrato_id, f.id_fila, f.team, f.fila_itens, f.indicadores, false, f.espera_qtd);
        });

        tickTempos();
    }

    function fetchLive() {
        if (!state.enabled || state.polling) return;
        state.polling = true;
        $.getJSON('staff/dash_fila_live.php')
            .done(applyPayload)
            .fail(function () {
                $('#dashFilaLiveTs').text('Erro ao atualizar');
            })
            .always(function () {
                state.polling = false;
            });
    }

    function start(cfg) {
        state.enabled = true;
        var interval = parseInt(cfg.interval, 10) || 10000;

        if (state.intervalId) clearInterval(state.intervalId);
        if (state.tickId) clearInterval(state.tickId);

        fetchLive();
        state.intervalId = setInterval(fetchLive, interval);
        state.tickId = setInterval(tickTempos, 1000);

        $(window).off('beforeunload.dashFilaLive').on('beforeunload.dashFilaLive', function () {
            stop();
        });
    }

    function stop() {
        state.enabled = false;
        if (state.intervalId) {
            clearInterval(state.intervalId);
            state.intervalId = null;
        }
        if (state.tickId) {
            clearInterval(state.tickId);
            state.tickId = null;
        }
    }

    window.dashFilaLive = {
        start: start,
        stop: stop,
        refresh: fetchLive
    };
}(window, jQuery));
