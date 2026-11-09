/* Aurora Glass — gráficos amCharts acompanham o tema claro/escuro.
   Carregar logo após js/charts/core.js (amCharts 4). Não altera dados nem cores de série:
   - amCharts 4: tema global (InterfaceColorSet) para gráficos criados depois deste script;
   - amCharts 4 e 5 já desenhados: rótulos com a cor de texto padrão e grades são
     recoloridos na troca de tema (evento st-glassy-themechange) e após cada AJAX
     (os gráficos chegam em HTML via $.post). Rótulos com cor própria não são tocados. */
(function (w, d) {
    'use strict';

    var PAL = {
        dark: { text: '#E9ECFF', grid: '#C1E7F5', background: '#1a1840' },
        light: { text: '#1F2147', grid: '#28285E', background: '#ffffff' }
    };
    var TEXT_DEFAULTS = ['#000000', PAL.dark.text.toLowerCase(), PAL.light.text.toLowerCase()];
    var GRID_DEFAULTS = ['#000000', PAL.dark.grid.toLowerCase(), PAL.light.grid.toLowerCase()];

    function pal() {
        return d.documentElement.getAttribute('data-st-theme') === 'light' ? PAL.light : PAL.dark;
    }

    function isDefault(hex, list) {
        return typeof hex === 'string' && list.indexOf(hex.toLowerCase()) !== -1;
    }

    /* ---------- amCharts 4 ---------- */
    function am4Theme(target) {
        if (w.am4core && target instanceof w.am4core.InterfaceColorSet) {
            var p = pal();
            target.setFor('text', w.am4core.color(p.text));
            target.setFor('grid', w.am4core.color(p.grid));
            target.setFor('background', w.am4core.color(p.background));
        }
    }

    function am4Hex(color) {
        return color && typeof color.hex === 'string' ? color.hex : null;
    }

    function am4Template(list, prop, color, defaults) {
        if (!list || !list.template) {
            return;
        }
        var cur = am4Hex(list.template[prop]);
        if (cur === null || isDefault(cur, defaults)) {
            list.template[prop] = color;
        }
    }

    function am4Walk(sprite, text, grid, depth) {
        if (!sprite || depth > 40 || (sprite.isDisposed && sprite.isDisposed())) {
            return;
        }
        var cn = sprite.className || '';
        if (/Label$/.test(cn) && isDefault(am4Hex(sprite.fill), TEXT_DEFAULTS)) {
            sprite.fill = text;
        } else if (/Grid$/.test(cn) && isDefault(am4Hex(sprite.stroke), GRID_DEFAULTS)) {
            sprite.stroke = grid;
        }
        if (sprite.children && typeof sprite.children.each === 'function') {
            sprite.children.each(function (child) { am4Walk(child, text, grid, depth + 1); });
        }
    }

    function am4Recolor() {
        if (!w.am4core || !w.am4core.registry || !w.am4core.registry.baseSprites) {
            return;
        }
        var p = pal();
        var text = w.am4core.color(p.text);
        var grid = w.am4core.color(p.grid);
        w.am4core.registry.baseSprites.slice().forEach(function (chart) {
            if (!chart || (chart.isDisposed && chart.isDisposed())) {
                return;
            }
            ['xAxes', 'yAxes'].forEach(function (k) {
                if (chart[k] && typeof chart[k].each === 'function') {
                    chart[k].each(function (axis) {
                        if (axis.renderer) {
                            am4Template(axis.renderer.labels, 'fill', text, TEXT_DEFAULTS);
                            am4Template(axis.renderer.grid, 'stroke', grid, GRID_DEFAULTS);
                        }
                    });
                }
            });
            if (chart.legend) {
                am4Template(chart.legend.labels, 'fill', text, TEXT_DEFAULTS);
                am4Template(chart.legend.valueLabels, 'fill', text, TEXT_DEFAULTS);
            }
            if (chart.series && typeof chart.series.each === 'function') {
                chart.series.each(function (s) {
                    am4Template(s.labels, 'fill', text, TEXT_DEFAULTS);
                });
            }
            am4Walk(chart, text, grid, 0);
        });
    }

    /* ---------- amCharts 5 ---------- */
    function am5Hex(color) {
        return color && typeof color.toCSSHex === 'function' ? color.toCSSHex() : null;
    }

    function am5Walk(ent, text, grid, depth) {
        if (!ent || depth > 40 || typeof ent.get !== 'function' || (ent.isDisposed && ent.isDisposed())) {
            return;
        }
        var cn = ent.className || '';
        if (/Label$/.test(cn)) {
            var f = am5Hex(ent.get('fill'));
            if (f === null || isDefault(f, TEXT_DEFAULTS)) {
                ent.set('fill', text);
            }
        } else if (cn === 'Grid' && isDefault(am5Hex(ent.get('stroke')), GRID_DEFAULTS)) {
            ent.set('stroke', grid);
        }
        var renderer = ent.get('renderer');
        if (renderer && renderer.labels && renderer.labels.template) {
            renderer.labels.template.set('fill', text);
            if (renderer.grid && renderer.grid.template) {
                renderer.grid.template.set('stroke', grid);
            }
        }
        if (ent.children && typeof ent.children.each === 'function') {
            ent.children.each(function (child) { am5Walk(child, text, grid, depth + 1); });
        }
    }

    function am5Recolor() {
        if (!w.am5 || !w.am5.registry || !w.am5.registry.rootElements) {
            return;
        }
        var p = pal();
        var text = w.am5.color(p.text);
        var grid = w.am5.color(p.grid);
        w.am5.registry.rootElements.slice().forEach(function (root) {
            if (!root || (root.isDisposed && root.isDisposed())) {
                return;
            }
            try {
                root.interfaceColors.setAll({ text: text, grid: grid });
            } catch (e) { /* versões antigas sem interfaceColors.setAll */ }
            am5Walk(root.container, text, grid, 0);
        });
    }

    function recolor() {
        try { am4Recolor(); } catch (e) { /* gráfico em montagem: tenta no próximo ciclo */ }
        try { am5Recolor(); } catch (e) { /* idem */ }
    }

    var timers = [];
    function scheduleRecolor() {
        timers.forEach(clearTimeout);
        timers = [400, 1500].map(function (ms) { return setTimeout(recolor, ms); });
    }

    if (w.am4core && typeof w.am4core.useTheme === 'function') {
        w.am4core.useTheme(am4Theme);
    }

    d.addEventListener('st-glassy-themechange', recolor);

    if (w.jQuery) {
        w.jQuery(d).ajaxComplete(scheduleRecolor);
    }
    w.addEventListener('load', scheduleRecolor);

    w.stGlassyCharts = { recolor: recolor, palette: pal };
})(window, document);
