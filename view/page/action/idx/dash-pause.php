<?php include_once("cnf/session.php");
include_once('cnf/rotina_pendencia.php');



$userIdPause = (int) ($_SESSION['dados']['id_user'] ?? 0);

$sql="SELECT user_id, contrato_id, agencia_id, fila_id, data_hora, acao from tbl_log_atendimento where user_id=? order by data_hora desc limit 1";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$userIdPause]);
$infoLog = $stmt->fetch( PDO::FETCH_ASSOC );

$sql="SELECT date_format(hora_in, '%Y-%m-%d %H:%i:%s') as hora_in, date_format(now(), '%Y-%m-%d %H:%i:%s') as server_now from tbl_pause where date_format(hora_in, '%Y-%m-%d')=curdate() and hora_out is null and user_id=? order by hora_in asc limit 1";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$userIdPause]);
$pausa = $stmt->fetch( PDO::FETCH_ASSOC );
$horaInPausaJs = isset($pausa['hora_in']) ? $pausa['hora_in'] : '';
$serverNowJs = isset($pausa['server_now']) ? $pausa['server_now'] : date('Y-m-d H:i:s');

if($horaInPausaJs !== '' && $infoLog['acao']!='Pausa'){
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Pausa');
}

?>
<script>
    /* Na tela de pausa, eventos do WebSocket (flagBko) não devem recarregar esta tela */
    if (typeof loadChatIn !== 'undefined') { loadChatIn = undefined; }
    if (window._intervalPausa) { clearInterval(window._intervalPausa); window._intervalPausa = null; }
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" crossorigin="anonymous"></script>

<script type="text/javascript">

    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        $.post("action.php",
        {
            action: action, sec: sec
        },
        function (valor) {
            $("#action-page").html(valor);
        });
    }

</script>

<style>
    #feedback {
        width: 100%;
        margin: auto;
        text-align: center;
        height: 200px;
        font-size: 50px;
        color: #CCCCCC;
    }
    #tempo-pausa {
        width: 100%;
        margin: 15px auto;
        text-align: center;
        font-size: 56px;
        font-weight: bold;
        font-variant-numeric: tabular-nums;
        color: #8C0000;
        letter-spacing: 2px;
    }
    #tempo-pausa-label {
        font-size: 16px;
        color: #666;
        margin-bottom: 5px;
    }
</style>

<div id="fila">
    <center>
        <h1>PAUSA REGISTRADA</h1>
        <div id="tempo-pausa-label">Tempo em pausa</div>
        <div id="tempo-pausa">00:00:00</div>
        <button type="button" id="fim_pausa" class="btn btn-secondary">Voltar aos Atendimentos</button>
        <div class="content-7-line" id="feedback"></div>
    </center>

</div>

<!-- <div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div> -->

<script>
        (function() {
            var horaInPausa = '<?php echo addslashes($horaInPausaJs); ?>';
            var serverNowStr = '<?php echo addslashes($serverNowJs); ?>';
            if (!horaInPausa) {
                $('#tempo-pausa-label').text('Pausa já finalizada. Retornando aos atendimentos...');
                $('#fim_pausa').prop('disabled', true);
                setTimeout(function(){ document.location.reload(true); }, 1500);
                return;
            }

            var inicioMs = new Date(horaInPausa.replace(' ', 'T')).getTime();
            var serverNowMs = new Date(serverNowStr.replace(' ', 'T')).getTime();
            var offsetMs = Date.now() - serverNowMs;

            function fmtNum(n) {
                return (n < 10 ? '0' : '') + n;
            }

            function atualizarRelogioPausa() {
                var elTempo = document.getElementById('tempo-pausa');
                if (!elTempo) {
                    clearInterval(window._intervalPausa);
                    window._intervalPausa = null;
                    return;
                }
                var agoraServerMs = Date.now() - offsetMs;
                var diffMs = Math.max(0, agoraServerMs - inicioMs);
                var totalSeg = Math.floor(diffMs / 1000);
                var horas = Math.floor(totalSeg / 3600);
                var restoSeg = totalSeg % 3600;
                var min = Math.floor(restoSeg / 60);
                var seg = restoSeg % 60;
                var texto = fmtNum(horas) + ':' + fmtNum(min) + ':' + fmtNum(seg);
                elTempo.textContent = texto;
            }

            atualizarRelogioPausa();
            window._intervalPausa = setInterval(atualizarRelogioPausa, 1000);
        })();

            $('#fim_pausa').click(function(){
                Swal.fire({
                    title: 'Vamos voltar aos atendimentos?',
                    text: "Finalizar pausa para voltar aos atendimentos",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, voltar',
                    cancelButtonText: 'Não'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        if (window._intervalPausa) clearInterval(window._intervalPausa);
                        save();
                    }
                    })
            });

            function save(){
                var user_id = <?php echo (int) $_SESSION['dados']['id_user']; ?>;
                $('#fim_pausa').prop('disabled', true);
                $.post("staff/save_pause.php",
                    {
                        user_id
                    },
                function (valor) {
                    // A tela pode ter sido substituída durante a requisição; o retorno precisa executar mesmo assim
                    if ($("#feedback").length) {
                        $("#feedback").html(valor);
                    } else {
                        $('body').append(valor);
                    }
                    setTimeout(function(){ document.location.reload(true); }, 2000);
                }).fail(function(){
                    $('#fim_pausa').prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Não foi possível finalizar a pausa',
                        text: 'Verifique sua conexão e tente novamente. Se persistir, atualize a página (F5).'
                    });
                });
            }


            function tempoPausa(horario){
                $.post("staff/tempo_atend.php",
                    { horario  },
                    function (valor) {
                            $("#feedback").html(valor);
                    }
                );
            }
</script>
<script type="text/javascript" src="js/load.js"></script>
