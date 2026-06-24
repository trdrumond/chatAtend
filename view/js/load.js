/**
 * Carrega filas do contrato no <select> (mesmo fluxo do relatório).
 * @param {string|number} contrato
 * @param {string} selectId - seletor jQuery do campo fila
 * @param {object} [opts]
 * @param {string|number} [opts.filaAlvo]
 * @param {string} [opts.labelVazio='Todas as filas']
 * @param {function} [opts.onDone]
 */
function stLoadFilasPorContrato(contrato, selectId, opts) {
    opts = opts || {};
    var $fila = $(selectId);
    var labelVazio = opts.labelVazio || 'Todas as filas';
    var onDone = typeof opts.onDone === 'function' ? opts.onDone : null;

    if (!contrato) {
        $fila.html('<option value="">' + labelVazio + '</option>');
        if (onDone) {
            onDone();
        }
        return;
    }

    $fila.html('<option value="">Carregando filas...</option>');
    $.post('staff/load_rel_filas.php', { contrato: contrato }, function (valor) {
        $fila.html(valor);
        $fila.find('option[value=""]').first().text(labelVazio);
        if (opts.filaAlvo) {
            $fila.val(String(opts.filaAlvo));
        }
        if (onDone) {
            onDone();
        }
    }).fail(function () {
        $fila.html('<option value="">' + labelVazio + '</option>');
        if (onDone) {
            onDone();
        }
    });
}
