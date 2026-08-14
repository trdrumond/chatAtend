<?php
include("cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

$filaId = (int) ($infoUser['fila_id'] ?? 0);
$bkoId = (int) ($_SESSION['dados']['id_user'] ?? 0);
$contratoId = (int) ($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0);

$sql="SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, timediff(now(), data_hora) as te_diff, te, hora_inicio from tbl_chat_fila where ".stFilaSqlAtendimentoAtivo()." and fila_id=? and bko_resp=?";
//echo "<br>".$sql;
$st = $PDO->prepare($sql);
$res = $st->execute([$filaId, $bkoId]);
$infFila = $st->fetch( PDO::FETCH_ASSOC );
/*
echo "<br>";
depurador($infFila);
echo "<br>";
*/
//depurador($infFila);

if($infFila['id_fila_chat']==''){
    $sqlVer="SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, timediff(now(), data_hora) as te_diff, te from tbl_chat_fila where status_fila=1 and fila_id=? and bko_resp is null order by id_fila_chat asc limit 1";
    //echo "<br>".$sqlVer;
    $stmt = $PDO->prepare($sqlVer);
    $result = $stmt->execute([$filaId]);
    $infFila = $stmt->fetch( PDO::FETCH_ASSOC );
    if($infFila['te']==''){$infFila['te']=$infFila['te_diff'];}
    //echo "<br>".$infFila['te'];
}

//depurador($infFila);

if($infFila['id_fila_chat']==''){
    echo "<script>setTimeout(function(){ actionPage('dash-ate', 'idx'); }, 1000);</script>";
} else {

    if($infFila['bko_resp']==''){
        stFilaEnsureSituacaoAguardando($PDO);
        $teVal = (string) ($infFila['te'] ?? '');
        $sql="UPDATE tbl_chat_fila SET status_fila=?, bko_resp=?, te=? where id_fila_chat=?";
        $stmt = $PDO->prepare( $sql );
        $stmt->execute([ST_FILA_AGUARDANDO_ATENDIMENTO, (int) $infoUser['id_user'], $teVal, (int) $infFila['id_fila_chat']]);
        logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Tratamento');
        $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
        $infFila['bko_resp'] = $infoUser['id_user'];
    } elseif ((int)$infFila['status_fila'] === ST_FILA_NA_FILA && (int)$infFila['bko_resp'] === (int)$infoUser['id_user']) {
        stFilaEnsureSituacaoAguardando($PDO);
        $stmt = $PDO->prepare('UPDATE tbl_chat_fila SET status_fila=? WHERE id_fila_chat=? AND bko_resp=? AND status_fila=?');
        $stmt->execute([ST_FILA_AGUARDANDO_ATENDIMENTO, (int)$infFila['id_fila_chat'], (int)$infoUser['id_user'], ST_FILA_NA_FILA]);
        $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
    }

    $sql="SELECT a.id_user, a.nome_usuario, a.nome, a.sobrenome, concat(a.nome, ' ', a.sobrenome) as nome_completo, a.email, a.contrato_id,"
    ." b.nome_contrato, a.municipio_id, c.nome_municipio, a.agencia_id, d.nome_agencia, a.uf_id, e.nome_estado, e.uf, a.nivel_id, a.regional_id, f.nome_regional,"
    ." g.nome_nivel, a.ativo, date_format(a.data_cad, '%d/%m/%Y') as data_cad, date_format(a.data_inativo, '%d/%m/%Y') as data_inativo, a.ativo"
    ." FROM tbl_user a, tbl_contrato b, tbl_municipio c, tbl_agencia d, tbl_estado e, tbl_nivel g, tbl_regional f"
    ." WHERE a.contrato_id=b.id_contrato"
    ." AND a.municipio_id=c.id_municipio"
    ." AND a.agencia_id=d.id_agencia"
    ." AND a.uf_id=e.id_estado"
    ." AND a.regional_id=f.id_regional"
    ." AND a.nivel_id=g.id_nivel"
    ." AND a.id_user=?";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([(int) $infFila['ate_resp']]);
    $dados_ate = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<script>
    userRem = <?=$infoUser['id_user']?>;
    nivel = <?=$infoUser['nivel_id']?>;
    $('#dash-ate').hide();
    $('#my-score').hide();
    $('#hist-dash').hide();
    $('#sair').hide();
    setTimeout(function() {sendBko();}, 500);
    setTimeout(function() {
        if (typeof sendAtend === 'function') {
            sendAtend();
        }
    }, 800);
</script>

<div id="dashboard" class="st-chat-workspace st-chat-workspace--bko">
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
                    <span><?= htmlspecialchars($dados_ate['nome_agencia']) ?></span>
                </div>
            </div>
        </div>
        <div id="div_tempo" class="st-chat-bko-timers">
            <div id="div_te" class="st-chat-timer"><i class="fas fa-history" aria-hidden="true"></i> TE: <?= htmlspecialchars($infFila['te']) ?></div>
            <div id="div_ta" class="st-chat-timer st-chat-timer--ta"></div>
        </div>
    </header>

    <div class="st-chat-bko-body">
        <div id="div_ope" class="st-chat-main">
            <?php
                    $userDestinatario = $infFila['ate_resp'];
                    //echo "<br>".$userDestinatario;

                    $sql="SELECT id_chat, token_chat, status_chat, fila_chat_id from tbl_chat_info where status_chat=1 and contrato_id=? and rem_chat=? and dest_chat=?";

                    //echo "<br>".$sql;

                    $stmt = $PDO->prepare($sql);
                    $result = $stmt->execute([$contratoId, (int) $infoUser['id_user'], (int) $userDestinatario]);
                    $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
                    //depurador($infoChat);
                    if($infoChat!=''){
                        $tokenChat = $infoChat['token_chat'];
                    } else {
                        $stringToken = $userDestinatario . date('YmdHis');
                        $tokenChat = md5($stringToken);
                        if($infoUser['nivel_id']==4){
                            $sql = "INSERT INTO tbl_chat_info (contrato_id, token_chat, assunto_id, fila_id, rem_chat, dest_chat, status_chat, fila_chat_id) VALUES (?, ?, ?, ?, ?, ?, 1, ?)";

                            //echo "<br>".$sql;
                            $stmt = $PDO->prepare( $sql );
                            $result = $stmt->execute([
                                (int) $infFila['contrato_id'],
                                $tokenChat,
                                (int) $infFila['assunto_id'],
                                (int) $infFila['fila_id'],
                                (int) $infoUser['id_user'],
                                (int) $userDestinatario,
                                (int) $infFila['id_fila_chat'],
                            ]);
                        }
                    }

                        if($infoChat['id_chat']==''){
                            $sql="SELECT id_chat, token_chat, status_chat, fila_chat_id from tbl_chat_info where token_chat=?";
                            $stmt = $PDO->prepare( $sql );
                            $result = $stmt->execute([$tokenChat]);
                            $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
                        }


                        $sql="SELECT id from tbl_tma_atend where fila_chat_id is null and resp_id=?";
                        //echo "<br>".$sql;
                        $stmt = $PDO->prepare( $sql );
                        $result = $stmt->execute([(int) $infoUser['id_user']]);
                        $infoAtend = $stmt->fetch( PDO::FETCH_ASSOC );

                        if($infoAtend['id']!=''){
                            $sql="UPDATE tbl_tma_atend SET fila_chat_id=?, chat_id=?, fila_id=?, date_in=now() where id=?";
                            //echo "<br>".$sql;
                            $stmt = $PDO->prepare( $sql );
                            $result = $stmt->execute([
                                (int) $infFila['id_fila_chat'],
                                (int) $infoChat['id_chat'],
                                (int) $infFila['fila_id'],
                                (int) $infoAtend['id'],
                            ]);
                        }


                    $chatId = $infoChat['id_chat'];

                    include("chat/chat_ind.php");
                ?>




        </div>

        <aside id="div_info" class="st-chat-side">
            <ul class="nav nav-tabs st-chat-tabs" id="tabChat" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="proc-tab" data-bs-toggle="tab" data-bs-target="#proc" type="button" role="tab" aria-controls="proc" aria-selected="true"><i class="fas fa-bars"></i> Procedimento</button>
                </li>
                <?php if($infoUser['env_file']==1){ ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#file" type="button" role="tab" aria-controls="file" aria-selected="false"><i class="fas fa-folder-open"></i> Depósito de arquivos</button>
                    </li>
                <?php } ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fila_atual-tab" data-bs-toggle="tab" data-bs-target="#fila_atual" type="button" role="tab" aria-controls="fila_atual" aria-selected="false"><i class="fas fa-list-ol"></i> Fila</button>
                </li>
            </ul>

            <div class="tab-content st-chat-side__content" id="myTabContent">
                <div class="tab-pane fade show active st-chat-pane" id="proc" role="tabpanel" aria-labelledby="proc-tab">
                    <?php
                        $sql="SELECT titulo_assunto, procedimento, date_format(data_alt, '%d/%m/%Y %H:%i:%s') as data_alt, date_format(data_alt, '%Y-%m-%d') as data_ver from tbl_assunto where id_assunto=?";
                        $stmt = $PDO->prepare($sql);
                        $stmt->execute([(int) $infFila['assunto_id']]);
                        $infoAssunto = $stmt->fetch(PDO::FETCH_ASSOC);
                        $data_ver = date('Y-m-d', strtotime('+5 days', strtotime($infoAssunto['data_ver'])));
                        $badge = (date('Y-m-d') > $data_ver) ? 'secondary' : 'danger';
                    ?>
                    <h4 class="st-chat-pane__title"><i class="fas fa-book-open" aria-hidden="true"></i> <?= htmlspecialchars($infoAssunto['titulo_assunto']) ?></h4>
                    <p class="st-chat-pane__meta">
                        <strong>Procedimento</strong>
                        <span class="badge bg-<?= $badge ?>">Atualizado: <?= htmlspecialchars($infoAssunto['data_alt']) ?></span>
                    </p>
                    <div id="proced" class="st-chat-pane__body"><?= $infoAssunto['procedimento'] ?></div>
                    <button type="button" class="btn btn-primary st-chat-call-btn" id="btn_call_ate"><i class="fas fa-bell" aria-hidden="true"></i> Chamar solicitante</button>
                </div>
                <?php if($infoUser['env_file']==1){ ?>
                    <div class="tab-pane fade st-chat-pane" id="file" role="tabpanel" aria-labelledby="file-tab">
                        <div id="files_deposit"></div>
                    </div>
                <?php } ?>

                <div class="tab-pane fade st-chat-pane" id="fila_atual" role="tabpanel" aria-labelledby="fila_atual-tab">
                    <div id="fila_ativa"></div>
                </div>
            </div>
        </aside>
    </div>
</div>

    <script>
        var time = setInterval(function(){ loadTa('<?= $infFila['hora_inicio']; ?>'); }, 1000);

        loadTa('<?= $infFila['hora_inicio']; ?>');
        load();
        //setInterval(function(){ loadFilaAtiva('<?= $infoUser['fila_id']; ?>'); }, 1000);
        function load(){
            loadFilaAtiva('<?= $infoUser['fila_id']; ?>');
        }


        $('#btn_call_ate').click(function (){

            sendBko();
        });

        function loadTa(tempo){
            var div = '#div_ta';
            $.post("staff/load_ta.php",
            {
                tempo
            },
            function (valor) {
                $(div).html(valor);
            });
        }

        function loadFilaAtiva(fila){
            var div = '#fila_ativa';
            $.post("staff/load_fila_ativa.php",
            {
                fila
            },
            function (valor) {
                $(div).html(valor);
            });
        }
    </script>

<?php } ?>
