<?php
    include("../cnf/session.php");
    ////include("../cnf/replace_msg.php");

    //depurador($_POST);

    $sql="SELECT rem_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_chat) as nome_rem_chat, dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as nome_dest_chat, fila_chat_id, token_chat from tbl_chat_info_secondary where id_chat='".$_POST['id']."'";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoChatx = $stmt->fetchAll( PDO::FETCH_ASSOC );


    //$sql="SELECT a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_id, a.contrato_id, b.rem_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_chat) as nome_rem_chat, b.dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as nome_dest_chat, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.dest_id, a.msg, b.fila_chat_id, b.token_chat from tbl_chat_msg a, tbl_chat_info_secondary b where a.chat_id=b.id_chat and id_chat='".$_POST['id']."'";
    $sql="SELECT
                a.data_hora,
                DATE_FORMAT(a.data_hora, '%d/%m/%Y %H:%i') AS hora_msg,
                a.chat_id,
                a.contrato_id,
                b.rem_chat,
                CONCAT(urc.nome, ' ', urc.sobrenome) AS nome_rem_chat,
                b.dest_chat,
                CONCAT(udc.nome, ' ', udc.sobrenome) AS nome_dest_chat,
                a.rem_id,
                CONCAT(ur.nome, ' ', ur.sobrenome) AS nome_rem,
                img.img,
                a.dest_id,
                a.msg,
                b.fila_chat_id,
                b.token_chat
            FROM tbl_chat_msg a
            INNER JOIN tbl_chat_info_secondary b
                ON a.chat_id = b.id_chat

            LEFT JOIN tbl_user urc
                ON urc.id_user = b.rem_chat

            LEFT JOIN tbl_user udc
                ON udc.id_user = b.dest_chat

            LEFT JOIN tbl_user ur
                ON ur.id_user = a.rem_id

            LEFT JOIN tbl_user_img_perfil img
                ON img.user_id = a.rem_id

            WHERE a.chat_id = '".$_POST['id']."'
            ORDER BY a.data_hora ASC;";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoChatMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($infoChatMsg);
    if(count($infoChatMsg)==0){
        //$sql="SELECT a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_id, a.contrato_id, b.rem_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_chat) as nome_rem_chat, b.dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as nome_dest_chat, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.dest_id, a.msg, b.fila_chat_id, b.token_chat from tbl_chat_msg_secondary a, tbl_chat_info_secondary b where a.chat_id=b.id_chat and id_chat='".$_POST['id']."'";
        $sql="SELECT
                a.data_hora,
                DATE_FORMAT(a.data_hora, '%d/%m/%Y %H:%i') AS hora_msg,
                a.chat_id,
                a.contrato_id,
                b.rem_chat,
                CONCAT(urc.nome, ' ', urc.sobrenome) AS nome_rem_chat,
                b.dest_chat,
                CONCAT(udc.nome, ' ', udc.sobrenome) AS nome_dest_chat,
                a.rem_id,
                CONCAT(ur.nome, ' ', ur.sobrenome) AS nome_rem,
                img.img,
                a.dest_id,
                a.msg,
                b.fila_chat_id,
                b.token_chat
            FROM tbl_chat_msg_secondary a
            INNER JOIN tbl_chat_info_secondary b
                ON a.chat_id = b.id_chat

            LEFT JOIN tbl_user urc
                ON urc.id_user = b.rem_chat

            LEFT JOIN tbl_user udc
                ON udc.id_user = b.dest_chat

            LEFT JOIN tbl_user ur
                ON ur.id_user = a.rem_id

            LEFT JOIN tbl_user_img_perfil img
                ON img.user_id = a.rem_id

            WHERE a.chat_id = '".$_POST['id']."'
            ORDER BY a.data_hora ASC;";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoChatMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );
    }


    $sql="SELECT concat(nome, ' ', sobrenome) as nome_completo, agencia_id, (SELECT nome_agencia from tbl_agencia where id_agencia=agencia_id) as agencia from tbl_user where id_user='".$infoChatx[0]['dest_chat']."'";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoSol = $stmt->fetch( PDO::FETCH_ASSOC );





    $sql="SELECT id_fila_chat, protocolo, data_hora, fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=fila_id) as fila, assunto_id, (SELECT titulo_assunto from tbl_assunto where id_assunto=assunto_id) as assunto, ate_resp, bko_resp, (SELECT concat(nome, ' ', sobrenome) as nome_completo from tbl_user where id_user=bko_resp) as nome_bko, date_format(data_hora, '%d/%m/%Y') as hora_reg, date_format(hora_inicio, '%H:%i:%s') as inicio, date_format(hora_fim, '%H:%i:%s') as fim, ta, te, status_fila, (SELECT nome_situacao from tbl_situacao_chat where id_situacao=status_fila) as nome_status from tbl_chat_fila_secondary where id_fila_chat=".$infoChatx[0]['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($infoChat);


    //SELECT count(*) from tbl_forms_mon_input_campo where fila_id=id_fila
    $sql="SELECT count(*) as qtd from tbl_forms_mon_input_campo where fila_id='".$infoChat['fila_id']."'";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoConfigMon = $stmt->fetch( PDO::FETCH_ASSOC );

    $sqlMotivo="SELECT motivo from  tbl_chat_fila where protocolo='".$infoChat['protocolo']."'";
    //echo "<br>".$sql_hist;
    $stmt = $PDO->prepare($sqlMotivo);
    $result = $stmt->execute();
    $motivoChat = $stmt->fetch( PDO::FETCH_ASSOC );

    if($motivoChat==''){
        $sqlMotivo="SELECT motivo from  tbl_chat_fila_secondary where protocolo='".$infoChat['protocolo']."'";
        //echo "<br>".$sql_hist;
        $stmt = $PDO->prepare($sqlMotivo);
        $result = $stmt->execute();
        $motivoChat = $stmt->fetch( PDO::FETCH_ASSOC );
    }


?>
<style>
#info_chat {
    height: 75px;
    width: 99%;
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
    width: 29%;
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
        <div class="class_info"><strong>Solicitante: </strong><?=$infoSol['nome_completo']?></div>
        <div class="class_info"><strong>Agência: </strong><?=$infoSol['agencia']?></div>
        <div class="class_info"><strong>Data: </strong><?=$infoChat['hora_reg']?></div>
        <div class="class_info"><strong>Tempo Espera: </strong><?=$infoChat['te']?></div>
    </div>

    <div class="class_div">
        <div class="class_info"><strong>Protocolo: </strong><?=$infoChat['protocolo']?></div>
        <div class="class_info"><strong>Backoffice: </strong><?=$infoChat['nome_bko']?></div>
        <div class="class_info"><strong>Assunto: </strong><?=$infoChat['assunto']?></div>
        <div class="class_info"><strong>Tempo Atend.: </strong><?=$infoChat['ta']?></div>
    </div>

    <div class="class_div">
        <div class="class_info"><strong>Início: </strong><?=$infoChat['inicio']?></div>
        <div class="class_info"><strong>Fim: </strong><?=$infoChat['fim']?></div>
        <div class="class_info"><strong>Status: </strong><?=$infoChat['nome_status']?></div>

        <?php
                    if($infoConfigMon['qtd']>0 && $infoUser['nivel_id']<=4){
                        echo '<div class="class_mon pointer" onclick="monitoria('.$_POST['id'].', '.$infoChatMsg[0]['contrato_id'].', '.$infoChat['fila_id'].')"><center>Monitoria</center></div>';
                    }
                ?>

    </div>
</div>

<script>
function monitoria(id_chat, contrato, fila) {

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

    $.post("staff/load_monitoria.php", {
            id_chat,
            contrato,
            fila
        },
        function(valor) {
            $(div_mon).html(valor);
        });
}

function closeMon(id_chat) {
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
    <div id="chat_<?=$_POST['id']; ?>" class="chat-div">


        <section class="chat-content" id="chat_content_0_">
            <?php if($motivoChat['motivo']!=''){ ?>
            <div class='motivo'><strong>Motivo:</strong>
                <p><?= $motivoChat['motivo'];?></p>
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
                                    $h5 = "<h5>".$ls['nome_rem']."</h5>";
                                }
                                echo "<div class='$class'>
                                        <img src='".$ls['img']."'>
                                        <div class='text'>
                                            ".$h5."
                                            <div class='paragrafo'>".$ls['msg']."</div>
                                            <div class='dataHora'>".$ls['hora_msg']."</div>
                                        </div>
                                    </div>";
                            }
                            if(count($infoChatMsg)==0){
                                echo '<center><h3>Não houveram mensagens neste chat!<br>Chat finalizado administrativamente!</h3></center>';
                            }
                        ?>
        </section>
        <div id="dig_0" class="dig" style="width: 100% !important;"><?php if($infoChat['status_fila']==2){?><center>
                Últimas mensagens até <?=date('d/m/Y H:i:s')?></center><?php } ?></div>
        <?php
                            $sql="SELECT count(*) as qtd from tbl_chat_files where token_chat='".$infoChatMsg[0]['token_chat']."'";
                            $stmt = $PDO->prepare($sql);
                            $result = $stmt->execute();
                            $infoFiles = $stmt->fetch( PDO::FETCH_ASSOC );
                            //depurador($infoFiles);
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
            var token = '<?=$infoChatMsg[0]['token_chat']?>';
            $.post("staff/load_deposit_file_hist.php", {
                    token
                },
                function(valor) {
                    $(feed).html(valor);
                });

        }
        </script>


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
    <div id="monitoria_<?=$_POST['id']; ?>" class="mon_ini"></div>
</div>
