/**
 * Carregamento sob demanda e em cache do TinyMCE (evita bloquear a página inteira).
 */
(function (window) {
    'use strict';

    var loadPromise = null;

    function getConfig() {
        return window.stTinyMceConfig || {};
    }

    function scriptUrl(path, ver) {
        var cfg = getConfig();
        var base = String(cfg.base || '').replace(/\/$/, '');
        return base + path + (ver ? ('?v=' + encodeURIComponent(String(ver))) : '');
    }

    function localScriptUrl(file, ver) {
        var cfg = getConfig();
        var base = String(cfg.localJs || (cfg.base + '/js')).replace(/\/$/, '');
        return base + '/' + file + (ver ? ('?v=' + encodeURIComponent(String(ver))) : '');
    }

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var existing = document.querySelector('script[src="' + src + '"]');
            if (existing && existing.getAttribute('data-st-loaded') === '1') {
                resolve();
                return;
            }
            var node = existing || document.createElement('script');
            if (!existing) {
                node.src = src;
                node.async = true;
                document.head.appendChild(node);
            }
            node.addEventListener('load', function onLoad() {
                node.setAttribute('data-st-loaded', '1');
                node.removeEventListener('load', onLoad);
                resolve();
            });
            node.addEventListener('error', function onError() {
                node.removeEventListener('error', onError);
                reject(new Error('Falha ao carregar: ' + src));
            });
        });
    }

    function configureTinyMce() {
        if (typeof window.tinymce === 'undefined') {
            return;
        }
        var cfg = getConfig();
        var base = String(cfg.base || '').replace(/\/$/, '');
        if (base) {
            window.tinymce.baseURL = base + '/js/tinymce_5_10_1';
            window.tinymce.suffix = '.min';
        }
    }

    window.stTinyMceReady = function stTinyMceReady() {
        if (typeof window.tinymce !== 'undefined' && typeof window.tinymce.init === 'function') {
            configureTinyMce();
            if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.tinymce !== 'function') {
                var cfg = getConfig();
                return loadScript(scriptUrl('/js/tinymce_5_10_1/jquery.tinymce.min.js', cfg.ver)).then(function () {
                    return window.tinymce;
                });
            }
            return Promise.resolve(window.tinymce);
        }
        if (loadPromise) {
            return loadPromise;
        }

        var cfg = getConfig();
        loadPromise = loadScript(localScriptUrl('st-tinymce-firefox-shim.js', cfg.shimVer))
            .then(function () {
                return loadScript(scriptUrl('/js/tinymce_5_10_1/tinymce.min.js', cfg.ver));
            })
            .then(function () {
                configureTinyMce();
                return loadScript(scriptUrl('/js/tinymce_5_10_1/jquery.tinymce.min.js', cfg.ver));
            })
            .then(function () {
                return window.tinymce;
            })
            .catch(function (err) {
                loadPromise = null;
                throw err;
            });

        return loadPromise;
    };

    window.stTinyMceGetContent = function stTinyMceGetContent(idOrSelector) {
        var $ = window.jQuery;
        var $ta = (idOrSelector && String(idOrSelector).charAt(0) === '#')
            ? $(idOrSelector)
            : $('#' + idOrSelector);
        if (!$ta.length) {
            return '';
        }
        var id = $ta.attr('id');
        if (id && typeof window.tinymce !== 'undefined' && window.tinymce.get(id)) {
            return window.tinymce.get(id).getContent();
        }
        return $ta.val() || '';
    };

    window.stTinyMceRemove = function stTinyMceRemove(idOrSelector) {
        var $ = window.jQuery;
        var $ta = (idOrSelector && String(idOrSelector).charAt(0) === '#')
            ? $(idOrSelector)
            : $('#' + idOrSelector);
        var id = $ta.attr('id');
        if (id && typeof window.tinymce !== 'undefined' && window.tinymce.get(id)) {
            try {
                window.tinymce.get(id).remove();
            } catch (e) { /* ignore */ }
        }
    };

    /** Inicializa TinyMCE somente ao abrir o modal (evita dezenas de instâncias em paralelo). */
    window.stTinyMceApplyOnModal = function stTinyMceApplyOnModal(modalEl, textareaSelector, options) {
        var $ = window.jQuery;
        var $modal = $(modalEl);
        if (!$modal.length) {
            return;
        }
        $modal.off('shown.bs.modal.stTinyMce hidden.bs.modal.stTinyMce')
            .on('shown.bs.modal.stTinyMce', function () {
                window.stTinyMceApply(textareaSelector, options || {}).catch(function () { /* ignore */ });
            })
            .on('hidden.bs.modal.stTinyMce', function () {
                window.stTinyMceRemove(textareaSelector);
            });
    };

    window.stTinyMceApply = function stTinyMceApply(selector, options) {
        return window.stTinyMceReady().then(function () {
            var $el = window.jQuery(selector);
            if (!$el.length) {
                return null;
            }
            var id = $el.attr('id');
            if (id && typeof window.tinymce !== 'undefined' && window.tinymce.get(id)) {
                return window.tinymce.get(id);
            }
            $el.tinymce(options || {});
            return window.tinymce;
        });
    };
}(window));
