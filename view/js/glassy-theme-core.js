/* Tema Aurora Glass dark|light — compartilhado pelo shell (view/index.php), telas de acesso e painel/index.php.
   O valor inicial em <html data-st-theme> é aplicado pelo script inline do <head> (antes da pintura);
   aqui ficam só o botão #st-theme-toggle, a persistência e a sincronização entre abas. */
(function () {
    'use strict';

    var THEME_KEY = 'st-glassy-theme';
    var THEME_ANIM_MS = 450;
    var themeAnimTimer = null;

    function currentTheme() {
        return document.documentElement.getAttribute('data-st-theme') === 'dark' ? 'dark' : 'light';
    }

    function syncThemeToggle(theme) {
        var btn = document.getElementById('st-theme-toggle');
        if (!btn) {
            return;
        }
        var light = theme === 'light';
        btn.setAttribute('aria-pressed', light ? 'true' : 'false');
        btn.setAttribute('title', light ? 'Tema escuro' : 'Tema claro');
        btn.setAttribute('aria-label', light ? 'Tema escuro' : 'Tema claro');
    }

    function applyTheme(theme, animate) {
        var root = document.documentElement;
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (animate && !reduce) {
            root.classList.add('st-theme-anim');
            clearTimeout(themeAnimTimer);
            themeAnimTimer = setTimeout(function () {
                root.classList.remove('st-theme-anim');
            }, THEME_ANIM_MS);
        }
        root.setAttribute('data-st-theme', theme);
        syncThemeToggle(theme);
        var evt;
        try {
            evt = new CustomEvent('st-glassy-themechange', { detail: { theme: theme } });
        } catch (e) {
            evt = document.createEvent('CustomEvent');
            evt.initCustomEvent('st-glassy-themechange', false, false, { theme: theme });
        }
        document.dispatchEvent(evt);
    }

    function init() {
        syncThemeToggle(currentTheme());

        var btn = document.getElementById('st-theme-toggle');
        if (btn) {
            btn.addEventListener('click', function () {
                var next = currentTheme() === 'light' ? 'dark' : 'light';
                applyTheme(next, true);
                try {
                    window.localStorage.setItem(THEME_KEY, next);
                } catch (e) { /* storage bloqueado: vale só para esta página */ }
            });
        }

        window.addEventListener('storage', function (e) {
            if (e.key === THEME_KEY && (e.newValue === 'light' || e.newValue === 'dark') && e.newValue !== currentTheme()) {
                applyTheme(e.newValue, false);
            }
        });
    }

    window.stGlassyTheme = { current: currentTheme, apply: applyTheme };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
