<?php
    include("../cnf/session.php");

    $idChat = (int) ($_POST['id'] ?? 0);
    if ($idChat < 1) {
        return;
    }

    $sql="SELECT a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_id, a.contrato_id, b.rem_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_chat) as nome_rem_chat, b.dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as nome_dest_chat, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.dest_id, a.msg, b.fila_chat_id, b.token_chat from tbl_chat_msg a, tbl_chat_info_secondary b where a.chat_id=b.id_chat and id_chat=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$idChat]);
    $infoChatMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );
    if(count($infoChatMsg)==0){
        $sql="SELECT a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_id, a.contrato_id, b.rem_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_chat) as nome_rem_chat, b.dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as nome_dest_chat, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.dest_id, a.msg, b.fila_chat_id, b.token_chat from tbl_chat_msg_secondary a, tbl_chat_info_secondary b where a.chat_id=b.id_chat and id_chat=?";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$idChat]);
        $infoChatMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );
    }

    $destChatId = (int) ($infoChatMsg[0]['dest_chat'] ?? 0);
    $filaChatId = (int) ($infoChatMsg[0]['fila_chat_id'] ?? 0);

    $sql="SELECT concat(nome, ' ', sobrenome) as nome_completo, agencia_id, (SELECT nome_agencia from tbl_agencia where id_agencia=agencia_id) as agencia from tbl_user where id_user=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$destChatId]);
    $infoSol = $stmt->fetch( PDO::FETCH_ASSOC );

    $sql="SELECT id_fila_chat, protocolo, data_hora, fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=fila_id) as fila, assunto_id, (SELECT titulo_assunto from tbl_assunto where id_assunto=assunto_id) as assunto, ate_resp, bko_resp, (SELECT concat(nome, ' ', sobrenome) as nome_completo from tbl_user where id_user=bko_resp) as nome_bko, date_format(data_hora, '%d/%m/%Y') as hora_reg, date_format(hora_inicio, '%H:%i:%s') as inicio, date_format(hora_fim, '%H:%i:%s') as fim, ta, te, status_fila, (SELECT nome_situacao from tbl_situacao_chat where id_situacao=status_fila) as nome_status, motivo from tbl_chat_fila_secondary where id_fila_chat=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$filaChatId]);
    $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );

    $sql="SELECT id_pend, fila_id, chat_id, ate_resp, bko_resp, data_hora, situacao_id, motivo, info_fim, data_hora_fim, data_hora_visualizacao from tbl_pend_info where chat_id=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$filaChatId]);
    $infoPend = $stmt->fetch( PDO::FETCH_ASSOC );

    if (!is_array($infoChatMsg) || $infoChatMsg === []) {
        echo '<p class="text-warning">Histórico não encontrado.</p>';
        return;
    }
    if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($infoChatMsg[0]['contrato_id'] ?? 0))) {
        echo '<p class="text-danger">Contrato não autorizado.</p>';
        return;
    }
    if (!is_array($infoSol)) {
        $infoSol = [];
    }
    if (!is_array($infoChat)) {
        $infoChat = [];
    }
    if (!is_array($infoPend)) {
        $infoPend = [];
    }

    if($nivel_usu==5 && ($infoPend['ate_resp'] ?? '')==$infoUser['id_user']){
        $sql="SELECT id_pend, data_hora_fim, data_hora_visualizacao from tbl_pend_info where chat_id=?";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$filaChatId]);
        $infoPendAte = $stmt->fetch( PDO::FETCH_ASSOC );
        if(is_array($infoPendAte) && ($infoPendAte['data_hora_fim'] ?? '')!='' && ($infoPendAte['data_hora_visualizacao'] ?? '')==''){
            $idPend = (int) ($infoPendAte['id_pend'] ?? 0);
            $sql="UPDATE tbl_pend_info SET data_hora_visualizacao=now() where id_pend=?";
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute([$idPend]);
            echo "<script>setTimeout(function() {sendBko();}, 500);</script>";
        }
    }

?>
<style>
#info_chat {
    height: 75px;
    width: 95%;
    margin: auto;
    text-align: left;
}

.class_info {
    font-size: 10px;
    margin: 2px;
    background-color: #EEEEEE;
    border-radius: 5px;
    padding-left: 5px;
}

.class_mon {
    font-size: 10px;
    margin: 2px;
    background-color: #29BFFF;
    color: #FFFFFF;
    border-radius: 5px;
    padding-left: 5px;
}

.class_div {
    width: 38%;
    float: left;
    margin: 2px;
}

