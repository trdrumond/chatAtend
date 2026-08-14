<script>

    $('#dash-ate').show();

    $('#my-score').show();

    $('#hist-dash').show();

    $('#hist-pend').show();

    $('#com-idx').show();

    $('#sair').show();



    function actionPage(action, sec){

            $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"><br>DIRECIONANDO...</div>');

            if( typeof conn !== 'undefined' && conn.readyState === 1){

                $.post("action.php",

                {

                    action: action, sec: sec

                },

                function (valor) {

                    if (typeof stInjectActionPageHtml === 'function') {

                        stInjectActionPageHtml(valor);

                    } else {

                        $("#action-page").html(valor);

                    }

                });

            } else {

                setTimeout(function(){ actionPage(action, sec); }, 400);

            }

        }



</script>



<div id="load_conn" class="st-dash-cha-loading">

    <div class="st-chat-open__spinner" aria-hidden="true"></div>

    <span>Aguardando conexão...</span>

</div>



<?php

include("cnf/session.php");



/** @var array<string, mixed> $infoUser */

/** @var PDO $PDO */



require_once __DIR__ . '/../cnf/_cnf_ui.php';



$bkoId = (int) $infoUser['id_user'];
$contratoDash = (int) ($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);

$sql="SELECT ia.id_fila_chat, ia.protocolo, ia.ate_resp, ia.nome, ia.sobrenome, ia.indice, ia.motivo, ia.bko_resp"
    ." FROM infoAte ia"
    ." INNER JOIN tbl_chat_fila cf ON cf.id_fila_chat = ia.id_fila_chat"
    ." INNER JOIN tbl_chat_info ci ON ci.fila_chat_id = cf.id_fila_chat AND ci.status_chat = 1"
    ." WHERE ia.bko_resp=?"
    ." AND cf.contrato_id=?"
    ." AND ".str_replace('status_fila', 'cf.status_fila', stFilaSqlAtendimentoAtivo());

$stmt = $PDO->prepare( $sql );

$result = $stmt->execute([$bkoId, $contratoDash]);

$infoAte = $stmt->fetchAll( PDO::FETCH_ASSOC );

if (count($infoAte) < 1) {
    $infoAte = stBkoListarAtendimentosAtivos($PDO, (int)$infoUser['id_user'], (int)$infoUser['contrato_id']);
}



if(count($infoAte)<1){

    $sql="SELECT resp_id, contrato_id, date_disp from infoAtendimento where resp_id=?";

    $stmt = $PDO->prepare( $sql );

    $result = $stmt->execute([$bkoId]);

    $infoAtend = $stmt->fetch( PDO::FETCH_ASSOC );

    if($infoAtend['resp_id']==''){

        $sql="INSERT INTO tbl_tma_atend (resp_id, contrato_id, date_disp) VALUES (?, ?, now())";

        $stmt = $PDO->prepare( $sql );

        $result = $stmt->execute([$bkoId, (int) $infoUser['id_contrato']]);

        $infoAtend['date_disp'] = date('Y-m-d H:i:s');

    }

}



$sql="SELECT user_id, contrato_id, agencia_id, fila_id, data_hora, acao from tbl_log_atendimento where user_id=? order by data_hora desc limit 1";

$stmt = $PDO->prepare( $sql );

$result = $stmt->execute([$bkoId]);

$infoLog = $stmt->fetch( PDO::FETCH_ASSOC );



$sqlVer="SELECT user_id, pause_id, hora_in from tbl_pause where date_format(hora_in, '%Y-%m-%d')=curdate() and hora_out is null and user_id=?";

$stmt = $PDO->prepare($sqlVer);

$result = $stmt->execute([(int) $_SESSION['dados']['id_user']]);

$ver = $stmt->fetch( PDO::FETCH_ASSOC );

