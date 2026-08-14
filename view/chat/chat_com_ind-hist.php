<link rel='stylesheet' type='text/css' href='chat/assets/css/style-com.css?<? time() ?>'>
</style>
<style>
.chat-div_<?=$_POST['id_com']?> {
    width: 95%;
    margin-top: 5px;
    margin-left: auto;
    margin-right: auto;
    height: 370px;
    background: #FFFFFF;
}

.chat-content-hist {
    margin: auto;
    width: 100%;
    height: 370px;
    background: #FFFFFF;
    overflow: scroll;

}
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

    if($infoCom['rem_chat']==$infoUser['id_user']){
        $destChat=$infoCom['dest_chat'];
    }
    if($infoCom['dest_chat']==$infoUser['id_user']){
        $destChat=$infoCom['rem_chat'];
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
    <section class="chat-content-hist" id="chat-content_com_<?=$_POST['id_com']?>">
        <?php if(count($infoComMsg)>30){ ?>
        <button id="ver-mais_<?=$_POST['id_com']?>" class="btn-chat" data-ref="2">Carregar mais...</button>
        <?php } ?>

        <div id="conteudo-grupo_<?=$_POST['id_com']?>">
            <?php

                            for($z=count($infoComMsg);$z>=0;$z--){
                                $ls=$infoComMsg[$z];

                                $class = ($ls['rem_id']==$infoCom ['rem_chat']) ? 'me' : 'other';
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
