<link rel='stylesheet' type='text/css' href='chat/assets/css/style-com.css?<? time() ?>'>
</style>

<script>
var com = <?=$_POST['id_com']?>;
var user = '<?=$infoUser['id_user']?>';
var indice = <?=$_POST['indice']?>;
//console.log('indice group: ' + indice);
//console.log("Com group: " + com);


if (typeof load_message_box !== 'undefined') {
    clearTimeout(load_message_box);
}
</script>

<style>
<?php if($infoUser['nivel_id']<=1 || ($infoUser['nivel_id']>2 && $infoUser['resp_men']==1)) {
    ?>.chat-content {
        height: 255px !important;
    }

    <?php
}

else {
    ?>.chat-content {
        height: 380px !important;
    }

    <?php
}

?>
</style>
<?php
$comIdGroup = (int) ($_POST['id_com'] ?? 0);
$userIdGroup = (int) ($infoUser['id_user'] ?? 0);
$nivelGroup = (int) ($infoUser['nivel_id'] ?? 0);
$contratoIdGroup = (int) ($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);

$sql="SELECT data_hora from tbl_com_msg_group where chat_group=? and rem_id<>? order by id_msg limit 1";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$comIdGroup, $userIdGroup]);
$infoMsg = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($infoMsg);
$sql="SELECT user_id, dt_view from tbl_com_msg_group_view where group_chat=? and user_id=?";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$comIdGroup, $userIdGroup]);
$infoMsgView = $stmt->fetch( PDO::FETCH_ASSOC );

if($infoMsgView['user_id']==''){
    $sql = "INSERT INTO tbl_com_msg_group_view (user_id, group_chat) VALUES (?, ?)";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute([$userIdGroup, $comIdGroup]);
}

$sql = "UPDATE tbl_com_msg_group_view SET dt_view=now() where group_chat=? and user_id=?";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([$comIdGroup, $userIdGroup]);
if($result){

    echo '<script>loadComList(indice, com);</script>';
}




$tk = strtotime(date('Y-m-d H:i:s'));

$sql="SELECT id_com, data_hora, rem_chat, dest_chat, grupo_com from tbl_com_info where id_com=?";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$comIdGroup]);
$infoCom = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($infoCom);


if($infoCom['rem_chat']==0 && $infoCom['dest_chat']==0){
    $sql_hist="SELECT a.id_msg, a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_group, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.msg from tbl_com_msg_group a where chat_group=? order by id_msg desc limit 0,30";
} else {
    $sql_hist="SELECT a.id_msg, a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_group, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.msg from tbl_com_msg a where com_id=? order by id_msg desc limit 0,30";
}


//echo "<br>".$sql_hist;

$stmt = $PDO->prepare($sql_hist);
$result = $stmt->execute([(int) $infoCom['id_com']]);
$infoComMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );

//depurador($infoComMsg);
/*
if($infoCom['rem_chat']==0 && $infoCom['dest_chat']==0){
    $com = $infoCom['grupo_com'];
} else {
    $com = $infoCom['id_com'];
}
*/
$com = $infoCom['id_com'];



?>

