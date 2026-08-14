<?php
/**
 * Partial incluído por chat-ate.php, chat-bko.php, dash-chat.php, etc.
 * Variáveis definidas pelo arquivo pai antes do include.
 *
 * @var array<string, mixed> $infFila
 * @var array<string, mixed> $infoUser
 * @var array<string, mixed>|false|null $infoChat
 * @var string $tokenChat
 * @var string|int|null $chatId
 * @var int|string $userDestinatario
 * @var string $indDiv
 * @var array<string, mixed>|null $motivoChat
 */
    if (!isset($PDO) || !isset($infoUser)) {
        include_once(__DIR__ . "/../cnf/session.php");
    }

    if (!isset($indDiv)) {
        $indDiv = (isset($infoChat['indice']) && $infoChat['indice'] !== '') ? (string) $infoChat['indice'] : '';
    }

    if ($_SESSION['dados']['nivel_id'] == 4) {
        $bkoId = (int)$_SESSION['dados']['id_user'];
        $filaIdChat = (int)$infFila['id_fila_chat'];
        $bkoRespAtual = isset($infFila['bko_resp']) ? (int)$infFila['bko_resp'] : 0;
        $statusFila = isset($infFila['status_fila']) ? (int)$infFila['status_fila'] : 0;

        if (!stFilaAtendimentoEncerrado($statusFila)) {
        if ($bkoRespAtual === 0) {
            stFilaEnsureSituacaoAguardando($PDO);
            $teVal = (string) (!empty($infFila['te']) ? $infFila['te'] : ($infFila['te_diff'] ?? ''));
            $sql = 'UPDATE tbl_chat_fila SET status_fila=?, bko_resp=?, te=? WHERE id_fila_chat=?'
                .' AND (bko_resp IS NULL OR bko_resp=0 OR bko_resp=\'\')';
            $stmt = $PDO->prepare($sql);
            $stmt->execute([ST_FILA_AGUARDANDO_ATENDIMENTO, $bkoId, $teVal, $filaIdChat]);
            if ($stmt->rowCount() > 0) {
                logAtendimento($PDO, $bkoId, 'Tratamento');
            }
            $infFila['bko_resp'] = $bkoId;
            $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
        } elseif ($bkoRespAtual === $bkoId && $statusFila === ST_FILA_NA_FILA) {
            stFilaEnsureSituacaoAguardando($PDO);
            $stmt = $PDO->prepare(
                'UPDATE tbl_chat_fila SET status_fila=? WHERE id_fila_chat=? AND bko_resp=? AND status_fila=?'
            );
            $stmt->execute([ST_FILA_AGUARDANDO_ATENDIMENTO, $filaIdChat, $bkoId, ST_FILA_NA_FILA]);
            $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
        } elseif ($bkoRespAtual !== $bkoId) {
            echo '<script>setTimeout(function() { location.reload(); }, 5000);</script>';
        }
        }
    }


    //echo "<br>".$chatId;

    //echo "<br>".$tokenChat;
    //$sql_hist="SELECT a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_id, a.contrato_id, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.dest_id, a.msg, b.fila_chat_id , c.motivo from tbl_chat_msg a, tbl_chat_info b, tbl_chat_fila c where a.chat_id=b.id_chat and b.fila_chat_id=c.id_fila_chat and c.protocolo='".$infFila['protocolo']."'";
    if (!empty($chatId)) {
        $sql_hist = "SELECT
                    a.data_hora,
                    DATE_FORMAT(a.data_hora, '%d/%m/%Y %H:%i') AS hora_msg,
                    a.chat_id,
                    a.contrato_id,
                    a.rem_id,
                    CONCAT(u.nome, ' ', u.sobrenome) AS nome_rem,
                    u.nome,
                    u.sobrenome,
                    img.img,
                    a.dest_id,
                    a.msg,
                    b.fila_chat_id
                FROM tbl_chat_msg a
                INNER JOIN tbl_chat_info b ON a.chat_id = b.id_chat
                LEFT JOIN tbl_user u ON u.id_user = a.rem_id
                LEFT JOIN tbl_user_img_perfil img ON img.user_id = a.rem_id
                WHERE a.chat_id = ?
                ORDER BY a.data_hora ASC";
    } else {
    $sql_hist="SELECT 
                    a.data_hora,
                    DATE_FORMAT(a.data_hora, '%d/%m/%Y %H:%i') AS hora_msg,
                    a.chat_id,
                    a.contrato_id,
                    a.rem_id,
                    CONCAT(u.nome, ' ', u.sobrenome) AS nome_rem,
                    u.nome,
                    u.sobrenome,
                    img.img,
                    a.dest_id,
                    a.msg,
                    b.fila_chat_id,
                    c.motivo
                FROM tbl_chat_msg a
                INNER JOIN tbl_chat_info b 
                    ON a.chat_id = b.id_chat
                INNER JOIN tbl_chat_fila c 
                    ON b.fila_chat_id = c.id_fila_chat
                LEFT JOIN tbl_user u 
                    ON u.id_user = a.rem_id
                LEFT JOIN tbl_user_img_perfil img 
                    ON img.user_id = a.rem_id
                WHERE c.protocolo = ?
                ORDER BY a.data_hora ASC;";
    }
    //echo "<br>".$sql_hist;

    $stmt = $PDO->prepare($sql_hist);
    if (!empty($chatId)) {
        $result = $stmt->execute([(int) $chatId]);
    } else {
        $result = $stmt->execute([(string) ($infFila['protocolo'] ?? '')]);
    }
    $infoChatMsg_hist = $stmt->fetchAll( PDO::FETCH_ASSOC );

    if (!isset($motivoChat) || !is_array($motivoChat)) {
        $motivoChat = ['motivo' => $infFila['motivo'] ?? ''];
    }
    if (($motivoChat['motivo'] ?? '') === '' && !empty($infFila['motivo'])) {
        $motivoChat['motivo'] = $infFila['motivo'];
    }
    if (($motivoChat['motivo'] ?? '') === '') {
        $sqlMotivo="SELECT motivo from tbl_chat_fila where protocolo=? LIMIT 1";
        $stmt = $PDO->prepare($sqlMotivo);
        $stmt->execute([(string) ($infFila['protocolo'] ?? '')]);
        $motivoChat = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['motivo' => ''];
    }



    $sql="SELECT a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_id, a.contrato_id, a.rem_id, a.dest_id, a.msg, b.fila_chat_id, a.flag from tbl_chat_msg a, tbl_chat_info b where a.chat_id=b.id_chat and a.flag=? and token_chat=?";
    //echo "<br>".$sql;

    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([(int) $infoUser['id_user'], (string) $tokenChat]);
    $infoChatMsgSys = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($infoChatMsgSys[0]);

    if (!empty($stChatSkipFilasCount)) {
        $infoFilas = ['qtd' => 1];
    } else {
    $contratoIdChatInd = (int) ($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);
    $sql="SELECT count(*) as qtd from tbl_config_fila where ativo=1 and contrato_id=?";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$contratoIdChatInd]);
    $infoFilas = $stmt->fetch( PDO::FETCH_ASSOC );
    }

    $stChatTinymcePrehide = ((int)$infoUser['env_img'] === 1);
    $stChatSolComposer = ((int)$infoUser['nivel_id'] !== 4);

?>

<link rel='stylesheet' type='text/css' href='chat/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: 1 ?>'>

<style>
#btn_pos,
[id^="btn_pos_"] {
    display: none !important;
    visibility: hidden !important;
}

#countCaracter {
    font-size: 10px;
    color: #CCCCCC;
}

.form-control:focus {
    border: 1px solid #CCCCCC;
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 5px rgb(0, 0, 0, 0.075) !important;
}

.upload-file>input {
    display: none;
}

<?php if($infoUser['nivel_id'] !=4) {
    echo "#action-page .st-chat-workspace .btn-send-chat { height: 36px !important; min-height: 36px !important; }";
}

?><?php if($infoUser['env_file']==0) {

    ?>#btn_file_responsive_<?=$chatId?>,
    #btn_file_<?=$chatId?> {
        display: none;
    }

    <?php
}

