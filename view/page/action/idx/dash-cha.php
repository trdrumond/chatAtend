<?php

include("cnf/session.php");

include('cnf/rotina_pendencia.php');

require_once __DIR__ . '/../cnf/_cnf_ui.php';



/** @var array<string, mixed> $infoUser */

/** @var PDO $PDO */

?>



            <div id="load_conn" class="st-dash-cha-loading">

                <img src="img/loading.gif" alt="Carregando..." width="64">

                <span>Aguardando conexão...</span>

            </div>



<script type="text/javascript">



    function actionPage(action, sec, status){

        loadChatIn = '';

        var mensagem = '';

        if(typeof conn === 'undefined'){

            //setTimeout(function(){ actionPage(action, sec);},  0);

            actionPage(action, sec);

        } else {

            if(conn.readyState === 1){

                if(status == 2){

                    mensagem = 'DIRECIONANDO PARA CHAT EM ANDAMENTO...';

                }

                if(status == 1){

                    mensagem = 'DIRECIONANDO PARA FILA...';

                }

                if (window.stChatOpen) {
                    var loaderTitle = (action === 'chat-ate') ? 'Abrindo chat' : ((action === 'chat-fila') ? 'Entrando na fila' : 'Carregando');
                    var loaderSub = (action === 'chat-ate') ? 'Conectando ao atendente...' : ((action === 'chat-fila') ? 'Aguarde um instante...' : mensagem);
                    $("#action-page").html(stChatOpen.loaderHtml(loaderTitle, loaderSub));
                } else {
                    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"><br>'+mensagem+'</div>');
                }

                $.post("action.php",

                {

                    action: action, sec: sec

                },

                function (valor) {

                    if (typeof valor === 'string') {
                        valor = valor.replace(/ƒ/g, 'f');
                    }
                    if (typeof stInjectActionPageHtml === 'function') {
                        stInjectActionPageHtml(valor);
                    } else {
                        $("#action-page").html(valor);
                    }

                });

            } else {

                //setTimeout(function(){ actionPage(action, sec);},  0);

                actionPage(action, sec);

            }

        }

    }



</script>



