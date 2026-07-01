(function () {
    'use strict';

    var cfg = window.MONITORA_DOC || {};
    var apiBase = (cfg.apiBase || '').replace(/\/$/, '');
    var proxyUrl = cfg.proxyUrl || 'doc.php?action=proxy';
    var storageKey = 'monitora_api_token';

    var elToken = document.getElementById('token');
    var elTokenStatus = document.getElementById('token-status');
    var elTokenVisible = document.getElementById('token-visible');
    var elFormToken = document.getElementById('form-token');
    var elResultMeta = document.getElementById('result-meta');
    var elResultBody = document.getElementById('result-body');
    var elCurl = document.getElementById('curl-preview');
    var elEndpointLabel = document.getElementById('endpoint-label');
    var tabs = document.querySelectorAll('.tab');
    var panels = document.querySelectorAll('.endpoint-panel');
    var btnRun = document.getElementById('btn-run');
    var btnRunAll = document.getElementById('btn-run-all');
    var btnClear = document.getElementById('btn-clear');

    var currentEndpoint = 'status';

    function nodeListEach(list, fn) {
        for (var i = 0; i < list.length; i++) {
            fn(list[i], i);
        }
    }

    function setClassActive(el, className, active) {
        if (!el) return;
        if (active) {
            el.className = el.className.indexOf(className) === -1 ? el.className + ' ' + className : el.className;
        } else {
            el.className = el.className.replace(new RegExp('(^|\\s)' + className + '(\\s|$)', 'g'), ' ').replace(/\s+/g, ' ').trim();
        }
    }

    function loadTokenFromStorage() {
        if (!elToken || elToken.value) {
            return;
        }
        try {
            var saved = localStorage.getItem(storageKey) || sessionStorage.getItem(storageKey);
            if (saved) {
                elToken.value = saved;
            }
        } catch (e) {}
    }

    function persistTokenLocally() {
        if (!elToken) return;
        try {
            localStorage.setItem(storageKey, elToken.value || '');
            sessionStorage.setItem(storageKey, elToken.value || '');
        } catch (e) {}
    }

    function getToken() {
        return (elToken && elToken.value) ? elToken.value.trim() : '';
    }

    function hasTokenForTest() {
        return cfg.hasToken === true || getToken() !== '';
    }

    function getField(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function buildPath(endpoint) {
        if (endpoint.indexOf('atendimentos/') === 0) {
            return 'monitora/atendimentos/' + encodeURIComponent(getField('param-protocolo'));
        }
        return 'monitora/' + endpoint;
    }

    function buildQuery(endpoint) {
        var query = {};

        if (endpoint === 'filas') {
            query.contrato = getField('param-contrato-filas') || getField('param-contrato');
        }

        if (endpoint === 'atendimentos') {
            query.data_inicio = getField('param-data-inicio');
            query.data_fim = getField('param-data-fim');
            query.contrato = getField('param-contrato');
            var fila = getField('param-fila');
            if (fila) query.fila = fila;
            query.pagina = getField('param-pagina') || '1';
            query.por_pagina = getField('param-por-pagina') || '10';
        }

        return query;
    }

    function buildApiUrl(endpoint) {
        var path = buildPath(endpoint);
        var query = buildQuery(endpoint);
        var qs = [];
        var key;

        for (key in query) {
            if (Object.prototype.hasOwnProperty.call(query, key) && query[key] !== '') {
                qs.push(encodeURIComponent(key) + '=' + encodeURIComponent(query[key]));
            }
        }

        return apiBase + '/' + path + (qs.length ? '?' + qs.join('&') : '');
    }

    function buildProxyUrl(endpoint) {
        var path = buildPath(endpoint);
        var query = buildQuery(endpoint);
        var parts = [proxyUrl, 'path=' + encodeURIComponent(path)];
        var key;

        for (key in query) {
            if (Object.prototype.hasOwnProperty.call(query, key) && query[key] !== '') {
                parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(query[key]));
            }
        }

        return parts.join('&');
    }

    function buildCurl(url) {
        var token = getToken() || 'SEU_TOKEN';
        return [
            'curl -s "' + url + '" \\',
            '  -H "Accept: application/json" \\',
            '  -H "Authorization: Bearer ' + token + '"'
        ].join('\n');
    }

    function setActiveTab(endpoint) {
        currentEndpoint = endpoint;

        nodeListEach(tabs, function (tab) {
            setClassActive(tab, 'active', tab.getAttribute('data-endpoint') === endpoint);
        });

        nodeListEach(panels, function (panel) {
            panel.style.display = panel.getAttribute('data-endpoint') === endpoint ? 'block' : 'none';
        });

        if (elEndpointLabel) {
            elEndpointLabel.textContent = 'GET /monitora/' + (endpoint === 'detalhe' ? 'atendimentos/{protocolo}' : endpoint);
        }

        if (elCurl) {
            var curlEndpoint = endpoint === 'detalhe'
                ? 'atendimentos/' + encodeURIComponent(getField('param-protocolo') || '{protocolo}')
                : endpoint;
            elCurl.textContent = buildCurl(buildApiUrl(curlEndpoint));
        }
    }

    function showResult(metaHtml, bodyText) {
        if (elResultMeta) elResultMeta.innerHTML = metaHtml;
        if (elResultBody) {
            elResultBody.textContent = bodyText;
            if (elResultBody.parentElement) {
                elResultBody.parentElement.className = 'result-panel';
            }
        }
    }

    function clearResult() {
        if (elResultMeta) elResultMeta.innerHTML = '';
        if (elResultBody) {
            elResultBody.textContent = 'Execute um teste para ver a resposta JSON aqui.';
            if (elResultBody.parentElement) {
                elResultBody.parentElement.className = 'result-panel empty';
            }
        }
    }

    function formatJson(text) {
        try {
            return JSON.stringify(JSON.parse(text), null, 2);
        } catch (e) {
            return text;
        }
    }

    function runTest(endpoint) {
        if (!hasTokenForTest()) {
            showResult(
                '<span class="status-err">Token não salvo</span>',
                'Informe o token e clique em "Salvar token" antes de executar os testes.'
            );
            return Promise.resolve();
        }

        var urlEndpoint = endpoint;
        if (endpoint === 'detalhe') {
            if (!getField('param-protocolo')) {
                showResult('<span class="status-err">Protocolo obrigatório</span>', 'Informe o número do protocolo.');
                return Promise.resolve();
            }
            urlEndpoint = 'atendimentos/' + encodeURIComponent(getField('param-protocolo'));
        }

        var url = buildProxyUrl(urlEndpoint);
        var apiUrl = buildApiUrl(urlEndpoint);
        var started = (window.performance && performance.now) ? performance.now() : Date.now();

        showResult('<span>Aguardando...</span>', 'Enviando requisição via proxy...');
        if (elCurl) elCurl.textContent = buildCurl(apiUrl);

        return fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(function (res) {
                var now = (window.performance && performance.now) ? performance.now() : Date.now();
                var elapsed = Math.round(now - started);
                return res.text().then(function (body) {
                    var statusClass = res.ok ? 'status-ok' : 'status-err';
                    var meta = [
                        '<span class="' + statusClass + '">HTTP ' + res.status + '</span>',
                        '<span>' + elapsed + ' ms</span>',
                        '<span>' + apiUrl + '</span>'
                    ].join('');
                    showResult(meta, formatJson(body));
                });
            })
            .catch(function (err) {
                showResult(
                    '<span class="status-err">Erro de rede</span>',
                    'Não foi possível executar o teste.\n\n' + (err.message || String(err))
                );
            });
    }

    function runAllSequential() {
        var sequence = ['status', 'contratos', 'filas', 'atendimentos'];
        var chain = Promise.resolve();

        nodeListEach(sequence, function (ep) {
            chain = chain.then(function () {
                setActiveTab(ep);
                return runTest(ep);
            });
        });

        return chain;
    }

    nodeListEach(tabs, function (tab) {
        tab.addEventListener('click', function () {
            setActiveTab(tab.getAttribute('data-endpoint'));
        });
    });

    if (elFormToken) {
        elFormToken.addEventListener('submit', function () {
            persistTokenLocally();
        });
    }

    if (elToken) {
        elToken.addEventListener('input', persistTokenLocally);
    }

    if (elTokenVisible && elToken) {
        elTokenVisible.addEventListener('change', function () {
            elToken.type = elTokenVisible.checked ? 'text' : 'password';
        });
    }

    if (btnRun) {
        btnRun.addEventListener('click', function () {
            runTest(currentEndpoint);
        });
    }

    if (btnRunAll) {
        btnRunAll.addEventListener('click', function () {
            runAllSequential();
        });
    }

    if (btnClear) {
        btnClear.addEventListener('click', clearResult);
    }

    nodeListEach(document.querySelectorAll('[data-sync-curl]'), function (input) {
        input.addEventListener('input', function () {
            if (!elCurl) return;
            var ep = currentEndpoint === 'detalhe'
                ? 'atendimentos/' + encodeURIComponent(getField('param-protocolo') || '{protocolo}')
                : currentEndpoint;
            elCurl.textContent = buildCurl(buildApiUrl(ep));
        });
    });

    loadTokenFromStorage();
    setActiveTab('status');
    clearResult();
})();
