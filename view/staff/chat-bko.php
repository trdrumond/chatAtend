<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

//depurador($_POST);


$indDiv = isset($_POST['indice']) ? (int)$_POST['indice'] : 1;
$contratoUser = (int)($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);
$bkoId = (int)$infoUser['id_user'];
$protocoloPost = !empty($_POST['protocolo']) ? trim((string)$_POST['protocolo']) : '';
$filaIdPref = (int)($infoUser['fila_id'] ?? 0);
$idFilaChatPost = isset($_POST['id_fila_chat']) ? (int)$_POST['id_fila_chat'] : 0;

$filasIn = '';
if ($protocoloPost === '') {
    $stmt = $PDO->prepare('SELECT filas FROM tbl_user_filas WHERE user_id=? LIMIT 1');
    $stmt->execute([$bkoId]);
    $cnfFilas = $stmt->fetch(PDO::FETCH_ASSOC);
    $filasIn = !empty($cnfFilas['filas']) ? (string)$cnfFilas['filas'] : '';
}

$bkoCtx = stChatBkoBootstrap($PDO, $bkoId, $protocoloPost, $contratoUser, $filaIdPref, $filasIn, $indDiv, $idFilaChatPost);
$infFila = $bkoCtx['infFila'];
$infoChat = $bkoCtx['infoChat'];

if ($bkoCtx['state'] === 'closed') {
    echo '<div data-st-bko-fail="1" data-st-bko-closed="1"></div>';
    exit;
}

if (empty($infFila['id_fila_chat']) || $bkoCtx['state'] === 'taken' || $bkoCtx['state'] === 'no_fila') {
    echo '<div data-st-bko-fail="1"></div>';
    exit;
}

if (empty($infoChat['id_chat'])) {
    echo '<div data-st-bko-fail="1" data-st-bko-wait="1"></div>';
    exit;
}

?>

