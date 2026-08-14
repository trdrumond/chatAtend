<?php
include("../cnf/session.php");

$idFila = (int) ($_POST['id_fila'] ?? 0);

$stmt = $PDO->prepare("SELECT id_chat, rem_chat, dest_chat, indice, contrato_id, token_chat from tbl_chat_info where fila_chat_id=?");
$stmt->execute([$idFila]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (is_array($info) && ($info['id_chat'] ?? '') != '') {
    $chatId = (int) $info['id_chat'];
    $remChat = htmlspecialchars((string) $info['rem_chat'], ENT_QUOTES, 'UTF-8');
    $destChat = htmlspecialchars((string) $info['dest_chat'], ENT_QUOTES, 'UTF-8');
    $tokenChat = htmlspecialchars((string) $info['token_chat'], ENT_QUOTES, 'UTF-8');
    $contratoId = (int) $info['contrato_id'];
    $indice = (int) $info['indice'];
    ?>
    <input type="hidden" id="id_user_remetente_<?= $chatId ?>" name="id_user_remetente_<?= $chatId ?>" value="<?= $remChat ?>">
    <input type="hidden" id="id_user_destinatario_<?= $chatId ?>" name="id_user_destinatario_<?= $chatId ?>" value="<?= $destChat ?>">
    <script>
        var remetente = <?= json_encode((string) $info['rem_chat'], JSON_UNESCAPED_UNICODE) ?>;
        var destinatario = <?= json_encode((string) $info['dest_chat'], JSON_UNESCAPED_UNICODE) ?>;
        var mensagem = 'Atendimento encerrado pelo Gestor';
        var chatId = <?= json_encode((string) $chatId) ?>;
        var indice = $('#indice_'+<?= $indice ?>).val();
        chatFim(chatId, destinatario, <?= $contratoId ?>, <?= json_encode((string) $info['token_chat']) ?>, mensagem, indice);
    </script>
    <?php
    $stmt = $PDO->prepare("UPDATE tbl_chat_info SET status_chat=9 where fila_chat_id=?");
    $stmt->execute([$idFila]);
}

$stmt = $PDO->prepare("UPDATE tbl_chat_fila SET status_fila=9 where id_fila_chat=?");
$result = $stmt->execute([$idFila]);

$stmt = $PDO->prepare("DELETE FROM tbl_tma_atend where fila_chat_id=?");
$stmt->execute([$idFila]);

if ($result) {
    echo '<i class="fas fa-check-circle fa-2x" style="color: green"></i>';
}