<div class="chat-div_<?=$_POST['id_com']?>">
    <section class="chat-content" id="chat-content_com_<?=$_POST['id_com']?>">
        <?php if(count($infoComMsg)>30){ ?>
        <button id="ver-mais_<?=$_POST['id_com']?>" class="btn-chat" data-ref="2">Carregar mais...</button>
        <?php } ?>

        <div id="conteudo-grupo_<?=$_POST['id_com']?>">
            <?php

                            for($z=count($infoComMsg);$z>=0;$z--){
                                $ls=$infoComMsg[$z];

                                $class = ($ls['rem_id']==$infoUser['id_user']) ? 'me' : 'other';
                                if($ls['rem_id']==0){

                                    $h5="";
                                    $class = 'sys';
                                } else {
                                    $h5 = "<h5>".ucwords(strtolower($ls['nome_rem']))."</h5>";
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
                    ?>
        </div>
    </section>
</div>


<div class="group-chat">

    <div id="form_com_<?=$_POST['id_com']?>" class="form_com">

        <div class="input-container">
            <input type="hidden" name="name_com_<?=$_POST['id_com']?>" id="name_com_<?=$_POST['id_com']?>"
                value="<?=ucwords(strtolower($infoUser['nome_completo']))?>" />
            <input type="hidden" name="id_user_remetente_com_<?=$_POST['id_com']?>"
                id="id_user_remetente_com_<?=$_POST['id_com']?>" value="<?=$infoUser['id_user']?>" />

            <input type="hidden" name="img_com_<?=$_POST['id_com']?>" id="img_com_<?=$_POST['id_com']?>"
                value="<?=$infoUser['img_perfil']?>" />
            <?php if($infoUser['nivel_id']<=1 || ($infoUser['nivel_id']>2 && $infoUser['resp_men']==1)){ ?>
            <textarea name="message_com_<?=$tk?>" id="message_com_<?=$tk?>" cols="5" rows="3"></textarea>
            <?php } ?>
        </div>

    </div>
    <?php if($infoUser['nivel_id']<=1 || ($infoUser['nivel_id']>2 && $infoUser['resp_men']==1)){ ?>
    <div class="div_btn_group">
        <div class="btn-form">
            </button>
            <button id="btn_send_com_<?=$_POST['id_com']?>" class="btn-chat btn-send-chat" title="Enviar Mensagem">
                <i class="far fa-paper-plane fa-3x"></i>
            </button>
        </div>

        <div class="btn-form">
            <button id="btn_file_<?=$_POST['id_com']?>" class="btn-chat btn-send-file-ind" title="Enviar Arquivo"
                data-bs-toggle="modal" data-bs-target="#div_file_<?=$_POST['id_com']?>">
                <i class="fas fa-upload fa-3x"></i>
            </button>
        </div>

    </div>
    <?php } ?>
</div>
<div id="feed_<?=$_POST['id_com']?>" class="feed"></div>


<script type="text/javascript" src='chat/assets/js/script_com_group.js?<?= time() ?>' defer></script>

<script>
$(document).ready(function() {
    $('#ver-mais_<?=$_POST['id_com']?>').click(function() {
        let proxima_pagina = $(this).attr('data-ref');

        $.ajax({
            url: 'staff/loadChatCom.php',
            data: {
                pagina: proxima_pagina,
                com: '<?=$com?>'
            },
            type: 'GET',
            success: function(response) {
                //$('#conteudo-grupo').append(response);
                $('#conteudo-grupo_<?=$_POST['id_com']?>').html(response);
            },
            complete: function() {
                $('#ver-mais_<?=$_POST['id_com']?>').attr('data-ref', parseInt(
                    proxima_pagina) + 1);
            }
        });
    });
});

$('#chat-content_com_<?=$_POST['id_com']?>').animate({
    scrollTop: 100000
}, 'slow');



//btn_env.addEventListener('click', function () {

//});
$('#btn_send_com_<?=$_POST['id_com']?>').click(function() {
    chat_com();
});

function chat_com() {
    var msg = $('#message_com_<?=$tk?>').val();
    var rem = $('#id_user_remetente_com_<?=$_POST['id_com']?>').val();
    var nome = $('#name_com_<?=$_POST['id_com']?>').val();
    var img = $('#img_com_<?=$_POST['id_com']?>').val();
    var tk = '<?=$tk?>';
    //console.log('stage 1');
    saveMsgCom(msg, rem, com, nome, img, tk);

}

setTimeout(function() {
    message_box_<?=$tk?>();
}, 0);


function message_box_<?=$tk?>() {
    stTinyMceApply('#message_com_<?=$tk?>', {
        menubar: false,
        height: 100,
        branding: false,
        promotion: false,
        toolbar: '',
        invalid_elements: "div,span,a,nav,code,h1,h2,h3,h4,h5,script,style,tr,table,td,javascript",
        content_style: 'body { font-size:12px }'
    });
}

/*        */
</script>

<div class="modal fade" id="div_file_<?=$_POST['id_com']?>" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Enviar um Arquivo</h1>
                    <button type="button" id="close_file_<?=$_POST['id_com']?>" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <div id="div_file_inp_<?=$_POST['id_com']?>" class="upload-file">
                        <h5>Tamanho de arquivo permitido até 10mb</h5>
                        </h5>
                        <label id="lbl_input_<?=$_POST['id_com']?>" for="file_<?=$_POST['id_com']?>"><i
                                class="fas fa-upload fa-10x"></i></label>
                        <input id="file_<?=$_POST['id_com']?>" name="file_<?=$_POST['id_com']?>" type="file"
                            style="width: 100%" accept=".jpg, .png, .doc, .docx, .xls, .xlsx, .pdf" />
                    </div>


                    <style>
                    .class_bar {
                        height: 100px;
                        width: 100%;
                        background: #FF0000;
                    }

                    .div_bar {
                        display: none;
                    }
                    </style>

                    <div id="div_bar_<?=$_POST['id_com']?>" class="div_bar">
                        <progress id="bar_<?=$_POST['id_com']?>" value="0" max="100"
                            class="class_bar"><br></progress><span id="porcentagem_<?=$_POST['id_com']?>">0%</span>
                    </div>

                    <input type="text" id="ipt_file_<?=$_POST['id_com']?>" style="display: none">


                    <div id="status_file_<?=$_POST['id_com']?>"></div>

                </center>


            </div>
            <div class="modal-footer">
                <div id="save_feed"></div>
                <button type="button" id="save_file_<?=$_POST['id_com']?>" class="btn btn-success" title="Enviar aquivo"
                    disabled><i class="fas fa-paper-plane"></i></button>
            </div>


            <script>
            $(document).ready(function() {

                var form;

                $('#file_<?=$_POST['id_com']?>').change(function(event) {
                    $('#div_file_inp_<?=$_POST['id_com']?>').hide();
                    $('#div_bar_<?=$_POST['id_com']?>').show();
                    form = new FormData();
                    form.append('arquivo', event.target.files[0]);
                    let rem = '<?=$infoUser['id_user']?>';
                    var comId = <?=$_POST['id_com']?>;
                    form.append('rem', rem);
                    form.append('comId', comId);
                    $('#status_file_<?=$_POST['id_com']?>').html('Carregando...');
                    $.ajax({
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();

                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = evt.loaded / evt.total;
                                    percentComplete = parseInt(percentComplete *
                                        100);
                                    //console.log(percentComplete);
                                    $('#bar_<?=$_POST['id_com']?>').val(
                                        percentComplete);
                                    $('#porcentagem_<?=$_POST['id_com']?>').html(
                                        percentComplete + '%');
                                    if (percentComplete === 100) {
                                        $('#bar_<?=$_POST['id_com']?>').val(
                                            percentComplete);
                                        $('#porcentagem_<?=$_POST['id_com']?>')
                                            .html(percentComplete + '%');
                                    }

                                }
                            }, false);

                            return xhr;
                        },
                        url: 'staff/load_file_com.php',
                        data: form,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: function(valor) {
                            //console.log(valor);
                            $('#status_file_<?=$_POST['id_com']?>').html(valor);
                            //zera_file();
                        }
                    });

                });



                $('#save_file_<?=$_POST['id_com']?>').click(function() {
                    var feed = '#status_file_<?=$_POST['id_com']?>';
                    var file = $('#ipt_file_<?=$_POST['id_com']?>').val();
                    var rem = rem = '<?=$infoUser['id_user']?>';
                    var com = '<?=$_POST['id_com']?>';
                    var name = $('#name_<?=$_POST['id_com']?>').val();
                    var mensagem = name + ' enviou um arquivo';
                    $(feed).html(
                        '<center><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></center>'
                        );
                    $.post("staff/save_file_com.php", {
                            file,
                            rem,
                            com
                        },
                        function(valor) {
                            $(feed).html(valor);
                            //console.log('chat_com_ind: ' + name_file);
                            //var mensagem = name + ' enviou o arquivo ' + name_file;
                            //chatIn(group, dest, contrato, token, mensagem, indice);
                            var msg = '<center><a href="' + link +
                                '" target="_blank" class="linkFile"><i class="fas fa-file-download fa-5x"></i><br>' +
                                name_file + '</a></center>';
                            var rem = $('#id_user_remetente_com_<?=$_POST['id_com']?>').val();
                            var nome = $('#name_com_<?=$_POST['id_com']?>').val();
                            var img = $('#img_com_<?=$_POST['id_com']?>').val();
                            var tk = '<?=$tk?>';
                            saveMsgCom(msg, rem, com, nome, img, tk);
                            $('#close_file_<?=$_POST['id_com']?>').click();
                            $('#save_file_<?=$_POST['id_com']?>').prop('disabled', true);
                        });
                });





                $('#close_file_<?=$_POST['id_com']?>').click(function() {
                    zera_file();
                });


                function zera_file() {
                    $('#status_file_<?=$_POST['id_com']?>').html('');
                    $('#bar_<?=$_POST['id_com']?>').val('0');
                    $('#porcentagem_<?=$_POST['id_com']?>').html('0%');
                    $('#div_bar_<?=$_POST['id_com']?>').hide();
                    $('#file_<?=$_POST['id_com']?>').val('');
                    $('#div_file_inp_<?=$_POST['id_com']?>').show();

                    form = '';

                }





            });
            </script>

        </div>
    </div>