if($ver['user_id']!=''){

    echo "<script>var loadChatIn = function loadChatIn() {setTimeout(function() { actionPage('dash-pause', 'idx'); }, 0);}</script>";

} else {

    if($infoLog['acao']!='Disponivel' && $infoLog['acao']!='Tratamento'){

        logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Disponivel');

    }



    $stBkoActiveChats = [];

    if (count($infoAte) > 0) {

        foreach ($infoAte as $stIdx => $stAte) {

            $stIndDiv = ($stAte['indice'] != '') ? (int)$stAte['indice'] : ($stIdx + 1);

            $stBkoActiveChats[] = [

                'indice' => $stIndDiv,

                'protocolo' => $stAte['protocolo'],

                'id_fila_chat' => (int)($stAte['id_fila_chat'] ?? 0),

            ];

        }

    }

    $stBkoBootInd = count($infoAte) > 0

        ? (int)(($infoAte[0]['indice'] != '') ? $infoAte[0]['indice'] : 1)

        : 1;

?>

<script>

    window.qtdMax = <?= (int)$infoUser['multichat'] ?>;

    window.stBkoCfg = {

        userId: <?= (int)$infoUser['id_user'] ?>,

        filaId: <?= (int)$infoUser['fila_id'] ?>,

        contratoId: <?= (int)($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0) ?>,

        hasActiveAte: <?= count($infoAte) > 0 ? 'true' : 'false' ?>,

        activeChats: <?= json_encode($stBkoActiveChats, JSON_UNESCAPED_UNICODE) ?>

    };

    window.stBkoIndiceAtivo = <?= $stBkoBootInd ?>;

    (function () {

        var cfg = window.stBkoCfg;

        if (!cfg || !cfg.hasActiveAte || !cfg.activeChats || !cfg.activeChats.length) {

            return;

        }

        function bootRestore() {

            cfg.activeChats.forEach(function (item, idx) {

                setTimeout(function () {

                    if (typeof window.stBkoDivHasChat === 'function' && window.stBkoDivHasChat(item.indice)) {

                        return;

                    }

                    if (typeof actionPageChat === 'function' && item.protocolo) {

                        actionPageChat(item.indice, item.protocolo);

                    }

                }, 120 + (idx * 350));

            });

        }

        if (document.readyState === 'loading') {

            document.addEventListener('DOMContentLoaded', bootRestore);

        } else {

            bootRestore();

        }

    })();

</script>

<style>

    #action-page #content-bko .show,

    #action-page #content-bko .active {

        display: flex;

    }



    #action-page #content-bko .sec,

    #action-page #content-bko .none {

        display: none;

    }



    .close-chat {

        float: right;

        margin: 5px;

        cursor: pointer;

    }



    .blink_me {

        animation: blinker 1s linear infinite;

    }



    @keyframes blinker {

        50% {

            opacity: 0.5;

            background-color: #ff6d6d;

        }

    }

</style>



    <div id="dashboard">



            <?php

            if(count($infoAte)<1){ $qtd_atend=1; } else { $qtd_atend= count($infoAte);}

            ?>



            <div id="content-bko"

                 data-user-id="<?= (int)$infoUser['id_user'] ?>"

                 data-fila-id="<?= (int)$infoUser['fila_id'] ?>"

                 data-contrato-id="<?= (int)$infoUser['contrato_id'] ?>"

                 data-has-active="<?= count($infoAte) > 0 ? '1' : '0' ?>">

                <div id="bloco-bko">

                    <button onclick="addAba();" id="btn-add-tab" class="btn btn-outline-secondary" disabled>+</button>

                    <?php

                    for($ind=1; $ind<=$qtd_atend;$ind++){

                        $indDiv = ($infoAte[$ind-1]['indice']!='') ? $infoAte[$ind-1]['indice'] : $ind ;

                        if(count($infoAte)==0) {$title = 'Aguardando...';} else { $title = ucwords((strtolower($infoAte[$ind-1]['nome'])).' '.(strtolower($infoAte[$ind-1]['sobrenome'][0])))."."; }

                    ?>

                    <span id="title-<?=$indDiv; ?>" class="tab <?php if($indDiv == 1 || $qtd_atend == 1 ){ echo 'active-tab'; } else  { echo ''; } ?>" onclick="selAba(<?=$indDiv; ?>)"><?=$title;?></span>

                    <?php } ?>

                </div>

                <div id="principal">

                    <?php

                        for($ind=1; $ind<=$qtd_atend;$ind++){

                            $indDiv = ($infoAte[$ind-1]['indice']!='') ? $infoAte[$ind-1]['indice'] : $ind ;

                    ?>

                        <div id="div-<?=$indDiv; ?>" class="div <?php if($indDiv == 1  || $qtd_atend == 1){ echo 'show'; } else  { echo 'none'; } ?>">

                            <?php

                            if (count($infoAte) > 0) {

                                echo st_chat_open_loader_html('Abrindo chat', 'Aguarde um instante...');

                            } else {

                                $_POST['indice'] = $indDiv;

                                include __DIR__ . '/../../../staff/load_fila_bko.php';

                            }

                            ?>

                        </div>

                    <?php } ?>

                </div>

            </div>



    </div>



    <script src="js/tabs.js?<?= time() ?>"></script>



<?php }  ?>

