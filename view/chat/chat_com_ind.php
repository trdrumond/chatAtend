<link rel='stylesheet' type='text/css' href='chat/assets/css/style-com.css?<? time() ?>'>
</style>


<script>
var com = <?=$_POST['id_com']?>;
var user = '<?=$infoUser['id_user']?>';
var indice = <?=$_POST['indice']?>;
//console.log('indice ind: ' + indice);
//console.log("Com ind: " + com);

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
<script type="text/javascript" src='chat/assets/js/script_com_ind.js?<?= time() ?>' defer></script>
<?php

$comIdInd = (int) ($_POST['id_com'] ?? 0);
$userIdInd = (int) ($infoUser['id_user'] ?? 0);

$tk = strtotime(date('Y-m-d H:i:s'));

$sqlVisual="UPDATE tbl_com_msg SET dt_visual=now() where dt_visual is null and com_id=? and dest_id=?";
//echo "<br>".$sqlVisual;
$stmt = $PDO->prepare( $sqlVisual );
$result = $stmt->execute([$comIdInd, $userIdInd]);
if($result){
    echo '<script>loadComList(indice, com);</script>';
}

$sql="SELECT id_com, data_hora, rem_chat, dest_chat, grupo_com from tbl_com_info where id_com=?";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$comIdInd]);
$infoCom = $stmt->fetch( PDO::FETCH_ASSOC );
//depurador($infoCom);

$destChat = 0;
if((int) $infoCom['rem_chat'] === (int) $infoUser['id_user']){
    $destChat = (int) $infoCom['dest_chat'];
}
if((int) $infoCom['dest_chat'] === (int) $infoUser['id_user']){
    $destChat = (int) $infoCom['rem_chat'];
}

    $sql_hist="SELECT a.id_msg, a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.msg from tbl_com_msg a where com_id=? order by id_msg desc limit 0,30";

//echo "<br>".$sql_hist;

$stmt = $PDO->prepare($sql_hist);
$result = $stmt->execute([(int) $infoCom['id_com']]);
$infoComMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );

//depurador($infoComMsg);
if($infoCom['rem_chat']==0 && $infoCom['dest_chat']==0){
    $com = $infoCom['grupo_com'];
} else {
    $com = $infoCom['id_com'];
}



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
    <!--
        <div class="btn-form">
            <button id="btn_send_com" class="btn-chat btn-send-chat">
                <i class="far fa-paper-plane"></i>
            </button>
        </div>
        -->
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


<script type="text/javascript" src='chat/assets/js/script_com_ind.js?<?= time() ?>' defer></script>

<script>
$(document).ready(function() {
    $('#ver-mais_<?=$_POST['id_com']?>').click(function() {
        let proxima_pagina = $(this).attr('data-ref');

        $.ajax({
            url: 'staff/loadChatComInd.php',
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
    var dest = '<?=$destChat?>';
    var nome = $('#name_com_<?=$_POST['id_com']?>').val();
    var img = $('#img_com_<?=$_POST['id_com']?>').val();
    var tk = '<?=$tk?>';
    //console.log(msg)
    saveMsgComInd(msg, rem, dest, com, nome, img, tk);

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
                            var dest = '<?=$destChat?>';
                            var nome = $('#name_com_<?=$_POST['id_com']?>').val();
                            var img = $('#img_com_<?=$_POST['id_com']?>').val();
                            var tk = '<?=$tk?>';
                            saveMsgComInd(msg, rem, dest, com, nome, img, tk);
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