?>
</style>


<div class="chat-div">
    <section class="chat-content" id="chat-content_<?=$chatId?>">
        <?php if ($motivoChat['motivo'] != '') { ?>
        <div class="motivo st-chat-motivo"><strong>Motivo do chamado</strong>
            <p><?= htmlspecialchars($motivoChat['motivo']) ?></p>
        </div>
        <?php } ?>
        <?php
                $horaInicioAtiva = !empty($infFila['hora_inicio'])
                    && $infFila['hora_inicio'] !== '0000-00-00 00:00:00';
                $emAtendimentoAtivo = isset($infFila['status_fila'])
                    && (int)$infFila['status_fila'] === ST_FILA_EM_ATENDIMENTO;
                if (count($infoChatMsg_hist) === 0 && !$horaInicioAtiva && !$emAtendimentoAtivo) {
                    echo '<div class="st-chat-waiting" id="st-chat-waiting_'.$chatId.'"><i class="fas fa-user-clock" aria-hidden="true"></i><span>Aguardando participantes na sala...</span></div>';
                }
                for ($z = 0; $z < count($infoChatMsg_hist); $z++) {
                    $ls=$infoChatMsg_hist[$z];

                    $class = ($ls['rem_id']==$infoUser['id_user']) ? 'me' : 'other';
                    if($ls['rem_id']==0){

                        $h5="";
                        $class = 'sys';
                    } else {
                        $h5 = "<h5>".ucwords((strtolower($ls['nome'])).' '.(strtolower($ls['sobrenome'][0])))."."."</h5>";
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

    </section>
    <div id="dig_<?=$chatId?>" class="dig"></div>
</div>



<div class="form-chat st-chat-composer">




    <div id="form_<?=$chatId?>" class="form">
        <div class="input-container">

            <input type="hidden" name="name_<?=$chatId?>" id="name_<?=$chatId?>"
                value="<?=ucwords((strtolower($infoUser['nome'])).' '.(strtolower($infoUser['sobrenome'][0]))).".";?>" />
            <input type="hidden" name="id_user_remetente_<?=$chatId?>" id="id_user_remetente_<?=$chatId?>"
                value="<?=$infoUser['id_user']?>" />
            <input type="hidden" name="id_user_destinatario_<?=$chatId?>" id="id_user_destinatario_<?=$chatId?>"
                value="<?=$userDestinatario?>" />
            <input type="hidden" name="indice_<?=$chatId?>" id="indice_<?=$chatId?>" value="<?=$indDiv?>" />

            <input type="hidden" name="img_<?=$chatId?>" id="img_<?=$chatId?>" value="<?=$infoUser['img_perfil']?>" />

            <?php if($infoUser['nivel_id']==4){
                $sql="SELECT id_campo, titulo_men, txt from tbl_config_men_ini where ativo=1 and contrato_id=? and (assunto_id=0 or assunto_id=?)";
                $stmt = $PDO->prepare($sql);
                $stmt->execute([(int) $infFila['contrato_id'], (int) $infFila['assunto_id']]);
                $selMen = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $selMen = [];
            } ?>
            <?php if($infoUser['nivel_id']==4){ ?>
            <select name="select_in_chat_<?=$chatId?>" id="select_in_chat_<?=$chatId?>">
                <option value="">Selecione...</option>
                <?php
                            for($m=0;$m < count($selMen);$m++){
                                echo "<option value='".$selMen[$m]['id_campo']."'>".htmlspecialchars($selMen[$m]['titulo_men'])."</option>";
                            }
                        ?>
            </select>
            <br>
            <?php } ?>

            <div id="div_message_<?=$chatId?>" class="msg_textarea<?= $stChatTinymcePrehide ? ' st-chat-tinymce-active' : '' ?>">
                <textarea name="msg_<?=$chatId?>" id="msg_<?=$chatId?>" class="form-control st-chat-textarea<?= $stChatTinymcePrehide ? ' st-chat-force-hidden' : '' ?>"
                    placeholder="Digite sua mensagem..." rows="2"<?= $stChatTinymcePrehide ? ' aria-hidden="true" tabindex="-1"' : '' ?>></textarea>
                <input type="hidden" id="message_<?=$chatId?>" name="message_<?=$chatId?>" value="">
            </div>

            <div id="btn-bko-980">
                <div style="width: 100%;" id="btns-bko-2">
                    <?php if($infoUser['nivel_id']==4){ ?>
                    <?php if($infoFilas['qtd']>1){ ?>
                    <button type="button" id="trasnferir_<?=$chatId?>" class="btn-bko" data-bs-toggle="modal"
                        title="Transferir Atendimento" data-bs-target="#div_transfer_<?=$chatId?>"><i
                            class="fas fa-share-square"></i></button>
                    <?php } ?>
                    <?php } ?>

                    <button type="button" id="btn_file_responsive_<?=$chatId?>" class="btn-bko btn-send-file"
                        data-bs-toggle="modal" title="Enviar um arquivo" data-bs-target="#div_file_<?=$chatId?>"><i
                            class="fas fa-upload"></i></button>
                    <?php if($infoUser['nivel_id']==4){ ?>
                    <button id="btn_atent_responsive_<?=$chatId?>" class="btn-chat btn-atent-chat"
                        title="Chamar Atenção do Solicitante"><i class="fas fa-volume-up"></i></button>
                    <?php } ?>
                    <button id="btn_fin_responsive_<?=$chatId?>" class="btn-finalizar" title="Finalizar atendimento"><i
                            class="fas fa-times-circle"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div id="btn-bko">
        <div style="width: 49%; float: left; padding-top: 20px" class="text-msg-area">
            <button type="button" id="btn1_<?=$chatId?>" class="btn-chat btn-send-chat" title="Enviar mensagem"><i
                    class="fas fa-paper-plane fa-2x"></i></button>
        </div>
        <div style="width: 49%; float: left; padding-top: 20px" id="btns-bko-1">
            <?php if($infoUser['nivel_id']==4){ ?>
            <?php if($infoFilas['qtd']>1){ ?>
            <button type="button" id="trasnferir_<?=$chatId?>" class="btn-bko" data-bs-toggle="modal"
                title="Transferir Atendimento" data-bs-target="#div_transfer_<?=$chatId?>"><i
                    class="fas fa-share-square"></i></button>
            <?php } ?>
            <?php } ?>
            <button type="button" id="btn_file_<?=$chatId?>" class="btn-bko btn-send-file" data-bs-toggle="modal"
                title="Enviar um arquivo" data-bs-target="#div_file_<?=$chatId?>"><i class="fas fa-upload"></i></button>
            <?php if($infoUser['nivel_id']==4){ ?>
            <button id="btn_atent_<?=$chatId?>" class="btn-chat btn-atent-chat" title="Chamar Atenção do Solicitante"><i
                    class="fas fa-volume-up"></i></button>
            <?php } ?>
            <button id="btn_fin_<?=$chatId?>" class="btn-finalizar" title="Finalizar atendimento"><i
                    class="fas fa-times-circle"></i></button>
        </div>


    </div>
</div>
<div id="feed_<?=$chatId?>"></div>
<audio controls volume="1" id="audio_men" style="display: none">
    <source src="audio/men.mp3" type="audio/mpeg">
</audio>

<audio controls volume="1" id="audio_atent" style="display: none">
    <source src="audio/atent_01.mp3" type="audio/mpeg">
</audio>

<script>
userDem_<?=$userDestinatario?> = <?=$userDestinatario?>;
chatId_<?=$chatId?> = <?=$chatId?>;
</script>

<div id="chat_id_feed"></div>


<link>
<script>
var chat_id = '<?=$chatId?>';

if (chat_id === '' || chat_id === '0') {
    setTimeout(function() {
        carregaId();
    }, 200);
}

function carregaId() {
    if (chat_id === '' || chat_id === '0') {
        loadId();
    }
}



function loadId() {
    //console.log('carrega id chat');
    var protocolo = '<?=$infFila['protocolo']?>';
    $.post("staff/loadId.php", {
            protocolo
        },
        function(valor) {
            console.log(valor);
            $('#chat_id_feed').html(valor);
        });
}

<?php if($infoUser['nivel_id']==4){ ?>

//setTimeout(function(){
//    verificaBko();
//}, 500);

function verificaBko() {
    //console.log('carrega id chat');
    var resp_id = <?=$infoUser['id_user']?>;
    var id = <?=$infFila['fila_id']; ?>;
    $.post("staff/verificaBko.php", {
            resp_id,
            id
        },
        function(valor) {
            //console.log(valor);
            $('#chat_id_feed').html(valor);
        });
}

<?php } ?>




//console.log('<?=$indDiv;?>');

<?php if($indDiv!=''){ ?>
indice = '<?=$indDiv;?>';
<?php } ?>

(function () {
    var indVal = (typeof indice !== 'undefined') ? indice : '';
    if ($('#indice_<?=$chatId?>').length && $('#indice_<?=$chatId?>').val() === '' && indVal !== '') {
        $('#indice_<?=$chatId?>').val(indVal);
    }
})();

<?php // newMessage definido em script.js (roteamento por chatId/aba) ?>








<?php //if(count($infoChatMsgSys)<1){ ?>
//console.log('count: <?= count($infoChatMsgSys); ?>')
(function bootInChat(retries) {
    retries = retries || 0;
    var domReady = $('#chat-content_<?=$chatId?>').length &&
        $('#id_user_remetente_<?=$chatId?>').length &&
        ($('.st-chat-workspace--sol, .st-chat-workspace--bko').length > 0);
    if (domReady && typeof inChat === 'function') {
        inChat();
        if (typeof stChatTryStartAtendimento === 'function') {
            setTimeout(function () {
                stChatTryStartAtendimento('<?= (int)$chatId ?>', <?= json_encode($tokenChat ?? '') ?>);
            }, 700);
        }
        return;
    }
    if (retries < 25) {
        setTimeout(function () { bootInChat(retries + 1); }, retries < 3 ? 0 : 16);
    }
})(0);
<?php //} ?>


if (typeof Notifyer !== 'undefined' && Notifyer.init) {
    Notifyer.init().catch(function () {});
}
if (typeof App !== 'undefined' && App.mostraNotificacao) {
    App.mostraNotificacao("Chat em andamento!");
}

function stChatEscHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function play_men() {
    if (typeof safePlayMen === 'function') {
        safePlayMen();
        return;
    }
    var men_audio = document.getElementById('audio_men');
    if (!men_audio) {
        return;
    }
    try {
        var playPromise = men_audio.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function () {});
        }
    } catch (errPlay) {}
}

