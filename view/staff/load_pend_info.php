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

<?php
    include("../cnf/session.php");
    include_once("../cnf/func_input.php");
    include('../cnf/rotina_pendencia.php');

    $idChat = (int) ($_POST['id_chat'] ?? 0);
    $contratoPost = (int) ($_POST['contrato'] ?? 0);
    if ($contratoPost > 0 && !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoPost)) {
        echo '<p class="text-danger">Contrato não autorizado.</p>';
        return;
    }

    $sql="SELECT a.id_pend, a.fila_id, a.chat_id, a.ate_resp, a.bko_resp, a.data_hora, a.motivo, b.fila_chat_id from tbl_pend_info a, tbl_chat_info_secondary b where a.chat_id=b.fila_chat_id and b.id_chat=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$idChat]);
    $infoPend = $stmt->fetch( PDO::FETCH_ASSOC );
    if (!is_array($infoPend)) {
        echo '<p class="text-warning">Pendência não encontrada.</p>';
        return;
    }

    $infoPend['motivo'] = (($infoPend['motivo'] ?? '')=='') ? 'Motivo não informado' : $infoPend['motivo'];
    $filaChatId = (int) ($infoPend['fila_chat_id'] ?? 0);
    $chatPendId = (int) ($infoPend['chat_id'] ?? 0);
?>
<div class="titulo_mon">
    <div class="titulo">
        <h5>Pendência</h5>
    </div>
    <div class="close_mon">
        <button type="button" class="btn-close" onclick="closePend(<?= $idChat ?>)"></button>
    </div>
</div>
<div class="cont_pend" id="conteudo_pend_<?= $filaChatId ?>">

    <div class="cont_pend">
        <div class="titulo_pend">Motivo da Pendência:</div>
        <div class="motivo_pend"><?= stHtml($infoPend['motivo']) ?></div>
    </div>

    <div class="cont_pend">
        <div class="titulo_pend">Informação da Pendência</div>
        <div class="content-10-line">
            <div class="input-container">
                <textarea id="txt_pend_<?= $filaChatId ?>" rows="3"></textarea>

            </div>
        </div>
    </div>

    <button class="btn btn-success" id="save_pend_<?= $filaChatId ?>" type="button">Finalizar
        Pendência</button>

</div>

<script>
function savePend(id_chat, id_fila_chat, txt_pend) {
    var div_pend = '#conteudo_pend_<?= $filaChatId ?>';

    $(div_pend).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

    $.post("staff/save_pend_info.php", {
            id_chat,
            id_fila_chat,
            txt_pend
        },
        function(valor) {
            $(div_pend).html(typeof stSafeChatHtml === 'function' ? stSafeChatHtml(valor) : valor);
            abreDetail(<?= $idChat ?>);
        });
}

$('#save_pend_<?= $filaChatId ?>').click(function() {
    var id_chat = <?= $idChat ?>;
    var id_fila_chat = <?= $chatPendId ?>;
    var txt_pend = $('#txt_pend_<?= $filaChatId ?>').val();
    $('#save_pend_<?= $filaChatId ?>').html(
        '<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');
    savePend(id_chat, id_fila_chat, txt_pend);
});
</script>
