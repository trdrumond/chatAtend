// Ao injetar HTML com <script src>, o jQuery usa _evalUrl com async:false (XHR síncrono).
(function ($) {
    if ($ && $.ajaxPrefilter) {
        $.ajaxPrefilter(function (options) {
            if (options.async === false && options.dataType === 'script') {
                options.async = true;
            }
        });
    }
}(window.jQuery));

// Controle de navegação das páginas (menu -> action.php)
var currentActionRequest = null;
var isActionLoading = false;
var actionRetryCount = 0;
var ACTION_MAX_RETRY = 1;       // número máximo de tentativas extras automáticas
var ACTION_TIMEOUT_MS = 30000;  // timeout da chamada em ms (30s)
var refreshDash = null;         // intervalo do dashboard

/** Para todo processamento do dashboard ao trocar de página (intervalos + requisição em andamento). */
function stopDashboardProcessing() {
    if (typeof window.stBkoStopAll === 'function') {
        window.stBkoStopAll();
    }
    if (refreshDash) {
        clearInterval(refreshDash);
        refreshDash = null;
    }
    if (window.__dashIdxInterval) {
        clearInterval(window.__dashIdxInterval);
        window.__dashIdxInterval = null;
    }
    if (window._dashboardIntervals && window._dashboardIntervals.length) {
        window._dashboardIntervals.forEach(function (id) { clearInterval(id); });
        window._dashboardIntervals = [];
    }
    if (window.stDadosIdxInFlight) {
        try { window.stDadosIdxInFlight.abort(); } catch (e) {}
        window.stDadosIdxInFlight = null;
    }
    if (typeof window.dashFilaLive !== 'undefined' && typeof window.dashFilaLive.stop === 'function') {
        window.dashFilaLive.stop();
    }
    if (typeof window.stGovDashboard !== 'undefined' && typeof window.stGovDashboard.destroy === 'function') {
        window.stGovDashboard.destroy();
    }
    if (typeof window.stIaInsights !== 'undefined' && typeof window.stIaInsights.destroy === 'function') {
        window.stIaInsights.destroy();
    }
    if (currentActionRequest) {
        try { currentActionRequest.abort(); } catch (e) {}
        currentActionRequest = null;
    }
}

function setMenuActive(action, sec) {
    $(".span-menu").removeClass("active");
    var menuId = action;
    if (sec === "cnf" && $("#" + action + "-cnf").length) {
        menuId = action + "-cnf";
    }
    if ($("#" + menuId).length) {
        $("#" + menuId).addClass("active");
    }
}

function setMenuEnabled(enabled) {
    var $items = $("#dash-idx, #dash-fila, #gov-analytics, #ia-insights, #dash-ate, #dash-cha, #my-score, #rel-dash, #rel-ind, #rel-fila, #hist-dash, #hist-pend, #com-idx," +
        " #dash-cnf, #cad-ctt-cnf, #cad-reg-cnf, #cad-emp-cnf, #cad-age-cnf," +
        " #cad-ass-cnf, #cad-pri-cnf, #cad-fil-cnf, #cad-men-cnf, #cad-usu-cnf," +
        " #cad-faq-cnf, #log-acess-cnf, #cnf-ia-cnf, #res-base, #pass");
    if (enabled) {
        $items.removeClass("disabled").css("pointer-events", "auto");
    } else {
        $items.addClass("disabled").css("pointer-events", "none");
    }
}

/** Injeta folhas de estilo no <head> uma única vez (evita loop ao usar .html()). */
function stEnsurePageStylesheets($container) {
    $container.find('link[rel="stylesheet"]').each(function () {
        var href = $(this).attr('href');
        if (!href) {
            return;
        }
        var absHref = href;
        try {
            absHref = new URL(href, window.location.href).href.split('#')[0];
        } catch (e) {}
        var safeKey = absHref.replace(/[^a-zA-Z0-9]/g, '_');
        if ($('head link[data-st-injected="' + safeKey + '"]').length) {
            return;
        }
        $('<link>', {
            rel: 'stylesheet',
            type: 'text/css',
            href: href
        }).attr('data-st-injected', safeKey).appendTo('head');
    });
    $container.find('link[rel="stylesheet"]').remove();
}