function play_atent(msg) {
    if (typeof safePlayAtent === 'function') {
        safePlayAtent();
    } else {
        audio_atent = document.getElementById('audio_atent');
        if (audio_atent) {
            try {
                var playPromise = audio_atent.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () {});
                }
            } catch (errPlay) {}
        }
    }
    if (typeof App !== 'undefined' && App.mostraNotificacao) {
        App.mostraNotificacao(msg);
    }
}



function inChat() {
    if (typeof chatIn !== 'function') {
        return;
    }
    var chatId = '<?=$chatId?>';
    var tokenChat = '<?=htmlspecialchars($tokenChat, ENT_QUOTES)?>';
    var remetente = $('#id_user_remetente_<?=$chatId?>').val();
    var destinatario = $('#id_user_destinatario_<?=$chatId?>').val();
    var name = $('#name_<?=$chatId?>').val();
    window.stChatJoinOnce = window.stChatJoinOnce || {};
    var guardKey = (chatId || tokenChat) + ':' + remetente;
    var joinStoreKey = 'stChatJoin:' + tokenChat + ':' + remetente;
    <?php if(count($infoChatMsgSys)<1){ ?>
    var mensagem = name + ' entrou no chat';
    <?php } else { ?>
    var mensagem = name + ' voltou para o chat';
    <?php } ?>
    if (sessionStorage.getItem(joinStoreKey)) {
        window.stChatJoinOnce[guardKey] = true;
        if (typeof stChatTryStartAtendimento === 'function') {
            stChatTryStartAtendimento(chatId, tokenChat);
        }
        return;
    }
    if (window.stChatJoinOnce[guardKey]) {
        if (typeof stChatTryStartAtendimento === 'function') {
            stChatTryStartAtendimento(chatId, tokenChat);
        }
        return;
    }
    if (typeof stChatHasSysMsg === 'function' && stChatHasSysMsg(chatId, mensagem)) {
        window.stChatJoinOnce[guardKey] = true;
        sessionStorage.setItem(joinStoreKey, '1');
        if (typeof stChatTryStartAtendimento === 'function') {
            stChatTryStartAtendimento(chatId, tokenChat);
        }
        return;
    }
    window.stChatJoinOnce[guardKey] = true;
    sessionStorage.setItem(joinStoreKey, '1');
    chatIn(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>', mensagem);
}



<?php if($infoUser['nivel_id']!=4){
                //echo "function load(){ console.log(''); }";
            }
            ?>

<?php if($infoUser['nivel_id']==4){
    $infoMeRapidaChatJson = json_encode($selMen, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
} else {
    $infoMeRapidaChatJson = '[]';
} ?>

function stChatStripPlain_<?=$chatId?>(html) {
    return String(html || '').replace(/<[^>]*>/g, '').replace(/\u00a0/g, ' ').trim();
}

function stChatGetMsgEl_<?=$chatId?>() {
    var $scoped = $('#div_message_<?=$chatId?> textarea#msg_<?=$chatId?>');
    return $scoped.length ? $scoped : $('#msg_<?=$chatId?>').first();
}

function stChatResolveEditor_<?=$chatId?>() {
    var fieldId = 'msg_<?=$chatId?>';
    if (typeof tinymce === 'undefined') {
        return null;
    }
    var ed = tinymce.get(fieldId);
    if (ed && !ed.removed) {
        return ed;
    }
    if (tinymce.activeEditor && !tinymce.activeEditor.removed) {
        var active = tinymce.activeEditor;
        if (active.id === fieldId || (active.targetElm && active.targetElm.id === fieldId)) {
            return active;
        }
    }
    var editors = tinymce.editors || [];
    for (var i = 0; i < editors.length; i++) {
        var candidate = editors[i];
        if (candidate && !candidate.removed && candidate.targetElm && candidate.targetElm.id === fieldId) {
            return candidate;
        }
    }
    return null;
}

function stChatReadFromToxFrame_<?=$chatId?>() {
    var $root = $('#div_message_<?=$chatId?>');
    if (!$root.length) {
        return { html: '', plain: '' };
    }
    var iframe = $root.find('iframe').get(0);
    if (iframe) {
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            if (doc && doc.body) {
                var html = doc.body.innerHTML || '';
                var plain = (doc.body.innerText || doc.body.textContent || '').replace(/\u00a0/g, ' ').trim();
                if (plain || /<img\b/i.test(html)) {
                    if (!plain && /<img\b/i.test(html)) {
                        plain = '[imagem]';
                    }
                    return { html: html, plain: plain };
                }
            }
        } catch (errFrame) {}
    }
    var $editable = $root.find('[contenteditable="true"]').first();
    if ($editable.length) {
        var editHtml = $editable.html() || '';
        var editPlain = ($editable.text() || '').replace(/\u00a0/g, ' ').trim();
        if (editPlain || /<img\b/i.test(editHtml)) {
            if (!editPlain && /<img\b/i.test(editHtml)) {
                editPlain = '[imagem]';
            }
            return { html: editHtml, plain: editPlain };
        }
    }
    return { html: '', plain: '' };
}

function stChatEditorReady_<?=$chatId?>(ed) {
    if (!ed || ed.removed) {
        return false;
    }
    if (typeof ed.initialized !== 'undefined' && !ed.initialized) {
        return false;
    }
    try {
        return !!(ed.getBody && ed.getBody());
    } catch (errReady) {
        return false;
    }
}

function stChatReadComposer_<?=$chatId?>() {
    var fieldId = 'msg_<?=$chatId?>';
    var html = '';
    var plain = '';
    var ed = null;
    if (typeof stChatGetPasteEditor_<?=$chatId?> === 'function') {
        ed = stChatGetPasteEditor_<?=$chatId?>();
    }
    if (!ed) {
        ed = getChatEditor_<?=$chatId?>();
    }

    if (ed) {
        try {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            ed.save();
            html = ed.getContent() || '';
        } catch (errEditor) {
            html = '';
        }
        plain = stChatStripPlain_<?=$chatId?>(html);
        if (!plain) {
            try {
                var body = ed.getBody && ed.getBody();
                if (body) {
                    plain = (body.innerText || body.textContent || '').replace(/\u00a0/g, ' ').trim();
                    if (!html) {
                        html = body.innerHTML || '';
                    }
                }
            } catch (errBody) {}
        }
    }

    if (!html && !plain) {
        var frameData = stChatReadFromToxFrame_<?=$chatId?>();
        html = frameData.html;
        plain = frameData.plain;
    }

    if (!html && !plain) {
        var $visual = $();
        if (typeof stChatGetVisualComposer_<?=$chatId?> === 'function') {
            $visual = stChatGetVisualComposer_<?=$chatId?>();
        }
        if ($visual.length) {
            html = $visual.html() || '';
            plain = stChatStripPlain_<?=$chatId?>(html);
        }
    }

    if (!plain) {
        var $msg = $('#' + fieldId);
        if ($msg.length) {
            try {
                html = $msg.val() || html;
            } catch (errVal) {
                html = html || '';
            }
            plain = stChatStripPlain_<?=$chatId?>(html);
        }
    }

    if (!plain && /<img\b/i.test(html)) {
        plain = '[imagem]';
    } else if (/<img\b/i.test(html) && (/<img/i.test(plain) || plain.length > 200)) {
        plain = '[imagem]';
    }

    return { html: html, plain: plain };
}

function getChatEditor_<?=$chatId?>() {
    var ed = stChatResolveEditor_<?=$chatId?>();
    return stChatEditorReady_<?=$chatId?>(ed) ? ed : null;
}

function getChatInput_<?=$chatId?>() {
    return stChatReadComposer_<?=$chatId?>().html;
}

function getChatPlainText_<?=$chatId?>() {
    return stChatReadComposer_<?=$chatId?>().plain;
}

function setChatInput_<?=$chatId?>(text) {
    var txt = (text == null) ? '' : String(text);
    var $ta = stChatGetMsgEl_<?=$chatId?>();
    $ta.val(txt);
    $('#message_<?=$chatId?>').val(txt);
    $('#div_message_<?=$chatId?> .st-chat-composer-visual').html(txt);
    var editor = getChatEditor_<?=$chatId?>();
    if (editor) {
        editor.setContent(txt);
    }
}

function isChatMsgBlocked_<?=$chatId?>(html, plain) {
    var raw = html || '';
    var text = plain || '';
    if (/<\s*\/?\s*script/i.test(raw) || /<\s*\/?\s*script/i.test(text)) {
        return 'script';
    }
    if (!getChatEditor_<?=$chatId?>() && /<\s*\/?\s*div/i.test(text)) {
        return 'div';
    }
    return '';
}

var stChatEnterSendLock_<?=$chatId?> = false;

function stChatHandleEnterSend_<?=$chatId?>(e) {
    if (e && e.keyCode !== 13 && e.key !== 'Enter') {
        return false;
    }
    if (e && (e.shiftKey || e.ctrlKey || e.altKey || e.metaKey)) {
        return false;
    }
    if (e) {
        e.preventDefault();
        if (typeof e.stopImmediatePropagation === 'function') {
            e.stopImmediatePropagation();
        }
        e.stopPropagation();
    }
    if (stChatEnterSendLock_<?=$chatId?>) {
        return false;
    }
    stChatEnterSendLock_<?=$chatId?> = true;
    setTimeout(function() {
        try {
            var ed = stChatResolveEditor_<?=$chatId?>();
            if (ed) {
                try {
                    ed.save();
                } catch (errSave) {}
            }
            sendChatMessage_<?=$chatId?>();
        } finally {
            setTimeout(function() {
                stChatEnterSendLock_<?=$chatId?> = false;
            }, 250);
        }
    }, 0);
    return false;
}

function sendChatMessage_<?=$chatId?>() {
    var destinatario = $('#id_user_destinatario_<?=$chatId?>').val();
    var composer = stChatReadComposer_<?=$chatId?>();
    var msg = composer.html;
    var plain = composer.plain;
    var chatId = '<?=$chatId?>';

    if (!plain) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Mensagem vazia',
                text: 'Digite uma mensagem antes de enviar.'
            });
        }
        return false;
    }

    var blockedTag = isChatMsgBlocked_<?=$chatId?>(msg, plain);
    if (blockedTag) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: 'Uso de palavras ou caracteres não permitidas no código da mensagem! Por favor revise o texto ou conteúdo inserido/copiado! - &lt;' + blockedTag
        });
        return false;
    }

    function stChatFinishSend_<?=$chatId?>(finalMsg) {
        $('#message_<?=$chatId?>').val(finalMsg);

        try {
            if (typeof chatMsg !== 'function') {
                throw new Error('chatMsg indisponível');
            }
            if (typeof conn === 'undefined' || !conn || conn.readyState !== 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Conexão',
                    text: 'Aguardando conexão com o servidor. Tente novamente em instantes.'
                });
                return false;
            }
            chatMsg(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>');
        } catch (err) {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Erro ao enviar',
                text: 'Não foi possível enviar a mensagem. Verifique a conexão e tente novamente.'
            });
            return false;
        }

        setChatInput_<?=$chatId?>('');
        $('#select_in_chat_<?=$chatId?>').val('');
        if (typeof stChatTypingHide === 'function') {
            stChatTypingHide(chatId);
        }
        if (window.stChatTypingState && window.stChatTypingState[chatId]) {
            window.stChatTypingState[chatId].lastEmit = 0;
        }
        return true;
    }

    if (typeof stChatInlineBlobImages === 'function' && /<img\b/i.test(msg)) {
        stChatInlineBlobImages(msg).then(function (normalized) {
            stChatFinishSend_<?=$chatId?>(normalized || msg);
        }).catch(function () {
            stChatFinishSend_<?=$chatId?>(msg);
        });
        return false;
    }

    return stChatFinishSend_<?=$chatId?>(msg);
}

