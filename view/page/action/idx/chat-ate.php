
<?php
    include("cnf/session.php");

    /** @var array<string, mixed> $infoUser */
    /** @var PDO $PDO */

    $userId = (int)$_SESSION['dados']['id_user'];
    $idFilaChatReq = isset($_POST['id_fila_chat']) ? (int)$_POST['id_fila_chat'] : 0;

    $ctx = stChatAteSolBootstrap($PDO, $userId, $idFilaChatReq, (int)($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0));
    $infFila = $ctx['infFila'];
    $state = $ctx['state'];

    if ($state === 'wait_fila') {
        if (function_exists('st_chat_open_loader_html')) {
            echo st_chat_open_loader_html('Abrindo chat', 'Aguardando o atendente...');
        } else {
            echo '<div class="st-chat-open" role="status"><div class="st-chat-open__panel">'
                .'<div class="st-chat-open__spinner" aria-hidden="true"></div>'
                .'<p class="st-chat-open__title">Abrindo chat</p>'
                .'<p class="st-chat-open__sub">Aguardando o atendente...</p>'
                .'</div></div>';
        }
        echo '<script>window.stChatSolOpeningAte=false;window.redirecionandoAtendimento=true;window.isActionLoading=false;</script>';
        exit;
    }

    if ($state === 'closed') {
        echo '<script>window.stChatSolOpeningAte=false;window.redirecionandoAtendimento=false;window.isActionLoading=false;'
            .'if(typeof window.actionPageNav==="function"){window.actionPageNav("dash-cha","idx");}'
            .'else if(typeof actionPage==="function"){actionPage("dash-cha","idx");}</script>';
        exit;
    }

    if ($state === 'wait_bko') {
        if (function_exists('st_chat_open_loader_html')) {
            echo st_chat_open_loader_html('Abrindo chat', 'Conectando você ao atendente...');
        } else {
            echo '<div class="st-chat-open" role="status"><div class="st-chat-open__panel">'
                .'<div class="st-chat-open__spinner" aria-hidden="true"></div>'
                .'<p class="st-chat-open__title">Abrindo chat</p>'
                .'<p class="st-chat-open__sub">Conectando você ao atendente...</p>'
                .'</div></div>';
        }
        echo '<script>window.stChatSolOpeningAte=false;window.redirecionandoAtendimento=true;window.isActionLoading=false;</script>';
        exit;
    }

    $infoChat = $ctx['infoChat'];
    $chatId = $ctx['chatId'];
    $bkoResp = $ctx['bkoResp'];
    $userDestinatario = $bkoResp;
    $idFilaChat = (int)$infFila['id_fila_chat'];
    $tokenChat = $infoChat['token_chat'] ?? md5($userDestinatario . date('YmdHis'));

    if ($state === 'wait_chat' || $chatId <= 0) {
        if (function_exists('st_chat_open_loader_html')) {
            echo st_chat_open_loader_html('Abrindo chat', 'Preparando sua conversa...');
        } else {
            echo '<div class="st-chat-open" role="status"><div class="st-chat-open__panel">'
                .'<div class="st-chat-open__spinner" aria-hidden="true"></div>'
                .'<p class="st-chat-open__title">Abrindo chat</p>'
                .'<p class="st-chat-open__sub">Preparando sua conversa...</p>'
                .'</div></div>';
        }
        echo '<script>window.stChatSolOpeningAte=false;window.redirecionandoAtendimento=true;window.isActionLoading=false;</script>';
        exit;
    }

    $stmt = $PDO->prepare(
        'SELECT u.id_user, CONCAT(u.nome, \' \', u.sobrenome) AS nome_completo, u.email, u.fila_id,'
        .' cf.nome_fila, img.img'
        .' FROM tbl_user u'
        .' LEFT JOIN tbl_config_fila cf ON cf.id_fila = u.fila_id'
        .' LEFT JOIN tbl_user_img_perfil img ON img.user_id = u.id_user'
        .' WHERE u.id_user=? LIMIT 1'
    );
    $stmt->execute([$userDestinatario]);
    $dados_ate = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $imgBko = !empty($dados_ate['img']) ? $dados_ate['img'] : 'img/perfil.fw.png';
    $motivoChat = ['motivo' => $infFila['motivo'] ?? ''];
    $stChatSkipFilasCount = true;

