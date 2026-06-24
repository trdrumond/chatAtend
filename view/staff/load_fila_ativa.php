<?php
include("../cnf/session.php");


//depurador($_POST);

// Sanitiza ID de fila antes de montar a cláusula
$idFila = isset($_POST['fila']) ? (int)$_POST['fila'] : 0;

$sql = "SELECT id_fila_chat, protocolo, hora_registro, tempo_decorrido, id_fila, nome_fila, titulo_assunto, nome_ate, agencia, municipio from fila_atual";
if ($idFila != 0) {
    $sql .= " where id_fila=" . $idFila;
}

$sql .= " order by hora_registro asc";



//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
?>
<style>
    .pointer {
        cursor: pointer;
    }
</style>
<?php
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$qtdFila = count($rows);
?>
<div class="dash-fila-panel">
    <p class="dash-fila-panel__meta">
        <span><?= $qtdFila ?> <?= $qtdFila === 1 ? 'atendimento na fila' : 'atendimentos na fila' ?></span>
        <span class="dash-fila-panel__meta-sep" aria-hidden="true">·</span>
        <span>Atualização a cada 60s</span>
    </p>
<?php if ($qtdFila > 0) { ?>
    <div class="dash-fila-table-wrap">
        <table id="tbl_rel" class="table table-hover table-striped table-sm dash-fila-table">
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th class="text-center">Local</th>
                    <th class="text-center">Fila</th>
                    <th class="text-center">Tempo de espera</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) { ?>
                <tr title="<?= htmlspecialchars($row['titulo_assunto']); ?>" class="pointer">
                    <td class="dash-fila-table__protocol"><?= htmlspecialchars($row['protocolo']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['municipio']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['nome_fila']); ?></td>
                    <td class="text-center">
                        <div
                            id="tempo_<?= $idFila; ?>_<?= $row['id_fila_chat']; ?>"
                            class="tempo-fila"
                            data-fila="<?= $idFila; ?>"
                            data-chat="<?= $row['id_fila_chat']; ?>"
                            data-horario="<?= $row['hora_registro']; ?>">
                            <?= htmlspecialchars($row['tempo_decorrido']); ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } else { ?>
    <div class="dash-fila-empty">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        <p class="dash-fila-empty__title">Fila vazia</p>
        <p class="dash-fila-empty__sub">Nenhum atendimento aguardando no momento.</p>
    </div>
<?php } ?>
</div>

<script>
    (function() {
        var filaId = <?= $idFila; ?>;
        var dataHoje = '<?= date('Y-m-d'); ?>';

        function formatarTempo(segundos) {
            if (segundos < 0) segundos = 0;
            var h = Math.floor(segundos / 3600);
            var m = Math.floor((segundos % 3600) / 60);
            var s = segundos % 60;

            var hh = (h < 10 ? '0' : '') + h;
            var mm = (m < 10 ? '0' : '') + m;
            var ss = (s < 10 ? '0' : '') + s;
            return hh + ':' + mm + ':' + ss;
        }

        function atualizarTempoFila() {
            var agora = new Date();

            $('.tempo-fila[data-fila="' + filaId + '"]').each(function() {
                var $el = $(this);
                var horario = $el.data('horario');
                if (!horario) {
                    return;
                }

                // Se vier só HH:MM:SS, completa com a data de hoje
                var textoDataHora = horario;
                if (horario.length <= 8) {
                    textoDataHora = dataHoje + ' ' + horario;
                }

                // Normaliza para formato compatível com Date
                textoDataHora = textoDataHora.replace(' ', 'T');
                var inicio = new Date(textoDataHora);
                if (isNaN(inicio.getTime())) {
                    return;
                }

                var diffMs = agora - inicio;
                var diffSeg = Math.floor(diffMs / 1000);
                $el.text(formatarTempo(diffSeg));
            });
        }

        // Evita múltiplos intervalos para a mesma fila
        if (window['tempoFilaInterval_' + filaId]) {
            clearInterval(window['tempoFilaInterval_' + filaId]);
        }

        // Atualiza imediatamente e depois a cada 30s (apenas no frontend, sem ir ao servidor)
        atualizarTempoFila();
        window['tempoFilaInterval_' + filaId] = setInterval(atualizarTempoFila, 30000);
    })();
</script>