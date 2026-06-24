var stDadosIdxInFlight = null;
window.stDadosIdxInFlight = null;

var stDadosIdxTimer = null;

function dadosIdx(resp_id, fila_id, contrato_id) {
    if (typeof window.stChatSolOpeningAte !== 'undefined' && window.stChatSolOpeningAte) {
        return;
    }
    if (window.stChatOpen && typeof window.stChatOpen.isOpeningAte === 'function' && window.stChatOpen.isOpeningAte()) {
        return;
    }
    if (stDadosIdxInFlight && stDadosIdxInFlight.readyState !== 4) {
        return;
    }

    stDadosIdxInFlight = $.post("staff/load_dados_dash_ind.php", {
        resp_id: resp_id,
        fila_id: fila_id,
        contrato_id: contrato_id
    }, function (valor) {
        stDadosIdxInFlight = null;
        window.stDadosIdxInFlight = null;

        var $container = $("#dadosDashInd");
        $container.html(valor);
        $container.find("script").each(function () {
            var code = this.text || this.textContent || "";
            if (code.trim() && $.globalEval) {
                try {
                    $.globalEval(code);
                } catch (err) {
                    console.error("dadosIdx script:", err);
                }
            }
        });
    }).fail(function () {
        stDadosIdxInFlight = null;
        window.stDadosIdxInFlight = null;
    });

    window.stDadosIdxInFlight = stDadosIdxInFlight;
}