/** Token incrementado a cada navegação AJAX — evita init DataTable de página anterior. */
var stCnfDataTableInitToken = 0;
var stCnfDataTableInitTimer = null;

/** Remove instâncias DataTables órfãs (DOM substituído via AJAX sem destroy). */
function stPurgeOrphanDataTables() {
    if (!window.jQuery || !$.fn.dataTable || !$.fn.dataTable.settings) {
        return;
    }
    var settings = $.fn.dataTable.settings;
    for (var i = settings.length - 1; i >= 0; i--) {
        var node = settings[i].nTable;
        if (!node || !node.parentNode || !document.body.contains(node)) {
            settings.splice(i, 1);
        }
    }
}

/** Destrói DataTables do #action-page antes de substituir o HTML (navegação AJAX). */
function stDestroyActionPageDataTables() {
    if (!window.jQuery || !$.fn.DataTable) {
        return;
    }
    $('#action-page').find('table').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            try {
                $(this).DataTable().clear().destroy(false);
            } catch (e) { /* ignore */ }
        }
    });
    stPurgeOrphanDataTables();
}

/** Destrói DataTable de um elemento de forma segura. */
function stSafeDestroyDataTable($table) {
    if (!window.jQuery || !$.fn.DataTable || !$table || !$table.length) {
        return;
    }
    var node = $table[0];
    if (!$.fn.DataTable.isDataTable(node)) {
        return;
    }
    try {
        $table.DataTable().clear().destroy(false);
    } catch (e) { /* ignore */ }
}

/** Remove editores TinyMCE do #action-page antes de substituir HTML. */
function stDestroyActionPageTinyMce() {
    if (typeof window.tinymce === 'undefined' || !window.jQuery) {
        return;
    }
    $('#action-page textarea[id]').each(function () {
        var id = this.id;
        if (id && window.tinymce.get(id)) {
            try {
                window.tinymce.get(id).remove();
            } catch (e) { /* ignore */ }
        }
    });
}

/** Executa <script> após injetar HTML via jQuery .html() */
function stInjectActionPageHtml(html) {
    stCnfDataTableInitToken += 1;
    if (stCnfDataTableInitTimer) {
        clearTimeout(stCnfDataTableInitTimer);
        stCnfDataTableInitTimer = null;
    }
    stDestroyActionPageTinyMce();
    var $wrap = $('<div>').html(html);
    stEnsurePageStylesheets($wrap);
    stDestroyActionPageDataTables();
    $("#action-page").html($wrap.html());
    $("#action-page").find("script").each(function () {
        if (this.src) {
            var src = this.src;
            var absSrc = src;
            try {
                absSrc = new URL(src, window.location.href).href.split('#')[0];
            } catch (e) {}
            var safeKey = absSrc.replace(/[^a-zA-Z0-9]/g, '_');
            if ($('script[data-st-page-src="' + safeKey + '"]').length) {
                return;
            }
            $.getScript(src).done(function () {
                $('<script>', { type: 'text/javascript' }).attr('data-st-page-src', safeKey).appendTo('head');
            });
            return;
        }
        var code = this.textContent || this.innerText || "";
        if (code.trim()) {
            $.globalEval(code);
        }
    });
    if (typeof window.stBkoOnPageReady === "function" && $("#content-bko").length) {
        window.stBkoOnPageReady();
    }
    if ($("#action-page .st-chat-workspace--sol").length && typeof window.inChat === "function") {
        window.requestAnimationFrame(function () {
            if ($("#action-page .st-chat-workspace--sol").length && typeof window.inChat === "function") {
                window.inChat();
            }
        });
    }
    if ($("#content-bko .st-chat-workspace--bko").length && typeof window.inChat === "function") {
        window.requestAnimationFrame(function () {
            if ($("#content-bko .st-chat-workspace--bko").length && typeof window.inChat === "function") {
                window.inChat();
            }
        });
    }
    if ($("#dashboard.st-dash-cha-workspace").length && typeof window.stDashChaRevealAfterLoad === "function") {
        window.stDashChaRevealAfterLoad();
    }
}