<?php

    $ateId = (int) ($infoUser['id_user'] ?? 0);
    $stm = $PDO->prepare("SELECT protocolo, data_hora from tbl_chat_fila where status_fila=1 and ate_resp=?");
    $stm->execute([$ateId]);
    $infoFila = $stm->fetch(PDO::FETCH_ASSOC);

    if($infoFila['protocolo']!=''){

        echo "<script>var loadChatIn = function loadChatIn() { if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) { return; } setTimeout(function(){ actionPage('chat-fila', 'idx', 1); }, 100); }</script>";

    } else {



?>





    <div id="dashboard" class="st-dash-cha-workspace">

        <?php

                $sqlVer="SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, timediff(now(), data_hora) as te from tbl_chat_fila where (status_fila=".ST_FILA_NA_FILA." or ".stFilaSqlChamarSolicitante().") and ate_resp=?";

                //echo "<br>".$sqlVer;

                $stmt = $PDO->prepare($sqlVer);

                $result = $stmt->execute([$ateId]);

                $infFila = $stmt->fetch( PDO::FETCH_ASSOC );

                //var_dump($infFila);



                if($infFila){

                    if($infFila['status_fila']==ST_FILA_NA_FILA){

                        echo "<script>var loadChatIn = function loadChatIn() { if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) { return; } setTimeout(function(){ actionPage('chat-fila', 'idx', 1); }, 100); }</script>";

                    } else if(stFilaDeveChamarSolicitante((int)$infFila['status_fila'])){

                        echo "<script>window.stFilaSolIdFila = ".(int)$infFila['id_fila_chat'].";";
                        echo "var loadChatIn = function loadChatIn() { if (typeof stChatSolIsEnteringChat === 'function' && stChatSolIsEnteringChat()) { return; } if (typeof stChatSolWorkspaceActive === 'function' && stChatSolWorkspaceActive()) { loadChatIn = ''; return; } if (window.stChatOpen && typeof stChatOpen.isOpeningAte === 'function' && stChatOpen.isOpeningAte()) { return; } if (window.stChatOpen && typeof stChatOpen.openChatAteFast === 'function') { stChatOpen.openChatAteFast(); } else { actionPage('chat-ate', 'idx', 2); }};</script>";

                    }

                } else {







        ?>

            <div id="div_ope" class="st-dash-cha-main" style="display: none;">

                <div id="bloco_central" class="st-dash-cha-card st-dash-cha-bot">

                    <header class="st-dash-cha-card__head">

                        <h5 class="st-dash-cha-card__title"><i class="fas fa-robot" aria-hidden="true"></i> Atendimento virtual</h5>

                        <p class="st-dash-cha-card__sub">Converse com o assistente para iniciar seu atendimento na fila.</p>

                    </header>

                    <div class="st-dash-cha-bot__body">

                        <div id="st_dash_cha_bot_messages" class="st-dash-cha-bot__chat" role="log" aria-live="polite" aria-label="Conversa com o assistente virtual"></div>

                        <div id="st_dash_cha_bot_input_area" class="st-dash-cha-bot__input"></div>

                    </div>

                    <div id="feed_call_bot" class="st-dash-cha-feed" hidden></div>

                </div>

            </div>



            <div id="div_pend" class="st-dash-cha-side" style="display: none;">

                <script>

                    function reabrirProt(id_chat, protocolo){

                        Swal.fire({

                            title: 'Deseja reabrir o protoclo '+protocolo+'?',

                            text: "Esta ação o colocará no fim da fila e carregará o historico da conversa para o novo atendimento.",

                            icon: 'warning',

                            showCancelButton: true,

                            confirmButtonColor: '#3085d6',

                            cancelButtonColor: '#d33',

                            confirmButtonText: 'Sim, reabrir',

                            cancelButtonText: 'Não'

                            }).then((result) => {

                            if (result.isConfirmed) {

                                reabrir(id_chat);

                            }

                        })

                    }



                    function reabrir(id_chat){

                        var id_chat = id_chat;

                        $("#feed_call_bot").html('<br><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');

                        $.post("staff/save_recall.php",

                        {

                            id_chat

                        },

                        function (valor) {

                            //console.log(valor);

                            $("#feed_call_bot").html(valor);

                        });

                    }

                </script>

                <?php

                    $sqlVer="SELECT id_fila_chat, protocolo, status_fila, (SELECT nome_situacao from tbl_situacao_chat where id_situacao=status_fila) as nome_situacao, (SELECT id_chat from tbl_chat_info where fila_chat_id=id_fila_chat) as chat_id from tbl_chat_fila where (status_fila=7) and date_format(data_hora, '%Y-%m-%d')=curdate() and ate_resp=?";

                    //echo "<br>".$sqlVer;

                    $stmt = $PDO->prepare($sqlVer);

                    $result = $stmt->execute([$ateId]);

                    $pend = $stmt->fetchAll( PDO::FETCH_ASSOC );

                    if(count($pend)>0){

                ?>

                    <header class="st-dash-cha-side__head">

                        <i class="fas fa-history" aria-hidden="true"></i>

                        <span>Protocolos de hoje</span>

                    </header>

                    <div class="cnf-table-wrap st-dash-cha-pend-wrap">

                    <table class="table table-sm table-hover cnf-table st-dash-cha-pend-table">

                        <thead>

                            <tr>

                                <th>Protocolo</th>

                                <th class="text-center">Status</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                            for($x=0;$x<count($pend);$x++){

                                echo '<tr id="pend_'.$pend[$x]['id_fila_chat'].'" class="pointer">';

                                    echo '<td>'.$pend[$x]['protocolo'].'</td>';

                                    echo '<td><center>'.$pend[$x]['nome_situacao'].'</center></td>';

                                echo '</tr>';

                                ?>

                                    <script>

                                        $('#pend_<?=$pend[$x]['id_fila_chat'];?>').click(function(){

                                            //console.log('clicou');

                                            var id_chat = '<?=$pend[$x]['id_fila_chat'];?>';

                                            var chat_id = '<?=$pend[$x]['chat_id'];?>';

                                            var protocolo = '<?=$pend[$x]['protocolo'];?>';

                                            <?php if($pend[$x]['status_fila']==7){ ?>

                                                reabrirProt(id_chat, protocolo);

                                            <?php } else if($pend[$x]['status_fila']==3){ ?>

                                                abreDetail(chat_id);

                                            <?php } ?>

                                        });

                                    </script>

                                <?php

                            }

                        ?>

                        </tbody>

                    </table>

                    </div>



                <?php } ?>

            </div>







            <script>

                var loadChatIn = function loadChatIn(){

                    $('#div_ope').show();

                    $('#div_pend').show();

                    $('#load_conn').hide();

                    if (typeof window.stDashChaBotInit === 'function') {
                        window.stDashChaBotInit();
                    }

                    if(typeof loadChatIn !== 'undefined'){ loadChatIn = ''; }

                }

            </script>



            <?php } ?>



    </div>



    <script type="text/javascript" src="js/dash-cha-bot.js"></script>

    <?php } ?>



    <script type="text/javascript" src="js/load.js"></script>

    <script>

            function abreDetail(id){

                    //console.log(id);

                    $('#fundo_detail').fadeIn();

                    $.post("staff/load_hist_pend.php",

                        {

                            id

                        },

                        function (valor) {

                            //$('#info_detail').show('slow');

                            $('#div_detail').html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"></div>');

                            setTimeout(function(){

                                $('#div_detail').html(valor);

                            }, 500);





                        });

            }

    </script>

    <div id="fundo_detail" class="gw-modal-fundo">

        <div id="mod_detail" class="gw-modal-large">

        <h5>Informações do Chat</h5><br />

                <div class="row" id="div_detail">

                </div>

        <div class="close"><span>&times;</span></div>

        </div>

    </div>





    <script>

        $("#fundo_detail, .close").click(function(){ $("#fundo_detail").hide(); $("#div_detail").html(''); });

        $("#mod_detail").click(function(e){ e.stopPropagation(); });

    </script>

