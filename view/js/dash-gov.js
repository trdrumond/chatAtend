/**
 * Painel de governança operacional — IDX
 */
(function (window, $) {
    'use strict';

    if (!$) {
        return;
    }

    var charts = {};
    var pollTimer = null;
    var POLL_MS = 120000;

    function esc(s) {
        return $('<div>').text(s == null ? '' : String(s)).html();
    }

    function kpiCard(label, value, mod, sub) {
        return '<div class="st-gov-kpi st-gov-kpi--' + (mod || 'default') + '">'
            + '<span class="st-gov-kpi__label">' + esc(label) + '</span>'
            + '<span class="st-gov-kpi__value">' + esc(value) + '</span>'
            + (sub ? '<span class="st-gov-kpi__sub">' + esc(sub) + '</span>' : '')
            + '</div>';
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
                h += '<td>' + (c.raw ? v : esc(v)) + '</td>';
            });
            h += '</tr>';
        });
        h += '</tbody></table></div>';
        return h;
    }

    function disposeChart(id) {
        if (charts[id]) {
            charts[id].dispose();
            delete charts[id];
        }
    }

    function makeLineChart(containerId, data, series) {
        disposeChart(containerId);
        var el = document.getElementById(containerId);
        if (!el || typeof am4core === 'undefined') {
            return;
        }
        am4core.ready(function () {
            var chart = am4core.create(containerId, am4charts.XYChart);
            chart.data = data;
            chart.paddingRight = 20;

            var cat = chart.xAxes.push(new am4charts.CategoryAxis());
            cat.dataFields.category = 'dia';
            cat.renderer.grid.template.location = 0;
            cat.renderer.minGridDistance = 30;
            cat.renderer.labels.template.rotation = -45;
            cat.renderer.labels.template.fontSize = 11;

            var val = chart.yAxes.push(new am4charts.ValueAxis());
            val.min = 0;

            series.forEach(function (s) {
                var ser = chart.series.push(new am4charts.LineSeries());
                ser.dataFields.categoryX = 'dia';
                ser.dataFields.valueY = s.field;
                ser.name = s.name;
                ser.strokeWidth = 2;
                if (s.color) {
                    ser.stroke = am4core.color(s.color);
                    ser.fill = am4core.color(s.color);
                }
                ser.tooltipText = '{name}: {valueY}';
            });

            chart.cursor = new am4charts.XYCursor();
            chart.legend = new am4charts.Legend();
            chart.legend.position = 'top';
            charts[containerId] = chart;
        });
    }

    function makeBarChart(containerId, data, categoryField, valueField, horizontal) {
        disposeChart(containerId);
        var el = document.getElementById(containerId);
        if (!el || typeof am4core === 'undefined') {
            return;
        }
        am4core.ready(function () {
            var chart = am4core.create(containerId, am4charts.XYChart);
            chart.data = data;

            var catAxis, valAxis, series;
            if (horizontal) {
                catAxis = chart.yAxes.push(new am4charts.CategoryAxis());
                valAxis = chart.xAxes.push(new am4charts.ValueAxis());
                catAxis.dataFields.category = categoryField;
                series = chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.categoryY = categoryField;
                series.dataFields.valueX = valueField;
                series.columns.template.tooltipText = '{categoryY}: {valueX}';
            } else {
                catAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                valAxis = chart.yAxes.push(new am4charts.ValueAxis());
                catAxis.dataFields.category = categoryField;
                catAxis.renderer.labels.template.rotation = -35;
                series = chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.categoryX = categoryField;
                series.dataFields.valueY = valueField;
                series.columns.template.tooltipText = '{categoryX}: {valueY}';
            }
            catAxis.renderer.grid.template.location = 0;
            catAxis.renderer.labels.template.fontSize = 11;
            valAxis.min = 0;
            series.columns.template.fill = am4core.color('#B7202F');
            series.columns.template.stroke = am4core.color('#B7202F');

            charts[containerId] = chart;
        });
    }

    function makePieChart(containerId, data) {
        disposeChart(containerId);
        var el = document.getElementById(containerId);
        if (!el || typeof am4core === 'undefined') {
            return;
        }
        am4core.ready(function () {
            var chart = am4core.create(containerId, am4charts.PieChart);
            chart.data = data;
            chart.innerRadius = am4core.percent(40);

            var pie = chart.series.push(new am4charts.PieSeries());
            pie.dataFields.value = 'qtd';
            pie.dataFields.category = 'nome';
            pie.labels.template.text = '{category}: {value.percent.formatNumber("#.0")}%';
            pie.slices.template.stroke = am4core.color('#fff');
            pie.slices.template.strokeWidth = 2;

            charts[containerId] = chart;
        });
    }

    function formatDia(iso) {
        if (!iso) return '';
        var p = String(iso).split('-');
        if (p.length !== 3) return iso;
        return p[2] + '/' + p[1];
    }

    function render(data) {
        var k = data.kpis || {};
        var sat = k.satisfacao || {};
        var satLabel = sat.media != null && sat.media !== '' ? sat.media + ' ★ (' + sat.total + ')' : '—';

        var html = '<div class="st-gov-periodo">'
            + '<i class="fas fa-calendar-alt"></i> Período: <strong>' + esc(formatDia(data.filtros.de)) + '</strong>'
            + ' a <strong>' + esc(formatDia(data.filtros.ate)) + '</strong>'
            + '</div>';

        html += '<div class="st-gov-kpis">';
        html += kpiCard('Entradas', k.entradas, 'entradas');
        html += kpiCard('Atendidos', k.atendidos, 'atendidos', k.taxa_atendimento + '% do total');
        html += kpiCard('Concluídos', k.concluidos, 'ok');
        html += kpiCard('Abandonos', k.abandonos, 'warn', k.taxa_abandono + '% do total');
        html += kpiCard('TMA médio', k.tma, 'tma');
        html += kpiCard('TME médio', k.tme, 'tme');
        html += kpiCard('Menor espera', k.menor_te, 'te');
        html += kpiCard('Maior espera', k.maior_te, 'warn');
        html += kpiCard('Atend. mais rápido', k.menor_tma, 'ok');
        html += kpiCard('Maior atendimento', k.maior_tma, 'tma');
        html += kpiCard('Produtividade', k.prod_total, 'prod', 'tempo total em atendimento');
        html += kpiCard('Solicitantes', k.solicitantes, 'default');
        html += kpiCard('BKOs ativos', k.bk_ativos, 'bko');
        html += kpiCard('Logins BKO', k.logins_bko, 'bko', 'dias distintos');
        html += kpiCard('Pendências', k.pendencias_periodo, 'pend', k.pendencias_abertas + ' abertas agora');
        html += kpiCard('Satisfação', satLabel, 'star');
        html += '</div>';

        html += '<div class="st-gov-panels">';
        html += '<div class="st-gov-panel st-gov-panel--wide"><h6 class="st-gov-panel__title"><i class="fas fa-chart-line"></i> Evolução diária</h6><div id="st-gov-chart-diario" class="st-gov-chart"></div></div>';
        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-chart-pie"></i> Por situação</h6><div id="st-gov-chart-status" class="st-gov-chart st-gov-chart--pie"></div></div>';
        html += '</div>';

        html += '<div class="st-gov-panels">';
        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-clock"></i> Distribuição por hora</h6><div id="st-gov-chart-hora" class="st-gov-chart"></div></div>';
        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-tags"></i> Top assuntos</h6><div id="st-gov-chart-assuntos" class="st-gov-chart"></div></div>';
        html += '</div>';

        if (data.por_fila && data.por_fila.length) {
            html += '<div class="st-gov-panel st-gov-panel--wide"><h6 class="st-gov-panel__title"><i class="fas fa-stream"></i> Desempenho por fila</h6>';
            html += rankTable([
                { title: '#', render: function (r, i) { return i + 1; } },
                { title: 'Fila', key: 'nome' },
                { title: 'Entradas', key: 'entradas' },
                { title: 'Atendidos', key: 'atendidos' },
                { title: 'TMA', key: 'tma' }
            ], data.por_fila);
            html += '</div>';
        }

        html += '<div class="st-gov-panel st-gov-panel--wide"><h6 class="st-gov-panel__title"><i class="fas fa-users"></i> Ranking de solicitantes</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return '<span class="st-gov-rank">' + (i + 1) + '</span>'; }, raw: true },
            { title: 'Solicitante', key: 'nome' },
            { title: 'Empresa', key: 'empresa' },
            { title: 'Entradas', key: 'qtd' },
            { title: 'Atendidos', key: 'atendidos' },
            { title: '% Atend.', key: 'pct_atendido', render: function (r) { return r.pct_atendido + '%'; } }
        ], data.rank_solicitantes);
        html += '</div>';

        html += '<div class="st-gov-rankings">';
        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-trophy"></i> Maior volume</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return '<span class="st-gov-rank">' + (i + 1) + '</span>'; }, raw: true },
            { title: 'Atendente', key: 'nome' },
            { title: 'Qtd', key: 'qtd' },
            { title: 'TMA', key: 'tma' }
        ], data.rank_volume);
        html += '</div>';

        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-bolt"></i> Melhor TMA</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return i + 1; } },
            { title: 'Atendente', key: 'nome' },
            { title: 'Qtd', key: 'qtd' },
            { title: 'TMA', key: 'tma' }
        ], data.rank_tma, 'Mínimo 3 atendimentos no período.');
        html += '</div>';

        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-hourglass-half"></i> Menor espera (TME)</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return i + 1; } },
            { title: 'Atendente', key: 'nome' },
            { title: 'Qtd', key: 'qtd' },
            { title: 'TME', key: 'tme' }
        ], data.rank_tme, 'Mínimo 3 atendimentos no período.');
        html += '</div>';

        html += '<div class="st-gov-panel"><h6 class="st-gov-panel__title"><i class="fas fa-layer-group"></i> Pico simultâneo</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return i + 1; } },
            { title: 'Atendente', key: 'nome' },
            { title: 'Pico', key: 'pico', render: function (r) { return r.pico + ' atend.'; } }
        ], data.rank_simultaneo, 'Apenas atendentes com mais de 1 atendimento simultâneo.');
        html += '</div>';
        html += '</div>';

        html += '<div class="st-gov-panel st-gov-panel--wide"><h6 class="st-gov-panel__title"><i class="fas fa-list-ol"></i> Ranking de assuntos</h6>';
        html += rankTable([
            { title: '#', render: function (r, i) { return i + 1; } },
            { title: 'Assunto', key: 'titulo' },
            { title: 'Atendimentos', key: 'qtd' },
            { title: 'TMA médio', key: 'tma' }
        ], data.top_assuntos);
        html += '</div>';

        $('#st-gov-content').html(html).show();
        $('#st-gov-loading').hide();

        var diario = (data.serie_diaria || []).map(function (d) {
            return {
                dia: formatDia(d.dia),
                entradas: parseInt(d.entradas, 10) || 0,
                atendidos: parseInt(d.atendidos, 10) || 0,
                abandonos: parseInt(d.abandonos, 10) || 0
            };
        });
        makeLineChart('st-gov-chart-diario', diario, [
            { field: 'entradas', name: 'Entradas', color: '#5C5C5C' },
            { field: 'atendidos', name: 'Atendidos', color: '#1e7e34' },
            { field: 'abandonos', name: 'Abandonos', color: '#c0392b' }
        ]);

        makePieChart('st-gov-chart-status', (data.por_status || []).map(function (s) {
            return { nome: s.nome || ('Status ' + s.status_fila), qtd: parseInt(s.qtd, 10) || 0 };
        }));

        var porHora = [];
        for (var h = 0; h < 24; h++) {
            porHora.push({ hora: (h < 10 ? '0' : '') + h + 'h', qtd: 0 });
        }
        (data.por_hora || []).forEach(function (row) {
            var idx = parseInt(row.hora, 10);
            if (idx >= 0 && idx < 24) {
                porHora[idx].qtd = parseInt(row.qtd, 10) || 0;
            }
        });
        makeBarChart('st-gov-chart-hora', porHora, 'hora', 'qtd', false);

        var assuntos = (data.top_assuntos || []).slice(0, 8).map(function (a) {
            var t = a.titulo || '';
            return { titulo: t.length > 28 ? t.substring(0, 26) + '…' : t, qtd: parseInt(a.qtd, 10) || 0 };
        });
        makeBarChart('st-gov-chart-assuntos', assuntos, 'titulo', 'qtd', true);
    }

    function load() {
        if (!$('#st-gov-root').length) {
            return;
        }
        $('#st-gov-loading').show();
        var params = {
            contrato: $('#gov_contrato').val() || 0,
            fila: $('#gov_fila').val() || 0,
            de: $('#gov_de').val(),
            ate: $('#gov_ate').val()
        };
        $.getJSON('staff/dash_gov_data.php', params)
            .done(function (data) {
                if (!data || !data.ok) {
                    $('#st-gov-content').html('<div class="alert alert-warning">Não foi possível carregar os dados.</div>').show();
                    $('#st-gov-loading').hide();
                    return;
                }
                render(data);
            })
            .fail(function () {
                $('#st-gov-content').html('<div class="alert alert-danger">Erro ao consultar o painel. Tente novamente.</div>').show();
                $('#st-gov-loading').hide();
            });
    }

    function applyFilters(defaults) {
        defaults = defaults || {};
        if (defaults.de) {
            $('#gov_de').val(defaults.de);
        }
        if (defaults.ate) {
            $('#gov_ate').val(defaults.ate);
        }
        if (defaults.contrato !== undefined && defaults.contrato !== null && String(defaults.contrato) !== '') {
            $('#gov_contrato').val(String(defaults.contrato));
        }
        if (defaults.fila !== undefined && defaults.fila !== null && String(defaults.fila) !== '') {
            $('#gov_fila').val(String(defaults.fila));
        }
    }

    function loadFilas(contrato, filaAlvo, onDone) {
        var $fila = $('#gov_fila');
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

    function startPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(load, POLL_MS);
    }

    function init(defaults) {
        applyFilters(defaults);
        var contrato = $('#gov_contrato').val();
        var filaAlvo = defaults && defaults.fila ? defaults.fila : $('#gov_fila').val();
        loadFilas(contrato, filaAlvo, function () {
            load();
            startPolling();
        });
    }

    var pageBound = false;

    function bindPageEvents() {
        if (pageBound) {
            return;
        }
        pageBound = true;
        $(document).on('change.stGov', '#gov_contrato', function () {
            loadFilas($(this).val(), '', null);
        });
        $(document).on('click.stGov', '#gov_btn_filter', function () {
            load();
        });
    }

    function bootPage() {
        if (!$('#st-gov-root').length) {
            return false;
        }
        bindPageEvents();
        var cfg = window.stGovBootConfig || {};
        window.stGovBootConfig = null;
        init(cfg);
        return true;
    }

    function destroy() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
        pageBound = false;
        $(document).off('.stGov');
        Object.keys(charts).forEach(disposeChart);
    }

    window.stGovDashboard = {
        init: init,
        load: load,
        bootPage: bootPage,
        destroy: destroy
    };

    $(window).on('beforeunload', destroy);

    if (window.stGovBootConfig && $('#st-gov-root').length) {
        bootPage();
    }

}(window, window.jQuery));