.class_div_ {
    width: 17%;
    float: left;
    margin: 2px;
}
</style>

<div id="info_chat">
    <div class="class_div">
        <div class="class_info" id="solicitante_id"><strong>Solicitante: </strong><?= stHtml($infoSol['nome_completo'] ?? '') ?>
            <?php if($infoUser['nivel_id']<=2 && ($infoChat['status_fila']==3)){ ?>
            <button id="btn_edit_sol"><i class="fas fa-edit"></i></button>
            <?php } ?>
        </div>
        <div class="class_info"><strong>Agência: </strong><?= stHtml($infoSol['agencia'] ?? '') ?></div>
        <div class="class_info"><strong>Data: </strong><?= stHtml($infoChat['hora_reg'] ?? '') ?></div>
        <div class="class_info"><strong>Tempo Espera: </strong><?= stHtml($infoChat['te'] ?? '') ?></div>
    </div>

    <div class="class_div">
        <div class="class_info"><strong>Protocolo: </strong><?= stHtml($infoChat['protocolo'] ?? '') ?></div>
        <div class="class_info" id="bko_id"><strong>Backoffice: </strong><?= stHtml($infoChat['nome_bko'] ?? '') ?>
            <?php if($infoUser['nivel_id']<=2 && ($infoChat['status_fila']==3)){ ?>
            <button id="btn_edit_bko"><i class="fas fa-edit"></i>
                <?php } ?>
        </div>
        <div class="class_info"><strong>Assunto: </strong><?= stHtml($infoChat['assunto'] ?? '') ?></div>
        <div class="class_info"><strong>Tempo Atend.: </strong><?= stHtml($infoChat['ta'] ?? '') ?></div>
    </div>

    <div class="class_div_">
        <div class="class_info"><strong>Início: </strong><?= stHtml($infoChat['inicio'] ?? '') ?></div>
        <div class="class_info"><strong>Fim: </strong><?= stHtml($infoChat['fim'] ?? '') ?></div>
        <div class="class_info"><strong>Status: </strong><?= stHtml($infoChat['nome_status'] ?? '') ?></div>

        <?php
                    if(($infoPend['data_hora_fim'] ?? '')==''){
                        if($infoUser['nivel_id']==4 && ($infoChat['bko_resp'] ?? '')==$infoUser['id_user']){
                            echo '<div class="class_mon pointer" onclick="infoPend('.(int)$idChat.', '.(int)($infoChatMsg[0]['contrato_id'] ?? 0).', '.(int)($infoChat['fila_id'] ?? 0).')"><center>Pendência</center></div>';
                        }
                    }

                ?>

    </div>
</div>

<script>
$("#btn_edit_sol").click(function() {
    //console.log('click');
    editSolicitante(<?= (int) ($infoChat['ate_resp'] ?? 0) ?>, <?= (int) ($infoChat['id_fila_chat'] ?? 0) ?>)
});

function editSolicitante(id_solicitante, fila_chat_id) {
    var div = '#solicitante_id';
    $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

    $.post("staff/load_pend_alt_sol.php", {
            id_solicitante,
            fila_chat_id
        },
        function(valor) {
            $(div).html(valor);
        });
}

$('#btn_edit_bko').click(function() {
    editBko(<?= (int) ($infoChat['bko_resp'] ?? 0) ?>, <?= (int) ($infoChat['id_fila_chat'] ?? 0) ?>);
});

function editBko(id_bko, fila_chat_id) {
    var div = '#bko_id';
    $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

    $.post("staff/load_pend_alt_bko.php", {
            id_bko,
            fila_chat_id
        },
        function(valor) {
            $(div).html(valor);
        });
}

function infoPend(id_chat, contrato, fila) {

    var id_chat, contrato, fila;

    var div_chat = '#chat_' + id_chat;
    //$(div_chat).removeClass('chat-div');
    //$(div_chat).addClass('chat-div-meio');

    var div_mon = '#monitoria_' + id_chat;
    //$(div_mon).removeClass('mon_ini');
    //$(div_mon).addClass('mon_show');
    //files_deposit
    $('#dep_file').hide();
    $(div_mon).removeClass('mon_ini');
    $(div_mon).addClass('mon_show');

    $(div_mon).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

    $.post("staff/load_pend_info.php", {
            id_chat,
            contrato,
            fila
        },
        function(valor) {
            $(div_mon).html(valor);
        });
}

