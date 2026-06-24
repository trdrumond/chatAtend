<?php
include(__DIR__ . '/../cnf/session.php');

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: text/html; charset=utf-8');

$idFilaChat = isset($_POST['id_fila_chat']) ? (int)$_POST['id_fila_chat'] : 0;
$userId = (int)$infoUser['id_user'];

if ($idFilaChat <= 0) {
    exit;
}

$stmt = $PDO->prepare(
    'SELECT id_fila_chat, protocolo, fila_id, assunto_id, contrato_id, bko_resp, motivo'
    .' FROM tbl_chat_fila WHERE id_fila_chat=? AND ate_resp=? AND '.stFilaSqlChamarSolicitante().' LIMIT 1'
);
$stmt->execute([$idFilaChat, $userId]);
$infFila = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($infFila['id_fila_chat'])) {
    exit;
}

$stmt = $PDO->prepare(
    'SELECT id_faq, titulo_faq, txt FROM tbl_faq'
    .' WHERE fila_id=? AND (assunto_id=0 OR assunto_id=?)'
);
$stmt->execute([(int)$infFila['fila_id'], (int)$infFila['assunto_id']]);
$infoFaq = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $PDO->prepare(
    'SELECT titulo_assunto, procedimento,'
    .' DATE_FORMAT(data_alt, \'%d/%m/%Y %H:%i:%s\') AS data_alt,'
    .' DATE_FORMAT(data_alt, \'%Y-%m-%d\') AS data_ver'
    .' FROM tbl_assunto WHERE id_assunto=? LIMIT 1'
);
$stmt->execute([(int)$infFila['assunto_id']]);
$infoAssunto = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['procedimento' => '', 'titulo_assunto' => '', 'data_alt' => '', 'data_ver' => ''];

$stmt = $PDO->prepare(
    'SELECT id_chat FROM tbl_chat_info WHERE status_chat=1 AND fila_chat_id=? LIMIT 1'
);
$stmt->execute([$idFilaChat]);
$chatRow = $stmt->fetch(PDO::FETCH_ASSOC);
$chatId = !empty($chatRow['id_chat']) ? (int)$chatRow['id_chat'] : 0;

$activeTabFaq = '';
$activeDivFaq = '';
if (($infoAssunto['procedimento'] ?? '') === '' && count($infoFaq) > 0) {
    $activeTabFaq = ' active';
    $activeDivFaq = ' show active';
}

$activeTabFile = '';
$activeDivFile = '';
if (($infoAssunto['procedimento'] ?? '') === '' && count($infoFaq) < 1 && (int)$infoUser['env_file'] === 1) {
    $activeTabFile = ' active';
    $activeDivFile = ' show active';
}

$hasProc = ($infoAssunto['procedimento'] ?? '') !== '';
$hasFaq = count($infoFaq) > 0;
$hasFile = (int)$infoUser['env_file'] === 1;

if (!$hasProc && !$hasFaq && !$hasFile) {
    exit;
}
?>
<ul class="nav nav-tabs st-chat-tabs" id="tabChat" role="tablist">
    <?php if ($hasProc) { ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="proc-tab" data-bs-toggle="tab" data-bs-target="#proc" type="button" role="tab" aria-controls="proc" aria-selected="true"><i class="fas fa-bars"></i> Procedimento</button>
    </li>
    <?php } ?>
    <?php if ($hasFaq) { ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTabFaq ?>" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button" role="tab" aria-controls="faq" aria-selected="false"><i class="fas fa-question-circle"></i> FAQ</button>
    </li>
    <?php } ?>
    <?php if ($hasFile) { ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTabFile ?>" id="file-tab" data-bs-toggle="tab" data-bs-target="#file" type="button" role="tab" aria-controls="file" aria-selected="false"><i class="fas fa-folder-open"></i> Arquivos</button>
    </li>
    <?php } ?>
</ul>
<div class="tab-content st-chat-side__content" id="myTabContent">
    <?php if ($hasProc) {
        $data_ver = date('Y-m-d', strtotime('+5 days', strtotime($infoAssunto['data_ver'])));
        $badge = (date('Y-m-d') > $data_ver) ? 'secondary' : 'danger';
    ?>
    <div class="tab-pane fade show active st-chat-pane" id="proc" role="tabpanel" aria-labelledby="proc-tab">
        <h4 class="st-chat-pane__title"><i class="fas fa-book-open" aria-hidden="true"></i> <?= htmlspecialchars($infoAssunto['titulo_assunto']) ?></h4>
        <p class="st-chat-pane__meta">
            <strong>Procedimento</strong>
            <span class="badge bg-<?= $badge ?>">Atualizado: <?= htmlspecialchars($infoAssunto['data_alt']) ?></span>
        </p>
        <div id="proced" class="st-chat-pane__body"><?= $infoAssunto['procedimento'] ?></div>
    </div>
    <?php } ?>
    <?php if ($hasFaq) { ?>
    <div class="tab-pane fade <?= $activeDivFaq ?> st-chat-pane" id="faq" role="tabpanel" aria-labelledby="faq-tab">
        <div class="accordion accordion-flush st-chat-faq" id="faq_accordion">
            <?php for ($x = 0; $x < count($infoFaq); $x++) { ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingOne-<?= $infoFaq[$x]['id_faq'] ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-<?= $infoFaq[$x]['id_faq'] ?>" aria-expanded="false" aria-controls="flush-<?= $infoFaq[$x]['id_faq'] ?>">
                        <?= htmlspecialchars($infoFaq[$x]['titulo_faq']) ?>
                    </button>
                </h2>
                <div id="flush-<?= $infoFaq[$x]['id_faq'] ?>" class="accordion-collapse collapse" aria-labelledby="flush-headingOne-<?= $infoFaq[$x]['id_faq'] ?>" data-bs-parent="#faq_accordion">
                    <div class="accordion-body"><?= $infoFaq[$x]['txt'] ?></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
    <?php if ($hasFile) { ?>
    <div class="tab-pane fade <?= $activeDivFile ?> st-chat-pane" id="file" role="tabpanel" aria-labelledby="file-tab">
        <div id="files_deposit_<?= $chatId ?>"></div>
    </div>
    <?php } ?>
</div>