<style>
.file_chat {
    margin: 10px;
    color: var(--c-muted, #717377);
    text-align: center;
}

.file_chat > a {
    color: var(--c-navy, #28285E);
}

.label-perfil {
    font-size: 10px;
    color: #B7202F;
}

.info-perfil {
    font-size: 12px;
    padding-left: 10px;
}
</style>

<script>
(function (tabInd) {
    tabInd = parseInt(tabInd, 10) || 1;
    var spans = $('.tab');
    var qtd_span = spans.length;

    userRem = <?=$infoUser['id_user']?>;
    nivel = <?=$infoUser['nivel_id']?>;

<?php if (!empty($infFila['id_fila_chat'])) { ?>
    if (typeof window.stBkoMarkChatOpen === 'function') {
        window.stBkoMarkChatOpen(tabInd, true);
    } else if (typeof window.stBkoChatAberto !== 'undefined') {
        window.stBkoChatAberto[tabInd] = true;
    }
<?php } ?>
    if ($('#title-' + tabInd).hasClass('active-tab')) {
        window.stBkoIndiceAtivo = tabInd;
    }

    $('#menu_bko').hide();
    $('#sair').hide();

    if (qtd_span < (window.qtdMax || 1)) {
        $("#btn-add-tab").attr('disabled', false);
    }

    if (typeof conn !== 'undefined' && conn.readyState === 1) {
        conn.send(JSON.stringify({ flagBko: 'true' }));
        conn.send(JSON.stringify({ flagAtend: 'true' }));
    }

    var notifyTries = 0;
    function stBkoNotifySolicitante() {
        if (typeof conn !== 'undefined' && conn.readyState === 1) {
            conn.send(JSON.stringify({ flagBko: 'true' }));
            conn.send(JSON.stringify({ flagAtend: 'true' }));
            if (typeof sendAtend === 'function') {
                sendAtend();
            }
            return;
        }
        if (notifyTries < 8) {
            notifyTries += 1;
            setTimeout(stBkoNotifySolicitante, 400);
        }
    }
    stBkoNotifySolicitante();
    setTimeout(stBkoNotifySolicitante, 2000);
})(<?= (int)$indDiv ?>);
</script>

<?php

    $sql="SELECT a.id_user, a.nome_usuario, a.nome, a.sobrenome, concat(a.nome, ' ', a.sobrenome) as nome_completo, a.email, a.agencia_id, d.nome_agencia, a.empresa_id, (SELECT nome_empresa from tbl_empresa where id_empresa=empresa_id) as nome_empresa, b.img"
    ." FROM tbl_user a, tbl_user_img_perfil b, tbl_agencia d"
    ." WHERE a.agencia_id=d.id_agencia"
    ." and a.id_user=b.user_id"
    ." AND a.id_user=".$infFila['ate_resp'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dados_ate = $stmt->fetch( PDO::FETCH_ASSOC );

    //if($dados_ate['email']!=''){ $mail = '('.$dados_ate['email'].')';} else {$mail='';}
    //var_dump($dados_ate);

    if($infoUser['id_user']==3324){
        //echo "<br>".$sql;
        //depurador($dados_ate);
    }

    $userDestinatario = $infFila['ate_resp'];

    $tokenChat = !empty($infoChat['token_chat']) ? (string)$infoChat['token_chat'] : '';

    if (!empty($infoChat['id_chat'])
        && (empty($infoChat['fila_chat_id']) || (int)$infoChat['fila_chat_id'] !== (int)$infFila['id_fila_chat'])
    ) {
        $stmtLink = $PDO->prepare(
            'UPDATE tbl_chat_info SET fila_chat_id=?, indice=?, status_chat=1 WHERE id_chat=? AND contrato_id=? AND status_chat=1'
        );
        $stmtLink->execute([(int)$infFila['id_fila_chat'], (string)$indDiv, (int)$infoChat['id_chat'], $contratoUser]);
        $infoChat['fila_chat_id'] = $infFila['id_fila_chat'];
    }

        $sql="SELECT id from tbl_tma_atend where fila_chat_id is null and resp_id=".$infoUser['id_user'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();
        $infoAtend = $stmt->fetch( PDO::FETCH_ASSOC );

        if($infoAtend['id']!=''){
            $sql="UPDATE tbl_tma_atend SET fila_chat_id=".$infFila['id_fila_chat'].", chat_id=".$infoChat['id_chat'].", fila_id=".$infFila['fila_id']." where id=".$infoAtend['id'];
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
        }

    $sql="SELECT id_faq, titulo_faq, txt from tbl_faq where fila_id=".$infFila['fila_id']." and (assunto_id=0 or assunto_id=".$infFila['assunto_id'].")";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $infoFaq = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($infoFaq);


    $chatId = $infoChat['id_chat'];
?>

<script>
(function (tabInd) {
    tabInd = parseInt(tabInd, 10) || 1;
    $('#title-' + tabInd).html(
        '<?= ucwords((strtolower($dados_ate['nome'])).' '.(strtolower($dados_ate['sobrenome'][0])))."."; ?>');
})(<?= (int)$indDiv ?>);
</script>

<div id="bko-workspace-<?= (int)$indDiv ?>" class="st-chat-workspace st-chat-workspace--bko"
     data-chat-id="<?= (int)$chatId ?>"
     data-token-chat="<?= htmlspecialchars($tokenChat, ENT_QUOTES, 'UTF-8') ?>"
     data-contrato-id="<?= (int)$infoUser['contrato_id'] ?>">
    <header id="topo_dash" class="st-chat-bko-header">
        <div id="info" class="st-chat-bko-header__main">
            <div id="prot" class="st-chat-bko-header__protocol">
                <i class="fas fa-headset" aria-hidden="true"></i>
                <span>Protocolo <strong><?= htmlspecialchars($infFila['protocolo']) ?></strong></span>
            </div>
            <div class="info st-chat-bko-solicitante">
                <div class="info-pad st-chat-bko-solicitante__name">
                    <i class="far fa-user" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($dados_ate['nome_completo']) ?></span>
                </div>
                <div class="info-pad st-chat-bko-solicitante__mail">
                    <i class="far fa-envelope" aria-hidden="true"></i>
                    <a href="mailto:<?= htmlspecialchars($dados_ate['email']) ?>?subject=Solvetask <?= htmlspecialchars($infFila['protocolo']) ?>"><?= htmlspecialchars($dados_ate['email']) ?></a>
                </div>
                <div class="info-pad st-chat-bko-solicitante__unit">
                    <i class="far fa-building" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($dados_ate['nome_empresa']) ?> — <?= htmlspecialchars($dados_ate['nome_agencia']) ?></span>
                </div>
            </div>
        </div>
        <div id="div_tempo_<?=$chatId;?>" class="st-chat-bko-timers div_tempo">
            <div id="div_te_<?=$_POST['indice'];?>" class="st-chat-timer div_te"><i class="fas fa-history" aria-hidden="true"></i> TE: <?= htmlspecialchars($infFila['te']) ?></div>
            <div id="div_ta_<?=$infoChat['id_chat'];?>" class="st-chat-timer st-chat-timer--ta div_te"></div>
        </div>
    </header>

    <div class="st-chat-bko-body">
    <div id="div_ope" class="st-chat-main">
        <?php include("../chat/chat_ind.php"); ?>

    </div>
    <aside id="div_info" class="st-chat-side">
        <ul class="nav nav-tabs st-chat-tabs" id="tabChat" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="proc-tab_<?=$chatId;?>" data-bs-toggle="tab"
                    data-bs-target="#proc_<?=$chatId;?>" type="button" role="tab" aria-controls="proc_<?=$chatId;?>"
                    aria-selected="true"><i class="fas fa-bars"></i> Procedimento</button>
            </li>
            <?php if(count($infoFaq)>0){ ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="faq-tab_<?=$chatId;?>" data-bs-toggle="tab"
                    data-bs-target="#tab_faq_<?=$chatId;?>" type="button" role="tab"
                    aria-controls="tab_faq_<?=$chatId;?>" aria-selected="false"><i class="fas fa-question-circle"></i>
                    FAQ</button>
            </li>
            <?php } ?>
            <?php if($infoUser['env_file']==1){ ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="file-tab_<?=$chatId;?>" data-bs-toggle="tab"
                    data-bs-target="#tab_file_<?=$chatId;?>" type="button" role="tab"
                    aria-controls="tab_file_<?=$chatId;?>" aria-selected="false"><i class="fas fa-folder-open"></i>
                    Depósito de arquivos</button>
            </li>
            <?php } ?>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="fila_atual-tab_<?=$chatId;?>" data-bs-toggle="tab"
                    data-bs-target="#fila_atual_<?=$chatId;?>" type="button" role="tab"
                    aria-controls="fila_atual_<?=$chatId;?>" aria-selected="false"><i class="fas fa-bars"></i>
                    Fila</button>
            </li>
        </ul>

        <div class="tab-content st-chat-side__content" id="myTabContent">
            <div class="tab-pane fade show active st-chat-pane" id="proc_<?=$chatId;?>" role="tabpanel"
                aria-labelledby="proc-tab_<?=$chatId;?>">
                <?php
                        $sql="SELECT titulo_assunto, procedimento, date_format(data_alt, '%d/%m/%Y %H:%i:%s') as data_alt, date_format(data_alt, '%Y-%m-%d') as data_ver from tbl_assunto where id_assunto=".$infFila['assunto_id'];
                        //echo "<br>".$sql;
                        $stmt = $PDO->prepare($sql);
                        $result = $stmt->execute();
                        $infoAssunto = $stmt->fetch( PDO::FETCH_ASSOC );
                        if($infoAssunto['procedimento']==''){$infoAssunto['procedimento'] = '<br><br><center><h6>Sem informações de procedimento no sistema.<h6></center>';}

                    ?>
                <h4 class="st-chat-pane__title"><i class="fas fa-book-open" aria-hidden="true"></i> <?= htmlspecialchars($infoAssunto['titulo_assunto']) ?></h4>
                <?php
                            $data_ver = date('Y-m-d', strtotime("+5 days",strtotime($infoAssunto['data_ver'])));
                            if(date('Y-m-d') > $data_ver){
                                $badge = 'secondary';
                            } else {
                                $badge = 'danger';
                            }

                        ?>
                <p class="st-chat-pane__meta">
                    <strong>Procedimento</strong>
                    <span class="badge bg-<?=$badge; ?>">Atualizado: <?= htmlspecialchars($infoAssunto['data_alt']) ?></span>
                </p>
                <div id="proced_<?= (int)$chatId ?>" class="st-chat-pane__body st-chat-proc-body"><?=$infoAssunto['procedimento']; ?></div>
                <!-- <button type="button" class="btn btn-danger" id="btn_call_ate">Chamar</button> -->


            </div>
            <?php if(count($infoFaq)>0){ ?>
            <div class="tab-pane fade" id="tab_faq_<?=$chatId;?>" role="tabpanel"
                aria-labelledby="file-faq_<?=$chatId;?>">
                <div class="accordion accordion-flush" id="faq_accordion">
                    <?php for($x=0;$x<count($infoFaq);$x++){ ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-<?=$infoFaq[$x]['id_faq']?>" aria-expanded="false"
                                aria-controls="flush-<?=$infoFaq[$x]['id_faq']?>">
                                <?=$infoFaq[$x]['titulo_faq']?>
                            </button>
                        </h2>
                        <div id="flush-<?=$infoFaq[$x]['id_faq']?>" class="accordion-collapse collapse"
                            aria-labelledby="flush-headingOne" data-bs-parent="#faq_accordion">
                            <div class="accordion-body"><?=$infoFaq[$x]['txt']?></div>
                        </div>
                    </div>
                    <?php } ?>

                </div>
            </div>
            <?php } ?>
            <?php if($infoUser['env_file']==1){ ?>
            <div class="tab-pane fade" id="tab_file_<?=$chatId;?>" role="tabpanel"
                aria-labelledby="file-tab_<?=$chatId;?>">
                <div id="files_deposit_<?=$chatId;?>"></div>
            </div>
            <?php } ?>

            <div class="tab-pane fade" id="fila_atual_<?=$chatId;?>" role="tabpanel"
                aria-labelledby="fila_atual-tab_<?=$chatId;?>">
                <div id="fila_ativa_<?=$chatId;?>" class="fila_ativa"></div>
            </div>
        </div>
    </aside>
    </div>
</div>

<script>
(function (tabInd) {
    tabInd = parseInt(tabInd, 10) || 1;
    var chatId = <?= (int)$chatId ?>;
    var tokenChat = <?= json_encode($tokenChat ?? '') ?>;
    var protocolo = <?= json_encode($infFila['protocolo'] ?? '') ?>;
    var taWaitKey = 'stBkoTaWait_' + chatId;

    function syncTaInicio() {
        var $ta = $('#div_ta_' + chatId);
        if ($ta.length && !$ta.data('st-ta-running')) {
            $ta.html("<i class='far fa-clock'></i> TA: aguardando participantes");
        }
        if (typeof stChatTryStartAtendimento === 'function') {
            stChatTryStartAtendimento(String(chatId), tokenChat);
        }
    }

    syncTaInicio();
    if (window[taWaitKey]) {
        clearInterval(window[taWaitKey]);
    }
    window[taWaitKey] = setInterval(syncTaInicio, 2500);

    if (typeof stBkoStartFilaPoll === 'function') {
        stBkoStartFilaPoll(chatId, <?= (int)$infoUser['fila_id'] ?>, tabInd);
    }
    if (typeof window.stBkoRegisterTab === 'function') {
        window.stBkoRegisterTab(tabInd, chatId, protocolo);
    }
})(<?= (int)$indDiv ?>);

$('#btn_call_ate').click(function () {
    sendAtend();
});
</script>