function closePend(id_chat) {
    var id_chat;
    var div_chat = '#chat_' + id_chat;
    //$(div_chat).removeClass('chat-div-meio');
    //$(div_chat).addClass('chat-div');

    var div_mon = '#monitoria_' + id_chat;
    //$(div_mon).removeClass('mon_show');
    //$(div_mon).addClass('mon_ini');
    $('#dep_file').show();
    $(div_mon).removeClass('mon_show');
    $(div_mon).addClass('mon_ini');
}
</script>

<div id="chat">
    <link rel='stylesheet' type='text/css' href='chat/assets/css/style.css?<?= time() ?>'>
    </style>

    <style>
    .chat-content {
        height: 290px;
        width: 99%;
    }

    .chat-div {
        width: 68%;
        float: left;
    }

    .chat-div-meio {
        width: 68%;
        float: left;
    }

    .mon_ini {
        display: none;
    }

    .mon_show {
        width: 28%;
        height: 320px;
        overflow: auto;
        float: left;
    }

    .dep_file {
        width: 28%;
        height: 320px;
        overflow: auto;
        float: left;
    }
    </style>
    <div id="chat_<?=$idChat; ?>" class="chat-div">

        <section class="chat-content" id="chat_content_0_">
            <?php if($infoChat['motivo']!=''){ ?>
            <div class='motivo'><strong>Motivo:</strong>
            <p><?= stHtml($infoChat['motivo'] ?? '') ?></p>
            </div>
            <?php } ?>
            <?php
                            for($z=0;$z<count($infoChatMsg);$z++){
                                $ls=$infoChatMsg[$z];

                                $class = ($ls['rem_id']==$ls['rem_chat']) ? 'me' : 'other';
                                if($ls['rem_id']==0){

                                    $h5="";
                                    $class = 'sys';
                                } else {
                                    $h5 = "<h5>".stHtml($ls['nome_rem'])."</h5>";
                                }
                                $imgSrc = (string) ($ls['img'] ?? '');
                                if ($imgSrc === '' || preg_match('#^\s*javascript:#i', $imgSrc)) {
                                    $imgSrc = 'img/perfil.fw.png';
                                }
                                $msgHtml = stChatRenderPostedMsg((string) ($ls['msg'] ?? ''), (int) ($ls['chat_id'] ?? 0), $PDO);
                                echo "<div class='$class'>
                                        <img src='".stHtml($imgSrc)."'>
                                        <div class='text'>
                                            ".$h5."
                                            <div class='paragrafo'>".$msgHtml."</div>
                                            <div class='dataHora'>".stHtml($ls['hora_msg'])."</div>
                                        </div>
                                    </div>";
                            }
                        ?>
        </section>
        <div id="dig_0" class="dig" style="width: 100% !important;"><?php if($infoChat['status_fila']==2){?><center>
                Últimas mensagens até <?=date('d/m/Y H:i:s')?></center><?php } ?></div>
        <?php
                            $tokenChat = (string) ($infoChatMsg[0]['token_chat'] ?? '');
                            $sql="SELECT count(*) as qtd from tbl_chat_files where token_chat=?";
                            $stmt = $PDO->prepare($sql);
                            $result = $stmt->execute([$tokenChat]);
                            $infoFiles = $stmt->fetch( PDO::FETCH_ASSOC );
                            //depurador($infoFiles);
                            //if(count($infoFiles['qtd'])>0){
                        ?>
        <style>
        .file_chat {
            margin-top: 5px;
            margin-bottom: 5px;
            margin-left: 5px;
            margin-right: 5px;
            color: #717377;
            text-align: center;
            float: left;
        }

        .file_chat>a {
            color: #717377;
        }
        </style>
        <script>
        loadFileDiv();

        function loadFileDiv() {
            var feed = '#files_deposit';
            var token = <?= json_encode($tokenChat, JSON_UNESCAPED_UNICODE) ?>;
            $.post("staff/load_deposit_file_hist.php", {
                    token
                },
                function(valor) {
                    $(feed).html(typeof stSafeChatHtml === 'function' ? stSafeChatHtml(valor) : valor);
                });

        }
        </script>
        <!-- <div id="files_deposit"></div> -->


        <?php //} ?>

    </div>
    <?php if($infoUser['env_file']==1){ ?>
    <div id="dep_file" class="dep_file">
        <ul class="nav nav-tabs" id="tabChat" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file" type="button"
                    role="tab" aria-controls="file" aria-selected="false"><i class="fas fa-folder-open"></i> Depósito de
                    arquivos</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="file" role="tabpanel" aria-labelledby="file-tab">
                <div id="files_deposit"></div>
            </div>
        </div>
    </div>
    <?php } ?>
    <div id="monitoria_<?=$idChat; ?>" class="mon_ini"></div>
</div>