?>

<script>
    userRem = <?=$infoUser['id_user']?>;
    var indice = <?=(int)($infoChat['indice'] ?? 0)?>;
    window.stFilaSolIdFila = <?= $idFilaChat ?>;
    $('#dash-cha').hide();
    $('#hist-dash').hide();
    $('#hist-pend').hide();
    $('#com-idx').hide();
    $('#sair').hide();
</script>

<div id="dashboard" class="st-chat-workspace st-chat-workspace--sol">
    <header class="st-chat-header">
        <div class="st-chat-header__icon" aria-hidden="true"><i class="fas fa-comments"></i></div>
        <div class="st-chat-header__info">
            <span class="st-chat-header__label">Atendimento em andamento</span>
            <strong class="st-chat-header__protocol">Protocolo <?= htmlspecialchars($infFila['protocolo']) ?></strong>
        </div>
    </header>

    <div class="st-chat-bko-body">
        <div id="div_ope" class="st-chat-main">
            <?php include("chat/chat_ind.php"); ?>
        </div>

        <aside id="div_info" class="st-chat-side">
            <div class="st-chat-agent">
                <img src="<?= htmlspecialchars($imgBko) ?>" alt="Foto do atendente" class="st-chat-agent__avatar rounded-circle">
                <div class="st-chat-agent__body">
                    <span class="st-chat-agent__role">Seu atendente</span>
                    <strong class="st-chat-agent__name"><?= htmlspecialchars($dados_ate['nome_completo'] ?? 'Atendente') ?></strong>
                    <?php if (!empty($dados_ate['email'])) { ?>
                    <a class="st-chat-agent__mail" href="mailto:<?= htmlspecialchars($dados_ate['email']) ?>?subject=Solvetask <?= htmlspecialchars($infFila['protocolo']) ?>">
                        <i class="far fa-envelope" aria-hidden="true"></i> <?= htmlspecialchars($dados_ate['email']) ?>
                    </a>
                    <?php } ?>
                    <?php if (!empty($dados_ate['nome_fila'])) { ?>
                    <span class="st-chat-agent__meta"><i class="far fa-building" aria-hidden="true"></i> <?= htmlspecialchars($dados_ate['nome_fila']) ?></span>
                    <?php } ?>
                </div>
            </div>
            <div id="st_chat_side_panel" class="st-chat-side__lazy" aria-busy="true">
                <p class="st-chat-side__lazy-hint text-muted small mb-0">Carregando procedimentos e FAQ...</p>
            </div>
        </aside>
    </div>
</div>

<script>
    (function () {
        var idFila = <?= $idFilaChat ?>;
        $.post('staff/load_chat_ate_painel.php', { id_fila_chat: idFila }, function (html) {
            var $panel = $('#st_chat_side_panel');
            if (!html || !String(html).trim()) {
                $panel.remove();
                return;
            }
            $panel.replaceWith(html);
            if (typeof loadFileDiv === 'function' && <?= (int)$chatId ?>) {
                loadFileDiv(<?= (int)$chatId ?>);
            }
        });
    })();
    if (typeof loadChatIn !== 'undefined') { loadChatIn = ''; }
    window.stChatSolRedirectPending = false;
    window.stChatSolOpeningAte = false;
    window.stChatSolEnterLock = false;
    window.stChatSolEnded = false;
    window.stChatSolClassModalOpen = false;
    window.redirecionandoAtendimento = true;
    window.loadAtendIniciado = true;
    window.load = function () {};
    window.stFilaSolPollAtendimentoAgora = function () {};
    if (window.timeLoadQtd) { clearTimeout(window.timeLoadQtd); window.timeLoadQtd = null; }
    if (window.loadAtendTimer) { clearTimeout(window.loadAtendTimer); window.loadAtendTimer = null; }
    if (typeof stFilaSolPararNotificarBko === 'function') { stFilaSolPararNotificarBko(); }
    if (typeof stFilaSolStopAllPolling === 'function') { stFilaSolStopAllPolling(); }
</script>
