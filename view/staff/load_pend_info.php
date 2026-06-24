<style>
.titulo_mon {
    background-color: #FFFFFF;
    width: 100%;
    padding-top: 10px;
    padding-bottom: 10px;
    height: 35px;
}

.titulo_mon>.titulo {
    width: 85%;
    float: left;
    background-color: #FFFFFF;
}

.titulo_mon>.close_mon {
    width: 15%;
    float: left;
    background-color: #FFFFFF;
}

.cont_pend {
    text-align: left;
    width: 90%;
    float: left;
    margin: 10px;
}

.titulo_pend {
    font-weight: bold;
    color: #520008;
}

.motivo_pend {
    color: #000000;
}
</style>

<div class="titulo_mon">
    <div class="titulo">
        <h5>Pendência</h5>
    </div>
    <div class="close_mon">
        <button type="button" class="btn-close" onclick="closePend(<?=$_POST['id_chat']; ?>)"></button>
    </div>
</div>

<?php
    include("../cnf/session.php");
    include_once("../cnf/func_input.php");
    include('../cnf/rotina_pendencia.php');

    $sql="SELECT a.id_pend, a.fila_id, a.chat_id, a.ate_resp, a.bko_resp, a.data_hora, a.motivo, b.fila_chat_id from tbl_pend_info a, tbl_chat_info_secondary b where a.chat_id=b.fila_chat_id and b.id_chat=".$_POST['id_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoPend = $stmt->fetch( PDO::FETCH_ASSOC );

    $infoPend['motivo'] = ($infoPend['motivo']=='') ? 'Motivo não informado' : $infoPend['motivo'];

    //depurador($infoPend);
?>
<div class="cont_pend" id="conteudo_pend_<?=$infoPend['fila_chat_id'];?>">

    <div class="cont_pend">
        <div class="titulo_pend">Motivo da Pendência:</div>
        <div class="motivo_pend"><?=$infoPend['motivo'];?></div>
    </div>

    <div class="cont_pend">
        <div class="titulo_pend">Informação da Pendência</div>
        <div class="content-10-line">
            <div class="input-container">
                <textarea id="txt_pend_<?=$infoPend['fila_chat_id'];?>" rows="3"></textarea>

            </div>
        </div>
    </div>

    <button class="btn btn-success" id="save_pend_<?=$infoPend['fila_chat_id'];?>" type="button">Finalizar
        Pendência</button>

</div>

<script>
//detail('.$dados[$x]['id_chat'].')
function savePend(id_chat, id_fila_chat, txt_pend) {
    console.log(id_chat);
    console.log(id_fila_chat);
    console.log(txt_pend);

    var div_pend = '#conteudo_pend_' + id_chat;

    $(div_pend).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

    $.post("staff/save_pend_info.php", {
            id_chat,
            id_fila_chat,
            txt_pend
        },
        function(valor) {
            //console.log(valor);
            $(div_pend).html(valor);
            abreDetail('<?=$_POST['id_chat'];?>');
        });
}

$('#save_pend_<?=$infoPend['fila_chat_id'];?>').click(function() {
    console.log('clicou no botão de salvar pendencia');
    var id_chat = '<?=$_POST['id_chat'];?>';
    var id_fila_chat = '<?=$infoPend['chat_id'];?>';
    var txt_pend = $('#txt_pend_<?=$infoPend['fila_chat_id'];?>').val();
    $('#save_pend_<?=$infoPend['fila_chat_id'];?>').html(
        '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');
    savePend(id_chat, id_fila_chat, txt_pend);
});
</script>