$('#btn1_<?=$chatId?>').off('click.stChatSend').on('click.stChatSend', function(e) {
    e.preventDefault();
    return sendChatMessage_<?=$chatId?>();
});

$('#select_in_chat_<?=$chatId?>').change(function() {
    var id = $(this).val();
    if (!id) {
        return;
    }
    var infoMeRapida = <?=$infoMeRapidaChatJson?>;
    var resultado = infoMeRapida.find(function(item) {
        return String(item.id_campo) === String(id);
    });
    if (resultado && resultado.txt) {
        setChatInput_<?=$chatId?>(resultado.txt);
    }
});

$('#btn_atent_responsive_<?=$chatId?>').click(function() {
    $('#btn_atent_<?=$chatId?>').click();
});

$('#btn_fin_responsive_<?=$chatId?>').click(function() {
    $('#btn_fin_<?=$chatId?>').click();
});

$('#btn_atent_<?=$chatId?>').click(function() {

    var msg = 'Atenção ao chat foi solicitada!';
    <?php
                    if($infoUser['nivel_id']==4){
                        echo "var msg = 'Backoffice pediu atenção ao chat';";
                    } else {
                        echo "var msg = 'Solicitante pediu atenção ao chat';";
                    }
                ?>

    var remetente = $('#id_user_remetente_<?=$chatId?>').val();
    var destinatario = $('#id_user_destinatario_<?=$chatId?>').val();
    var mensagem = msg;
    var chatId = '<?=$chatId?>';
    chatAtent(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>', mensagem);
    //fimMsg(destinatario, remetente);
});


$('#btn_fin_<?=$chatId?>').click(function() {

    <?php if($infoUser['nivel_id']==4){
                            echo "var msg = 'Atendimento encerrado pelo Backoffice';";
                        } else {
                            echo "var msg = 'Atendimento encerrado pelo Solicitante';";
                        }
                ?>

    var remetente = $('#id_user_remetente_<?=$chatId?>').val();
    var destinatario = $('#id_user_destinatario_<?=$chatId?>').val();
    var mensagem = msg;
    var chatId = '<?=$chatId?>';
    var indice = $('#indice_' + <?=$chatId?>).val();
    var isBko = <?= (int)$infoUser['nivel_id'] === 4 ? 'true' : 'false' ?>;

    if (typeof stChatConfirmFinalize === 'function') {
        stChatConfirmFinalize(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>', mensagem, indice, isBko);
    } else {
        chatFim(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>', mensagem, indice);
    }
    //fimMsg(destinatario, remetente);
});


<?php
// Enter envia mensagem quando "Envio de imagem" está desligado (textarea simples).
$stChatEnterSend = ((int)$infoUser['env_img'] === 0);
$stInvalidElements = 'div,span,a,nav,code,h1,h2,h3,h4,h5,script,style,tr,table,td,javascript';
if ((int)$infoUser['env_img'] === 0) {
    $stInvalidElements .= ',img';
}
?>

<?php if($infoUser['env_img']==1){?>

function stChatGetPasteEditor_<?=$chatId?>() {
    var ed = stChatResolveEditor_<?=$chatId?>();
    if (!ed || ed.removed) {
        return null;
    }
    try {
        return (ed.getBody && ed.getBody()) ? ed : null;
    } catch (errPasteEd) {
        return null;
    }
}

function stChatGetVisualComposer_<?=$chatId?>() {
    var $visual = $('#div_message_<?=$chatId?> .st-chat-composer-visual');
    if (!$visual.length || !$visual.is(':visible')) {
        return $();
    }
    if ($('#div_message_<?=$chatId?> .tox-tinymce').length) {
        return $();
    }
    return $visual;
}

function stChatClearComposerFallback_<?=$chatId?>() {
    $('#div_message_<?=$chatId?> .st-chat-composer-visual').remove();
    if (window.stChatFallbackTimer_<?=$chatId?>) {
        clearTimeout(window.stChatFallbackTimer_<?=$chatId?>);
        window.stChatFallbackTimer_<?=$chatId?> = null;
    }
}

function stChatInsertPastedImage_<?=$chatId?>(dataUrl, editor) {
    if (!dataUrl) {
        return;
    }
    editor = editor || stChatGetPasteEditor_<?=$chatId?>();
    var src = String(dataUrl).trim();
    if (!src) {
        return;
    }

    if (editor) {
        editor.undoManager.transact(function() {
            editor.insertContent(editor.dom.createHTML('img', {
                src: src,
                alt: '',
                style: 'max-width:100%;height:auto;display:block;'
            }));
        });
        editor.nodeChanged();
        editor.focus();
        return;
    }

    var $ce = stChatGetVisualComposer_<?=$chatId?>();
    if (!$ce.length) {
        $ce = $('#div_message_<?=$chatId?>').find('[contenteditable="true"]').first();
    }
    if ($ce.length) {
        $ce.focus();
        $ce.empty();
        var imgNode = document.createElement('img');
        imgNode.src = src;
        imgNode.alt = '';
        imgNode.style.maxWidth = '100%';
        imgNode.style.height = 'auto';
        imgNode.style.display = 'block';
        $ce[0].appendChild(imgNode);
        var $ta = stChatGetMsgEl_<?=$chatId?>();
        if ($ta.length) {
            $ta.val($ce.html());
        }
        return;
    }
}

function stChatExtractImgSrcFromMarkup_<?=$chatId?>(markup) {
    var raw = String(markup || '').trim();
    if (!raw) {
        return '';
    }
    if (/^data:image\//i.test(raw)) {
        return raw;
    }
    var htmlMatch = raw.match(/<img[^>]+src=["']([^"']+)["']/i);
    if (htmlMatch && htmlMatch[1]) {
        return htmlMatch[1];
    }
    var srcMatch = raw.match(/src=["']([^"']+)["']/i);
    if (srcMatch && srcMatch[1]) {
        return srcMatch[1];
    }
    return '';
}

function stChatExtractClipboardImage_<?=$chatId?>(clipboardData) {
    if (!clipboardData) {
        return null;
    }
    var items = clipboardData.items;
    if (items && items.length) {
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (item.type && item.type.indexOf('image') !== -1) {
                return item.getAsFile();
            }
        }
    }
    var files = clipboardData.files;
    if (files && files.length) {
        for (var j = 0; j < files.length; j++) {
            if (files[j].type && files[j].type.indexOf('image') !== -1) {
                return files[j];
            }
        }
    }
    return null;
}

function stChatPreventImagePasteDefault_<?=$chatId?>(event) {
    if (!event) {
        return;
    }
    if (typeof event.preventDefault === 'function') {
        event.preventDefault();
    }
    if (typeof event.stopImmediatePropagation === 'function') {
        event.stopImmediatePropagation();
    }
    if (typeof event.stopPropagation === 'function') {
        event.stopPropagation();
    }
}

function stChatHandleImagePasteEvent_<?=$chatId?>(event, editor) {
    if (!event) {
        return false;
    }
    editor = editor || stChatGetPasteEditor_<?=$chatId?>();
    var cd = event.clipboardData || (window.clipboardData || null);
    if (!cd) {
        return false;
    }

    var imageFile = stChatExtractClipboardImage_<?=$chatId?>(cd);
    if (imageFile) {
        stChatPreventImagePasteDefault_<?=$chatId?>(event);
        var reader = new FileReader();
        reader.onload = function(ev) {
            stChatInsertPastedImage_<?=$chatId?>(ev.target && ev.target.result, editor);
        };
        reader.readAsDataURL(imageFile);
        return true;
    }

    var htmlData = '';
    var plainData = '';
    try {
        htmlData = cd.getData('text/html') || '';
    } catch (errHtml) {}
    try {
        plainData = cd.getData('text/plain') || '';
    } catch (errPlain) {}

    var imgSrc = stChatExtractImgSrcFromMarkup_<?=$chatId?>(htmlData);
    if (!imgSrc) {
        imgSrc = stChatExtractImgSrcFromMarkup_<?=$chatId?>(plainData);
    }
    if (!imgSrc) {
        return false;
    }

    stChatPreventImagePasteDefault_<?=$chatId?>(event);
    stChatInsertPastedImage_<?=$chatId?>(imgSrc, editor);
    return true;
}

function stChatBindEditorImagePaste_<?=$chatId?>(editor) {
    if (!editor || editor.stChatImgPasteBound) {
        return;
    }
    editor.stChatImgPasteBound = true;
    var bindPasteTarget = function(target) {
        if (!target || target.stChatImgPasteBound) {
            return;
        }
        target.stChatImgPasteBound = true;
        target.addEventListener('paste', function(ev) {
            stChatHandleImagePasteEvent_<?=$chatId?>(ev, editor);
        }, true);
    };
    editor.on('init', function() {
        stChatClearComposerFallback_<?=$chatId?>();
        $('#div_message_<?=$chatId?>').addClass('st-chat-tinymce-active');
        stChatGetMsgEl_<?=$chatId?>().addClass('st-chat-force-hidden');
        var doc = editor.getDoc && editor.getDoc();
        var body = editor.getBody && editor.getBody();
        bindPasteTarget(doc);
        if (body && body !== doc) {
            bindPasteTarget(body);
        }
        $('#div_message_<?=$chatId?>').off('click.stChatFocus').on('click.stChatFocus', function(e) {
            if ($(e.target).closest('.tox-tinymce').length) {
                editor.focus();
            }
        });
    });
}

function stChatBindVisualComposerPaste_<?=$chatId?>($visual) {
    if (!$visual || !$visual.length) {
        return;
    }
    $visual.off('paste.stChatImg keydown.chatEnterSend input.stChatSync').on('paste.stChatImg', function(e) {
        if (stChatHandleImagePasteEvent_<?=$chatId?>(e.originalEvent || e, null)) {
            e.preventDefault();
            return false;
        }
    }).on('keydown.chatEnterSend', function(e) {
        stChatHandleEnterSend_<?=$chatId?>(e);
    }).on('input.stChatSync', function() {
        stChatGetMsgEl_<?=$chatId?>().val($visual.html());
    });
}

function stChatBindImagePaste_<?=$chatId?>($ta, editor) {
    if (!$ta || !$ta.length || $ta.hasClass('st-chat-force-hidden')) {
        return;
    }
    if ($ta.hasClass('st-chat-composer-visual')) {
        stChatBindVisualComposerPaste_<?=$chatId?>($ta);
        return;
    }
    $ta.off('paste.stChatImg').on('paste.stChatImg', function(e) {
        if (stChatHandleImagePasteEvent_<?=$chatId?>(e.originalEvent || e, editor || stChatGetPasteEditor_<?=$chatId?>())) {
            e.preventDefault();
            return false;
        }
    });
}

function stChatEnsureComposerVisual_<?=$chatId?>() {
    var $wrap = $('#div_message_<?=$chatId?>');
    if ($wrap.find('.tox-tinymce').length || stChatGetPasteEditor_<?=$chatId?>()) {
        stChatClearComposerFallback_<?=$chatId?>();
        return $();
    }
    var $visual = $wrap.find('.st-chat-composer-visual');
    if ($visual.length) {
        return $visual;
    }
    var $ta = stChatGetMsgEl_<?=$chatId?>();
    $visual = $('<div class="st-chat-composer-visual form-control st-chat-textarea" contenteditable="true" aria-label="Mensagem"></div>');
    $wrap.prepend($visual);
    $ta.addClass('st-chat-force-hidden');
    stChatBindVisualComposerPaste_<?=$chatId?>($visual);
    return $visual;
}

function stChatFallbackTextarea_<?=$chatId?>() {
    var $wrap = $('#div_message_<?=$chatId?>');
    if ($wrap.find('.tox-tinymce').length || stChatGetPasteEditor_<?=$chatId?>()) {
        stChatClearComposerFallback_<?=$chatId?>();
        return;
    }
    if (typeof tinymce !== 'undefined' && tinymce.get('msg_<?=$chatId?>')) {
        return;
    }
    $wrap.find('.tox-tinymce').remove();
    $wrap.removeClass('st-chat-tinymce-active');
    var $ta = $('#msg_<?=$chatId?>');
    $ta.addClass('st-chat-force-hidden');
    $ta.attr({ 'aria-hidden': 'true', tabindex: '-1' });
    $ta.css({ position: 'absolute', left: '-9999px', width: '1px', height: '1px', opacity: 0 });
    var $visual = stChatEnsureComposerVisual_<?=$chatId?>();
    if (!$visual.length) {
        return;
    }
    $visual.css({ visibility: 'visible', minHeight: '100px', display: 'block' });
}

function stChatScheduleComposerFallback_<?=$chatId?>() {
    if (window.stChatFallbackTimer_<?=$chatId?>) {
        clearTimeout(window.stChatFallbackTimer_<?=$chatId?>);
    }
    window.stChatFallbackTimer_<?=$chatId?> = setTimeout(function() {
        window.stChatFallbackTimer_<?=$chatId?> = null;
        var $wrap = $('#div_message_<?=$chatId?>');
        if ($wrap.find('.tox-tinymce').length || stChatGetPasteEditor_<?=$chatId?>()) {
            stChatClearComposerFallback_<?=$chatId?>();
            return;
        }
        stChatFallbackTextarea_<?=$chatId?>();
    }, 6000);
}

function stChatBootTinyMce_<?=$chatId?>() {
    var $wrap = $('#div_message_<?=$chatId?>');
    $wrap.addClass('st-chat-tinymce-loading');
    var boot = (typeof stTinyMceReady === 'function')
        ? stTinyMceReady()
        : Promise.resolve((typeof tinymce !== 'undefined') ? tinymce : null);
    boot.then(function() {
        loadTextarea_<?=$chatId?>();
    }).catch(function() {
        stChatFallbackTextarea_<?=$chatId?>();
    }).finally(function() {
        $wrap.removeClass('st-chat-tinymce-loading');
    });
}

function loadTextarea_<?=$chatId?>() {
    stChatClearComposerFallback_<?=$chatId?>();
    $('#div_message_<?=$chatId?> .st-chat-composer-visual').remove();
    if (typeof tinymce === 'undefined') {
        if (typeof stTinyMceReady === 'function') {
            stChatBootTinyMce_<?=$chatId?>();
            return;
        }
        stChatFallbackTextarea_<?=$chatId?>();
        return;
    }
    <?php if ($stChatTinymcePrehide) { ?>
    $('#div_message_<?=$chatId?>').addClass('st-chat-tinymce-active');
    <?php } ?>
    stChatGetMsgEl_<?=$chatId?>().addClass('st-chat-force-hidden');
    if (tinymce.get('msg_<?=$chatId?>')) {
        tinymce.get('msg_<?=$chatId?>').remove();
    }
    $('#div_message_<?=$chatId?> .tox-tinymce').remove();
    $('textarea#msg_<?=$chatId?>').tinymce({
        menubar: false,
        height: 100,
        min_height: 100,
        statusbar: false,
        plugins: ['autoresize'],
        toolbar: '',
        branding: false,
        promotion: false,
        allow_html_data_urls: true,
        paste_as_text: false,
        extended_valid_elements: 'img[src|alt|width|height|style|class]',
        invalid_elements: <?= json_encode($stInvalidElements, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        content_style: 'body { font-size:12px; margin:8px; } img { max-width:100%; height:auto; display:block; }',
        setup: function(editor) {
            stChatBindEditorImagePaste_<?=$chatId?>(editor);
            editor.on('init', function() {
                stChatClearComposerFallback_<?=$chatId?>();
                $('#div_message_<?=$chatId?>').addClass('st-chat-tinymce-active');
                stChatGetMsgEl_<?=$chatId?>().addClass('st-chat-force-hidden');
                var container = editor.getContainer();
                if (container) {
                    container.style.visibility = 'visible';
                    container.style.minHeight = '100px';
                }
            });
            if (!editor.stChatTypingBound) {
                editor.stChatTypingBound = true;
                editor.on('keydown keyup input ExecCommand Undo Redo SetContent', function() {
                    if (typeof stChatTypingPulse === 'function') {
                        stChatTypingPulse('<?=$chatId?>');
                    }
                });
            }
            if (!editor.stChatEnterBound) {
                editor.stChatEnterBound = true;
                editor.on('keydown', function(e) {
                    if (e.keyCode === 13 && !e.shiftKey) {
                        stChatHandleEnterSend_<?=$chatId?>(e);
                    }
                });
            }
        }
    });
    stChatScheduleComposerFallback_<?=$chatId?>();
}

stChatBootTinyMce_<?=$chatId?>();
<?php } ?>

<?php if ($stChatEnterSend) { ?>
function bindChatEnterSend_<?=$chatId?>() {
    $('#msg_<?=$chatId?>').off('keydown.chatEnterSend').on('keydown.chatEnterSend', function(e) {
        stChatHandleEnterSend_<?=$chatId?>(e);
    });
}
bindChatEnterSend_<?=$chatId?>();
<?php } ?>

$(document).ready(function() {
    if (typeof stChatBindTypingIndicator === 'function') {
        stChatBindTypingIndicator('<?=$chatId?>', <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>');
    }
    if (typeof loadChatIn !== 'undefined') {
        loadChatIn = '';
    }
    $('#tab_<?=$chatId?>').click(function() {
        setTimeout(function() {
            $('#chat-content_<?=$chatId?>').scrollTop(1000000);
        }, 0);
    });
});




window.onunload = function() {
    var chatId = '<?=$chatId?>';
    var destinatario = $('#id_user_destinatario_<?=$chatId?>').val();
    var contrato = '<?=$infoUser['contrato_id']?>';
    var tokenChat = '<?=$tokenChat?>';
    var name = $('#name_<?=$chatId?>').val();
    var mensagem = name + ' fechou a janela do Chat';
    chatIn(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>', mensagem);
};

$('#chat-content_<?=$chatId?>').animate({
    scrollTop: 100000
}, 'slow');

loadFileDiv(<?=$chatId?>);



function loadFileDiv(chatId) {
    var chatId = chatId;
    if (chatId == 'undefined') {
        chatId = <?=$chatId?>;
    }

    //console.log(chatId);
    var feed = '#files_deposit_' + chatId;
    var token = '<?=$tokenChat?>';
    $.post("staff/load_deposit_file.php", {
            token,
            chatId
        },
        function(valor) {
            $(feed).html(typeof stSafeChatHtml === 'function' ? stSafeChatHtml(valor) : valor);
        });

}
</script>


<button type="button" id="btn_pos_<?=$chatId?>" class="btn btn-secondary st-chat-pos-trigger" data-bs-toggle="modal"
    data-bs-target="#div_pos_<?=$chatId?>" hidden aria-hidden="true" tabindex="-1">PÓS</button>


<!-- MODAL FINALIZAÇÃO -->
<?php if($infoUser['nivel_id']==5){ ?>
<div class="modal fade" id="div_pos_<?=$chatId?>" tabindex="-1" aria-labelledby="exampleModalLabel"
    data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Pós-Atendimento</h1>
                    <button type="button" id="close" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div id="body-pos" class="modal-body" style="max-height: 350px; overflow: auto;">
                <style>
                .star-sel {
                    color: #FCD703;
                }

                .star-dis {
                    color: silver;
                }

                #star_class {
                    display: none;
                }
                </style>
                <h5>Classifique como foi o atendimento recebido:</h3>
                    <p>A sua classificação ficará disponível após 5 dias</p>
                    <div id="classificacao">
                        <center>
                            <i class="fas fa-star fa-5x star-dis" id="star_1" onclick="clickStar(1)"></i>
                            <i class="fas fa-star fa-5x star-dis" id="star_2" onclick="clickStar(2)"></i>
                            <i class="fas fa-star fa-5x star-dis" id="star_3" onclick="clickStar(3)"></i>
                            <i class="fas fa-star fa-5x star-dis" id="star_4" onclick="clickStar(4)"></i>
                            <i class="fas fa-star fa-5x star-dis" id="star_5" onclick="clickStar(5)"></i>
                            <input type="text" name="star_class" id="star_class">
                        </center>
                    </div>
                    <script>
                    function clickStar(star) {
                        var star = star;
                        for (var i = 1; i <= 5; i++) {
                            var div = '#star_' + i;
                            $(div).removeClass('star-dis');
                            $(div).removeClass('star-sel');
                            $(div).addClass('star-dis');
                        }
                        for (var i = star; i >= 1; i--) {
                            var div = '#star_' + i;
                            $(div).removeClass('star-dis');
                            $(div).addClass('star-sel');
                        }
                        $('#star_class').val(star);
                    }
                    </script>


            </div>
            <div class="modal-footer">
                <div id="save_feed"></div>
                <button type="button" id="save_5" class="btn btn-success"><i class="fas fa-save"></i></button>
            </div>

            <script>
            $(document).ready(function() {
                $('#save_5').click(function() {
                    var feed = '#save_feed';
                    var star = parseInt($('#star_class').val(), 10);
                    var tokenChat = '<?=$tokenChat?>';
                    if (!star || star < 1 || star > 5) {
                        $(feed).html('<span class="text-danger">Selecione de 1 a 5 estrelas.</span>');
                        return;
                    }
                    var $btn = $(this);
                    $btn.prop('disabled', true);
                    $(feed).html(
                        '<center><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></center>'
                    );
                    $.ajax({
                        url: 'staff/save_class.php',
                        type: 'POST',
                        dataType: 'json',
                        data: { star: star, tokenChat: tokenChat }
                    }).done(function(retorno) {
                        if (retorno && retorno.ok) {
                            $(feed).html('<span class="text-success">' + stChatEscHtml(retorno.msg || 'Salvo!') + '</span>');
                            if (typeof stChatSolAfterClassSave === 'function') {
                                setTimeout(function () { stChatSolAfterClassSave(); }, 250);
                            }
                            return;
                        }
                        $btn.prop('disabled', false);
                        $(feed).html('<span class="text-danger">' + stChatEscHtml((retorno && retorno.msg) ? retorno.msg : 'Não foi possível salvar.') + '</span>');
                    }).fail(function() {
                        $btn.prop('disabled', false);
                        $(feed).html('<span class="text-danger">Falha de comunicação. Tente novamente.</span>');
                    });
                });

                $('#save_4').click(function() {
                    var feed = '#save_feed';
                    var fila_id = '<?=$infFila['fila_id']; ?>';
                    var assunto = $('input[name=assunto]:checked').val();
                    var pausa = $('#pausa_bko').val();
                    var situacao_dem = $('#situacao_dem').val();
                    var motivo_situacao = $('#motivo_situacao').val();
                    var tokenChat = '<?=$tokenChat?>';
                    <?php
                                $sql = "SELECT nome_campo, input_id, (SELECT tipo_input from tbl_forms_pos_input where id_input=input_id) as tipo_input FROM tbl_forms_pos_input_campo where fila_id=?";
                                //echo "<br>".$sql;
                                $stmt = $PDO->prepare($sql);
                                $result = $stmt->execute([(int) $infFila['fila_id']]);
                                $campoScript = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                if(count($campoScript)>0){
                                    for($num=0;$num<count($campoScript);$num++){
                                        if($campoScript[$num]['tipo_input']!='checkbox'){
                                            echo 'var '.$campoScript[$num]['nome_campo'].'= $("#'.$campoScript[$num]['nome_campo'].'").val();'."\n";
                                        } else {
                                            echo 'var '.$campoScript[$num]['nome_campo'].'= $("input:radio[name='.$campoScript[$num]['nome_campo'].']:checked").val();'."\n";
                                        }

                                    }
                                }
                            ?>
                    //console.log(assunto);

                    $(feed).html(
                        '<center><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></center>'
                    );
                    var postData = {
                        assunto: assunto,
                        tokenChat: tokenChat,
                        fila_id: fila_id,
                        pausa: pausa,
                        situacao_dem: situacao_dem,
                        motivo_situacao: motivo_situacao
                    };
                    <?php
                                    if (count($campoScript) > 0) {
                                        for ($num = 0; $num < count($campoScript); $num++) {
                                            echo 'postData.' . $campoScript[$num]['nome_campo'] . ' = ' . $campoScript[$num]['nome_campo'] . ";\n";
                                        }
                                    }
                                ?>
                    $.post("staff/save_pos.php", postData,
                        function(valor) {
                            $(feed).html(valor);
                        });


                });
            });
            </script>

        </div>
    </div>
</div>
<?php } ?>

<!-- MODAL FILE -->
<div class="modal fade" id="div_file_<?=$chatId?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Enviar um Arquivo</h1>
                    <button type="button" id="close_file_<?=$chatId?>" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <div id="div_file_inp_<?=$chatId?>" class="upload-file">
                        <h5>Tamanho de arquivo permitido até 5mb</h5>
                        </h5>
                        <label id="lbl_input_<?=$chatId?>" for="file_<?=$chatId?>"><i
                                class="fas fa-upload fa-10x"></i></label>
                        <input id="file_<?=$chatId?>" name="file_<?=$chatId?>" type="file" style="width: 100%"
                            accept=".jpg, .png, .doc, .docx, .xls, .xlsx, .pdf" />
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

                    <div id="div_bar_<?=$chatId?>" class="div_bar">
                        <progress id="bar_<?=$chatId?>" value="0" max="100" class="class_bar"><br></progress><span
                            id="porcentagem_<?=$chatId?>">0%</span>
                    </div>

                    <input type="text" id="ipt_file_<?=$chatId?>" style="display: none">


                    <div id="status_file_<?=$chatId?>"></div>

                </center>


            </div>
            <div class="modal-footer">
                <div id="save_feed"></div>
                <button type="button" id="save_file_<?=$chatId?>" class="btn btn-success" title="Enviar aquivo"
                    disabled><i class="fas fa-paper-plane"></i></button>
            </div>


            <script>
            $(document).ready(function() {
                var form;
                $('#file_<?=$chatId?>').change(function(event) {
                    $('#div_file_inp_<?=$chatId?>').hide();
                    $('#div_bar_<?=$chatId?>').show();
                    form = new FormData();
                    form.append('arquivo', event.target.files[0]);
                    let token = '<?=$tokenChat?>';
                    let dest = '<?=$userDestinatario?>';
                    let chatId = '<?=$chatId?>';
                    token = JSON.stringify(token);
                    token = btoa(token);
                    //console.log( token );
                    form.append('token', token);
                    form.append('dest', dest);
                    form.append('chatId', chatId);
                    $('#status_file_<?=$chatId?>').html('Carregando...');
                    $.ajax({
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();

                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = evt.loaded / evt.total;
                                    percentComplete = parseInt(percentComplete *
                                        100);
                                    //console.log(percentComplete);
                                    $('#bar_<?=$chatId?>').val(percentComplete);
                                    $('#porcentagem_<?=$chatId?>').html(
                                        percentComplete + '%');
                                    if (percentComplete === 100) {
                                        $('#bar_<?=$chatId?>').val(percentComplete);
                                        $('#porcentagem_<?=$chatId?>').html(
                                            percentComplete + '%');

                                        $('#bar_<?=$chatId?>').fadeOut();
                                        $('#porcentagem_<?=$chatId?>').fadeOut()
                                    }

                                }
                            }, false);

                            return xhr;
                        },
                        url: 'staff/load_file.php',
                        data: form,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: function(valor) {
                            //console.log(valor);
                            $('#status_file_<?=$chatId?>').html(valor);
                            //zera_file();
                        }
                    });

                });

                $('#save_file_<?=$chatId?>').click(function() {
                    var feed = '#status_file_<?=$chatId?>';
                    var file = $('#ipt_file_<?=$chatId?>').val();
                    var rem = $('#id_user_remetente_<?=$chatId?>').val();
                    var chatId = '<?=$chatId?>';
                    var name = $('#name_<?=$chatId?>').val();
                    var mensagem = name + ' enviou um arquivo';
                    var tokenChat = '<?=$tokenChat?>';
                    $(feed).html(
                        '<center><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></center>'
                    );
                    $.post("staff/save_file.php", {
                            file,
                            tokenChat,
                            rem,
                            chatId
                        },
                        function(valor) {
                            $(feed).html(valor);
                            //console.log(name_file);
                            var mensagem = name + ' enviou o arquivo ' + name_file;
                            chatIn(chatId, <?=$userDestinatario?>, <?=$infoUser['contrato_id']?>,
                                '<?=$tokenChat?>', mensagem, indice);
                            $('#close_file_<?=$chatId?>').click();
                            $('#save_file_<?=$chatId?>').prop('disabled', true);
                        });
                });



                $('#close_file_<?=$chatId?>').click(function() {
                    zera_file();
                });


                function zera_file() {
                    $('#status_file_<?=$chatId?>').html('');
                    $('#bar_<?=$chatId?>').val('0');
                    $('#porcentagem_<?=$chatId?>').html('0%');
                    $('#div_bar_<?=$chatId?>').hide();
                    $('#file_<?=$chatId?>').val('');
                    $('#div_file_inp_<?=$chatId?>').show();

                    form = '';

                }


            });
            </script>

        </div>
    </div>
