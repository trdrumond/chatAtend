/* Tema Glassy (Aurora Glass dark/light) — só classes/atributos de apresentação; não move DOM nem altera IDs.
   A troca de tema (botão #st-theme-toggle) fica em glassy-theme-core.js. */
(function () {
    'use strict';

    var COUNTER_IDS = ['dadosPendMen', 'countCom'];

    function syncCounter(el) {
        var txt = (el.textContent || '').replace(/\s+/g, '');
        el.classList.toggle('st-glassy-count-empty', txt === '' || txt === '0');
    }

    function initCounters() {
        COUNTER_IDS.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) {
                return;
            }
            syncCounter(el);
            if (typeof MutationObserver === 'function') {
                new MutationObserver(function () {
                    syncCounter(el);
                }).observe(el, { childList: true, characterData: true, subtree: true });
            }
        });
    }

    function menuLabel(el) {
        var txt = '';
        for (var i = 0; i < el.childNodes.length; i++) {
            if (el.childNodes[i].nodeType === 3) {
                txt += el.childNodes[i].nodeValue;
            }
        }
        return txt.replace(/\s+/g, ' ').trim();
    }

    /* Dock/tab bar mostra só ícones em telas pequenas: rótulo via title + acesso por teclado. */
    function initMenu() {
        var items = document.querySelectorAll('#menu .span-menu');
        Array.prototype.forEach.call(items, function (el) {
            var label = menuLabel(el);
            if (label && !el.hasAttribute('title')) {
                el.setAttribute('title', label);
            }
            if (!el.hasAttribute('tabindex')) {
                el.setAttribute('tabindex', '0');
            }
            if (!el.hasAttribute('role')) {
                el.setAttribute('role', 'button');
            }
        });

        var menu = document.getElementById('menu');
        if (!menu) {
            return;
        }
        menu.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') {
                return;
            }
            var el = e.target && e.target.closest ? e.target.closest('.span-menu') : null;
            if (!el || el.classList.contains('disabled') || el.style.pointerEvents === 'none') {
                return;
            }
            e.preventDefault();
            el.click();
        });
    }

    /* Refração na borda (feDisplacementMap em backdrop-filter) só funciona no Chromium. */
    function initRefraction() {
        var brands = (navigator.userAgentData && navigator.userAgentData.brands) || [];
        var isChromium = brands.some(function (b) { return /Chromium/i.test(b.brand); });
        if (!isChromium || !window.matchMedia) {
            return;
        }
        if (window.matchMedia('(prefers-reduced-transparency: reduce)').matches) {
            return;
        }
        if (!document.getElementById('st-glass-refract')) {
            return;
        }
        document.body.classList.add('st-glassy-refract');
    }

    function init() {
        if (!document.body || !document.body.classList.contains('st-glassy')) {
            return;
        }
        initCounters();
        initMenu();
        initRefraction();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
