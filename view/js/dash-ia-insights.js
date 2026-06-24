/**
 * Painel Insights IA — análise diária D+1
 */
(function (window, $) {
    'use strict';

    if (!$) {
        return;
    }

    function esc(s) {
        return $('<div>').text(s == null ? '' : String(s)).html();
    }

    /** Converte **texto** (asteriscos colados ao conteúdo) em negrito, após escape HTML. */
    function formatIaBold(s) {
        return esc(s).replace(/\*\*([^*\n]+)\*\*/g, function (_, inner) {
            return '<strong>' + inner + '</strong>';
        });
    }

    function formatIaParagraphs(text) {
        return String(text == null ? '' : text)
            .split(/\n/)
            .map(formatIaBold)
            .join('<br>');
    }

    function formatDia(iso) {
        if (!iso) return '—';
        var p = String(iso).split('-');
        if (p.length !== 3) return iso;
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function kpiCard(label, value, mod, sub) {
        return '<div class="st-gov-kpi st-gov-kpi--' + (mod || 'default') + '">'
            + '<span class="st-gov-kpi__label">' + esc(label) + '</span>'
            + '<span class="st-gov-kpi__value">' + esc(value) + '</span>'
            + (sub ? '<span class="st-gov-kpi__sub">' + esc(sub) + '</span>' : '')
            + '</div>';
    }

    function motivoLabel(row) {
        if (!row || typeof row !== 'object') {
            return '—';
        }
        var t = row.termo || row.motivo_resumo || row.titulo || '';
        return t !== '' ? String(t) : '—';
    }

    function rankTable(cols, rows, emptyMsg) {
        if (!rows || !rows.length) {
            return '<p class="st-gov-empty">' + esc(emptyMsg || 'Sem dados no período.') + '</p>';
        }
        var h = '<div class="st-gov-table-wrap"><table class="st-gov-table"><thead><tr>';
        cols.forEach(function (c) { h += '<th>' + esc(c.title) + '</th>'; });
        h += '</tr></thead><tbody>';
        rows.forEach(function (row, i) {
            h += '<tr>';
            cols.forEach(function (c) {
                var v = typeof c.render === 'function' ? c.render(row, i) : row[c.key];
                h += '<td>' + esc(v) + '</td>';
            });
            h += '</tr>';
        });
        h += '</tbody></table></div>';
        return h;
    }

    function getFilters() {
        var boot = window.stIaBootConfig || {};
        var ultimo = boot.ultimoDia || '';
        var de = $('#ia_de').val() || boot.de || '';
        var ate = $('#ia_ate').val() || boot.ate || '';
        if (ultimo && ate > ultimo) ate = ultimo;
        if (ultimo && de > ultimo) de = ultimo;
        return {
            de: de,
            ate: ate,
            contrato: parseInt($('#ia_contrato').val(), 10) || 0,
            fila: parseInt($('#ia_fila').val(), 10) || 0
        };
    }

    function render(data) {
        var p = data.periodo || {};
        var k = p.kpis || {};
        var html = '';

        html += '<div class="st-gov-periodo"><i class="fas fa-calendar-alt"></i> Período: '
            + esc(formatDia(data.filtros.de)) + ' a ' + esc(formatDia(data.filtros.ate));
        if (p.dias_com_dados) {
            html += ' — ' + esc(p.dias_com_dados) + ' dia(s) com dados';
        }
        html += '</div>';

        html += '<div class="st-gov-kpis">';
        html += kpiCard('Entradas', k.entradas || 0, 'entradas');
        html += kpiCard('Atendidos', k.atendidos || 0, 'atendidos', 'Taxa ' + (k.taxa_atendimento || 0) + '%');
        html += kpiCard('Abandonos', k.abandonos || 0, 'warn');
        html += kpiCard('TMA médio', k.tma || '--:--:--', 'tma');
        html += kpiCard('TME médio', k.tme || '--:--:--', 'tme');
        if (k.hora_pico != null) {
            var hp = (k.hora_pico < 10 ? '0' : '') + k.hora_pico + 'h';
            html += kpiCard('Hora de pico', hp, 'bko', (k.hora_pico_qtd || 0) + ' entradas');
        }
        if (k.satisfacao_media != null) {
            html += kpiCard('Satisfação', k.satisfacao_media, 'star', (k.satisfacao_total || 0) + ' avaliações');
        }
        html += '</div>';

        html += '<div class="st-gov-panels">';
        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-list-ol"></i> Assuntos mais demandados</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return i + 1; } },
            { title: 'Assunto', key: 'titulo' },
            { title: 'Qtd', key: 'qtd' }
        ], p.top_assuntos);
        html += '</div>';

        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-question-circle"></i> Principais dúvidas (temas)</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return i + 1; } },
            { title: 'Tema / dúvida', render: function (r) { return motivoLabel(r); } },
            { title: 'Menções', key: 'qtd' }
        ], p.top_motivos, 'Sem motivos registrados no período.');
        html += '</div>';
        html += '</div>';

        var ia = data.ia || {};
        var filtros = data.filtros || {};
        html += '<div class="st-ia-section">';
        html += '<h6 class="st-gov-panel__title"><i class="fas fa-robot"></i> Resumo do período (IA)</h6>';

        if (filtros.de && filtros.ate) {
            html += '<p class="st-ia-periodo-label"><i class="fas fa-calendar-alt"></i> '
                + esc(formatDia(filtros.de)) + ' a ' + esc(formatDia(filtros.ate));
            if (ia.analise_do_cache) {
                html += ' <span class="st-ia-cache-tag">(em cache)</span>';
            }
            html += '</p>';
        }

        if (ia.disponivel && ia.analise_periodo) {
            html += '<div class="st-ia-dia-card st-ia-dia-card--periodo">';
            html += '<div class="st-ia-dia-card__body">' + formatIaParagraphs(ia.analise_periodo) + '</div>';
            html += '</div>';
        } else {
            var cls = 'st-ia-banner--warn';
            var msg = ia.motivo_indisponivel || 'Análise por IA indisponível para este período.';
            html += '<div class="st-ia-banner ' + cls + '"><i class="fas fa-exclamation-triangle"></i><div>' + esc(msg) + '</div></div>';
        }
        html += '</div>';

        $('#st-ia-content').html(html).show();
        $('#st-ia-loading').hide();
    }

    function load() {
        var f = getFilters();
        $('#st-ia-loading').show();
        $('#st-ia-content').hide();

        $.getJSON('staff/dash_ia_insights_data.php', f)
            .done(function (data) {
                if (!data || !data.ok) {
                    $('#st-ia-content').html('<div class="alert alert-danger">' + esc((data && data.error) || 'Erro ao carregar') + '</div>').show();
                    $('#st-ia-loading').hide();
                    return;
                }
        if (data.meta && data.meta.ultimo_dia_disponivel) {
            $('#st-ia-ultimo-dia').text(data.meta.ultimo_dia_disponivel);
        }
        if (data.geracao && data.geracao.dias_gerados > 0) {
            var g = data.geracao;
            var msg = g.dias_gerados === 1
                ? '1 dia foi gerado e salvo agora.'
                : g.dias_gerados + ' dias foram gerados e salvos agora.';
            if (g.limite_atingido) {
                msg += ' A geração foi interrompida por limite da API OpenAI.';
            }
            $('#st-ia-geracao-aviso').html('<i class="fas fa-check-circle"></i><div>' + esc(msg) + '</div>').show();
        } else {
            $('#st-ia-geracao-aviso').hide();
        }
        render(data);
            })
            .fail(function () {
                $('#st-ia-content').html('<div class="alert alert-danger">Falha ao carregar insights.</div>').show();
                $('#st-ia-loading').hide();
            });
    }

    function loadFilas(contrato, filaAlvo, onDone) {
        var $fila = $('#ia_fila');
        if (!contrato) {
            $fila.html('<option value="">Todas as filas</option>');
            if (typeof onDone === 'function') {
                onDone();
            }
            return;
        }
        $fila.html('<option value="">Carregando filas...</option>');
        $.post('staff/load_rel_filas.php', { contrato: contrato }, function (valor) {
            $fila.html(valor);
            $fila.find('option[value=""]').first().text('Todas as filas');
            if (filaAlvo) {
                $fila.val(String(filaAlvo));
            }
            if (typeof onDone === 'function') {
                onDone();
            }
        }).fail(function () {
            $fila.html('<option value="">Todas as filas</option>');
            if (typeof onDone === 'function') {
                onDone();
            }
        });
    }

    var pageBound = false;

    function bindPageEvents() {
        if (pageBound) {
            return;
        }
        pageBound = true;
        $(document).on('change.stIa', '#ia_contrato', function () {
            loadFilas($(this).val(), '', null);
        });
        $(document).on('click.stIa', '#ia_btn_filter', function () {
            load();
        });
    }

    function bootPage() {
        if (!$('#st-ia-root').length) {
            return false;
        }
        var boot = window.stIaBootConfig || {};
        if (boot.ultimoDia) {
            $('#ia_de, #ia_ate').attr('max', boot.ultimoDia);
        }
        bindPageEvents();
        var contrato = $('#ia_contrato').val() || (boot.contrato ? String(boot.contrato) : '');
        var filaAlvo = boot.fila ? String(boot.fila) : ($('#ia_fila').val() || '');
        if (boot.contrato) {
            $('#ia_contrato').val(String(boot.contrato));
            contrato = String(boot.contrato);
        }
        loadFilas(contrato, filaAlvo, function () {
            load();
        });
        return true;
    }

    function destroy() {
        pageBound = false;
        $(document).off('.stIa');
    }

    window.stIaInsights = {
        bootPage: bootPage,
        reload: load,
        destroy: destroy
    };
})(window, window.jQuery);