</div>


<!-- MODAL TRASNFER -->
<div class="modal fade" id="div_transfer_<?=$chatId?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 100%;">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Transferir Atendimento</h1>
                    <button type="button" id="close_fila_<?=$chatId?>" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <center>
                    <div class="content-10-line">
                        <div id="div_fila_<?=$chatId?>" class="input-container">
                            <select name="fila_<?=$chatId?>" id="fila_<?=$chatId?>">
                                <option value="">Selecione uma fila?</option>
                                <?php
                            $contratoParts = preg_split('/\s*,\s*/', trim((string) ($infFila['contrato_id'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);
                            $contratoIds = [];
                            foreach ($contratoParts as $contratoPart) {
                                $cid = (int) $contratoPart;
                                if ($cid > 0) {
                                    $contratoIds[$cid] = $cid;
                                }
                            }
                            if ($contratoIds === []) {
                                $contratoIds = [0];
                            }
                            $contratoPlaceholders = implode(',', array_fill(0, count($contratoIds), '?'));
                            $filaAtualId = (int) ($infFila['fila_id'] ?? 0);
                            $sql="SELECT id_fila, nome_fila, ativo from tbl_config_fila where ativo=1 and contrato_id in ($contratoPlaceholders) and id_fila<>? order by nome_fila asc";
                            //echo "<br>".$sql;
                            $stmt = $PDO->prepare($sql);
                            $result = $stmt->execute(array_merge(array_values($contratoIds), [$filaAtualId]));
                            $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
                            for($x=0;$x<count($dados);$x++){
                                echo '<option value="'.$dados[$x]['id_fila'].'">'.$dados[$x]['nome_fila'].'</option>';
                            }
                        ?>
                            </select>
                        </div>
                    </div>
                    <div class="content-10-line">
                        <div id="div_assunto" class="input-container">
                            <select name="assunto_<?=$chatId?>" id="assunto_<?=$chatId?>">
                                <option>Selecione o Assunto...</option>
                            </select>
                        </div>
                    </div>
                    <input type="text" id="ipt_fila_<?=$chatId?>" style="display: none;">

                </center>


            </div>
            <div class="modal-footer">
                <div id="save_feed"></div>
                <button type="button" id="save_trasnfer_<?=$chatId?>" class="btn btn-success"
                    title="Tranferir Atendimento"><i class="fas fa-share-square"></i></button>
            </div>

            <script>
            $(document).ready(function() {

                $("#fila_<?=$chatId?>").change(function() {
                    var fila = $('#fila_<?=$chatId?>').val();
                    loadAss(fila);
                    loadFila(fila);
                });



                function loadAss(fila) {
                    //console.log(fila);
                    $("#assunto_<?=$chatId?>").html('<option>Carregando Assuntos...</option>');
                    $.post("staff/load_ass.php", {
                            fila
                        },
                        function(valor) {
                            $("#assunto_<?=$chatId?>").html(typeof stSafeChatHtml === 'function' ? stSafeChatHtml(valor) : valor);
                        });
                }

                function loadFila(fila) {
                    //console.log(fila);
                    //$("#ipt_fila_<?=$chatId?>").html('<option>Carregando Assuntos...</option>');
                    $.post("staff/load_fila_atend.php", {
                            fila
                        },
                        function(valor) {
                            $("#ipt_fila_<?=$chatId?>").val(valor);
                            //console.log(valor);
                        });
                }

                $('#save_trasnfer_<?=$chatId?>').click(function() {
                    $('#close_fila_<?=$chatId?>').click();

                    var msg = 'Atendimento trasnferido para ' + $("#ipt_fila_<?=$chatId?>").val();
                    var remetente = $('#id_user_remetente_<?=$chatId?>').val();
                    var destinatario = $('#id_user_destinatario_<?=$chatId?>').val();
                    var fila = $('#ipt_fila_<?=$chatId?>').val();
                    var assunto = $('#assunto_<?=$chatId?>').val();
                    var chatId = '<?=$chatId?>';
                    var mensagem = msg;
                    //console.log('salva_transfer_1');
                    chatTransfer(chatId, destinatario, <?=$infoUser['contrato_id']?>, '<?=$tokenChat?>',
                        mensagem);
                    //$('#close_fila_<?=$chatId?>').click();
                });



            });
            </script>

        </div>
    </div>
</div>