</div>


<?php
$infoParticipantes='';
$sqlGroupConfig="SELECT grupo_com_id, equipe_adm, equipe_bko, equipe_ate, cols, (SELECT grupo_nome from tbl_com_info where id_com=grupo_com_id) as nome_grupo from tbl_com_config where grupo_com_id=?";
$stmt = $PDO->prepare($sqlGroupConfig);
$result = $stmt->execute([$comIdGroup]);
$infoConfigGrupo = $stmt->fetch( PDO::FETCH_ASSOC );
// var_dump($infoConfigGrupo);
if($infoConfigGrupo['cols']!=''){
?>

<div class="modal fade" id="mod_participantes_<?=$_POST['id_com']?>" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Participantes do Grupo</h1>
                    <button type="button" id="close_part_<?=$_POST['id_com']?>" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php

                        $infoConfigGrupo['col'] = str_replace("'", "", $infoConfigGrupo['cols']);
                        $infoConfigGrupo['col']=substr($infoConfigGrupo['col'], 2);
                        //echo "<br>".$infoConfigGrupo['col'];

                        $colIds = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', (string) ($infoConfigGrupo['col'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))));
                        $infoPart = [];
                        if (count($colIds) > 0) {
                            $ph = implode(',', array_fill(0, count($colIds), '?'));
                            $sqlGroupConfig="SELECT concat(nome, ' ', sobrenome) as nome_user, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel from tbl_user where id_user IN ($ph) order by nivel, nome_user";
                            //echo "<br>".$sqlGroupConfig;

                            $stmt = $PDO->prepare($sqlGroupConfig);
                            $result = $stmt->execute($colIds);
                            $infoPart = $stmt->fetchAll( PDO::FETCH_ASSOC );
                        }
                        //depurador($infoPart);

                         for($arrPart=0;$arrPart<count($infoPart);$arrPart++){
                            echo "<li>".$infoPart[$arrPart]['nivel']." - ".$infoPart[$arrPart]['nome_user']."</li>";
                        }





                ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php if($infoUser['nivel_id']<1){ ?>
<!-- ALT GRUPO -->
<div class="modal fade" id="alt_group_<?=$_POST['id_com']?>" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Alterar Grupo</h5>
                <button type="button" id="close_group_alt_<?=$_POST['id_com']?>" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="content-10-line">
                    <div class="input-container">
                        <input id="nome_grupo_alt_<?=$_POST['id_com']?>" class="input" type="text" pattern=".+"
                            value="<?=$infoConfigGrupo['nome_grupo']?>" required />
                        <label class="label" for="nome_grupo_alt_<?=$_POST['id_com']?>">Nome do Grupo</label>
                    </div>
                </div>
                <?php
                    $sql="SELECT id_user, concat(nome, ' ', sobrenome) as nome_col, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel from tbl_user where id_user<>?";
                    $altGroupParams = [$userIdGroup];
                    if($nivelGroup !== 0){
                        $sql .= ' and contrato_id=?';
                        $altGroupParams[] = $contratoIdGroup;
                    }
                    $sql .= ' order by nivel asc, nome_col asc';
                    //echo "<br>".$sql;
                    $stmt = $PDO->prepare($sql);
                    $result = $stmt->execute($altGroupParams);
                    $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
                ?>

                <div class="content-10-line">
                    <div class="switch">
                        <select id="col_group_alt_<?=$_POST['id_com']?>" name="col_group_alt_<?=$_POST['id_com']?>[]"
                            multiple class="form-control">
                            <?php

                                for($x=0;$x<count($dados);$x++){
                                    $stringUser = "'".$dados[$x]['id_user']."'";
                                    echo '<br>'.$stringUser;
                                    if (strpos($infoConfigGrupo['cols'], $stringUser) !== false) {
                                        $sel='selected';
                                    } else {
                                        $sel='';
                                    }
                                    echo '<option value="'.$dados[$x]['id_user'].'" '.$sel.'>'.$dados[$x]['nivel'].' - '.$dados[$x]['nome_col'].'</option>';
                                }
                            ?>
                        </select>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <div id="save_feed_group_alt_<?=$_POST['id_com']?>"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                        class="fas fa-times-circle"></i></button>
                <button type="button" id="save_group_alt_<?=$_POST['id_com']?>" class="btn btn-success"><i
                        class="fas fa-save"></i></button>
            </div>

        </div>
    </div>
</div>

<script>
$("#save_group_alt_<?=$_POST['id_com']?>").click(function() {
    //console.log('Clicou no botão');
    var com = '<?=$_POST['id_com']?>';
    var col = $('#col_group_alt_<?=$_POST['id_com']?>').val();
    var nome_grupo = $('#nome_grupo_alt_<?=$_POST['id_com']?>').val();

    altGrupo_<?=$_POST['id_com']?>(com, col, nome_grupo);
    //$('#col_group').val('');

});

function altGrupo_<?=$_POST['id_com']?>(com, col, nome_grupo) {
    //img = '';
    console.log(col);
    //console.log(nome_grupo);
    //console.log(rem);
    //console.log(msg);

    //$('#close_group').click();
    $("#save_feed_group_alt_<?=$_POST['id_com']?>").html(
        '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
    $.post("staff/alt_grupo.php", {
            com,
            col,
            nome_grupo
        },
        function(valor) {
            $("#save_feed_group_alt_<?=$_POST['id_com']?>").html(valor);
            loadComList();
        });
}
</script>
<?php } ?>