$(document).ready(function () {

    // Navegação principal: usa actionPageNav para não ser sobrescrita por páginas carregadas em #action-page
    function actionPage(action, sec) {
        if (isActionLoading) return;
        if (typeof window.stChatSolIsEnteringChat === 'function' && window.stChatSolIsEnteringChat()) {
            return;
        }
        if (action === 'chat-ate' && window.stChatOpen && typeof window.stChatOpen.openChatAteFast === 'function') {
            stopDashboardProcessing();
            isActionLoading = true;
            setMenuEnabled(false);
            window.stChatOpen.openChatAteFast(false);
            isActionLoading = false;
            setMenuEnabled(true);
            return;
        }
        stopDashboardProcessing();
        isActionLoading = true;
        actionRetryCount = 0;
        setMenuEnabled(false);
        if (action === 'chat-ate' && window.stChatOpen) {
            $("#action-page").html(stChatOpen.loaderHtml('Abrindo chat', 'Conectando você ao atendente...'));
        } else {
            $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        }

        function doRequest() {
            if (currentActionRequest) currentActionRequest.abort();
            currentActionRequest = $.ajax({
                url: "action.php",
                type: "POST",
                data: { action: action, sec: sec },
                timeout: ACTION_TIMEOUT_MS,
                success: function (valor) {
                    isActionLoading = false;
                    setMenuEnabled(true);
                    setMenuActive(action, sec);
                    stInjectActionPageHtml(valor);
                    if (action == 'dash-idx') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Chat</h3>'); }
                    if (action == 'dash-fila') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Dashboard</h3>'); }
                    if (action == 'gov-analytics') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Governança</h3>'); }
                    if (action == 'ia-insights') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Insights IA</h3>'); }
                    if (action == 'dash-inicio') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Início</h3>'); }
                    if (action == 'rel-dash') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Relatórios</h3>'); }
                    if (action == 'rel-ind') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Indicadores</h3>'); }
                    if (action == 'rel-fila') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Fila</h3>'); }
                    if (action == 'hist-dash') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Histórico</h3>'); }
                    if (action == 'hist-pend') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Pendências</h3>'); }
                    if (action == 'com-idx') { $("#indice-idx-men").html('<h3><i class="fas fa-arrow-right"></i> Comunicação</h3>'); }
                    // Atualização automática completa do dashboard de filas desabilitada.
                    // Mantemos apenas a limpeza do intervalo, se existir.
                    if (refreshDash) { clearInterval(refreshDash); refreshDash = null; }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // Ignora erros gerados por abortos intencionais ao trocar de página
                    if (textStatus === "abort") {
                        return;
                    }

                    console.error("Falha ao carregar action.php", {
                        action: action,
                        sec: sec,
                        status: jqXHR ? jqXHR.status : null,
                        textStatus: textStatus,
                        error: errorThrown
                    });

                    if (actionRetryCount < ACTION_MAX_RETRY) {
                        actionRetryCount++;
                        doRequest();
                    } else {
                        isActionLoading = false;
                        setMenuEnabled(true);
                        $("#action-page").html(
                            '<div style="padding:20px;text-align:center;">' +
                            '<p>Não foi possível carregar a página selecionada.<br>Verifique sua conexão com a internet ou reinicie a conexão caso o problema persista.</p>' +
                            '<button id="btnRetryAction" class="btn btn-secondary">Tentar novamente</button>' +
                            '</div>'
                        );
                        $("#btnRetryAction").off("click").on("click", function () { actionPage(action, sec); });
                    }
                }
            });
        }
        doRequest();
    }
    window.actionPageNav = actionPage;

    //IDX - DEMANDAS (sempre usa actionPageNav para não ser sobrescrito por páginas carregadas)
    $(document).on("click", "#dash-idx", function (e) { e.preventDefault(); window.actionPageNav('dash-idx', 'idx'); });
    $(document).on("click", "#dash-fila", function (e) { e.preventDefault(); window.actionPageNav('dash-fila', 'idx'); });
    $(document).on("click", "#gov-analytics", function (e) { e.preventDefault(); window.actionPageNav('gov-analytics', 'idx'); });
    $(document).on("click", "#ia-insights", function (e) { e.preventDefault(); window.actionPageNav('ia-insights', 'idx'); });
    $(document).on("click", "#rel-dash", function (e) { e.preventDefault(); window.actionPageNav('rel-dash', 'idx'); });
    $(document).on("click", "#rel-ind", function (e) { e.preventDefault(); window.actionPageNav('rel-ind', 'idx'); });
    $(document).on("click", "#rel-fila", function (e) { e.preventDefault(); window.actionPageNav('rel-fila', 'idx'); });
    $(document).on("click", "#hist-dash", function (e) { e.preventDefault(); window.actionPageNav('hist-dash', 'idx'); });
    $(document).on("click", "#hist-pend", function (e) { e.preventDefault(); window.actionPageNav('hist-pend', 'idx'); });
    $(document).on("click", "#com-idx", function (e) { e.preventDefault(); window.actionPageNav('com-idx', 'idx'); });

    $("#dash-ate").click(function () { location.reload(); });
    $("#dash-cha").click(function () { location.reload(); });


    //location.reload();
    //USU - USUARIOS
    //$("#dash-usu").click(function(){actionPage('dash', 'usu');});
    //$("#cad-usu").click(function(){actionPage('cad-usu', 'usu');});
    $("#pass").click(function () { window.actionPageNav('pass', 'usu'); });



    //CNF - CONTRATOS
    $("#dash-cnf").click(function () { window.actionPageNav('dash', 'cnf'); });
    $("#cad-ctt-cnf").click(function () { window.actionPageNav('cad-ctt', 'cnf'); });
    $("#cad-reg-cnf").click(function () { window.actionPageNav('cad-reg', 'cnf'); });
    $("#cad-emp-cnf").click(function () { window.actionPageNav('cad-emp', 'cnf'); });
    $("#cad-age-cnf").click(function () { window.actionPageNav('cad-age', 'cnf'); });
    $("#cad-ass-cnf").click(function () { window.actionPageNav('cad-ass', 'cnf'); });
    $("#cad-pri-cnf").click(function () { window.actionPageNav('cad-pri', 'cnf'); });
    $("#cad-fil-cnf").click(function () { window.actionPageNav('cad-fil', 'cnf'); });
    $("#cad-men-cnf").click(function () { window.actionPageNav('cad-men', 'cnf'); });
    $("#cad-usu-cnf").click(function () { window.actionPageNav('cad-usu', 'cnf'); });
    $("#cad-faq-cnf").click(function () { window.actionPageNav('cad-faq', 'cnf'); });
    $("#log-acess-cnf").click(function () { window.actionPageNav('log-acess-cnf', 'cnf'); });
    $("#cnf-ia-cnf").click(function () { window.actionPageNav('cnf-ia', 'cnf'); });
    $("#res-base").click(function () { window.actionPageNav('res-base', 'cnf'); });





    $("#sair").click(function () {
        Swal.fire({
            title: 'Sair',
            text: "Tem certeza que deseja realizar o logout do sistema?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EE3F60',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, realizar logout!',
            cancelButtonText: 'Não!'
        }).then((result) => {
            if (result.isConfirmed) {
                logout('sair');
            }
        })

    });

    function logout(action) {
        $.post("logout.php",
            {
                action: action
            },
            function (valor) {
                $("#logout").html(valor);
            });
    }

});

function actionPageScore(action, sec, user) {
    if (isActionLoading) {
        return;
    }
    isActionLoading = true;
    actionRetryCount = 0;
    setMenuEnabled(false);

    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');

    if (currentActionRequest) {
        currentActionRequest.abort();
    }

    currentActionRequest = $.ajax({
        url: "action.php",
        type: "POST",
        data: { action: action, sec: sec, user: user },
        timeout: ACTION_TIMEOUT_MS,
        success: function (valor) {
            isActionLoading = false;
            setMenuEnabled(true);
            setMenuActive("my-score", sec);
            stInjectActionPageHtml(valor);
        },
        error: function () {
            isActionLoading = false;
            setMenuEnabled(true);
            $("#action-page").html(
                '<div style="padding:20px;text-align:center;">' +
                '<p>Não foi possível carregar o score.</p>' +
                '</div>'
            );
        }
    });
}

