<?php
include("../cnf/session.php");

$msg = (string) ($_POST['msg'] ?? '');
$tokenChat = (string) ($_POST['tokenChat'] ?? '');

if ($msg !== '' && $tokenChat !== '') {
    $stmt = $PDO->prepare("SELECT id_chat, contrato_id from tbl_chat_info where status_chat=1 and token_chat=?");
    $stmt->execute([$tokenChat]);
    $infoChat = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($infoChat) || empty($infoChat['id_chat'])) {
        return;
    }
    $chatId = (int) $infoChat['id_chat'];
    $contratoId = (int) ($infoChat['contrato_id'] ?? 0);
    if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
        return;
    }

    if (strpos($msg, '<img') !== false) {
        $imgSrc = null;
        if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $msg, $imgMatch)) {
            $imgSrc = $imgMatch[2];
        }

        if ($imgSrc !== null && $imgSrc !== '') {
            $stmt = $PDO->prepare("SELECT count(chat_id) as qtd from tbl_img where chat_id=?");
            $stmt->execute([$chatId]);
            $infoImg = $stmt->fetch(PDO::FETCH_ASSOC);
            $key = ((int) ($infoImg['qtd'] ?? 0)) + 1;

            $msg .= '<p><a href=staff/img.php?id=' . $chatId . '&key=' . $key . ' target="_blank">Abrir imagem</a></p>';

            $stmt = $PDO->prepare(
                'INSERT INTO tbl_img (chat_id, token_chat, src, chave) VALUES (:chat_id, :token_chat, :src, :chave)'
            );
            $stmt->execute([
                ':chat_id' => $chatId,
                ':token_chat' => $tokenChat,
                ':src' => $imgSrc,
                ':chave' => (string) $key,
            ]);
        }
    }

    $contrato = $contratoId;
    $rem = (int) ($_POST['rem'] ?? 0);
    $dest = (int) ($_POST['dest'] ?? 0);
    $flag = $_POST['flag'] ?? '';

    if ($flag !== '') {
        $stmt = $PDO->prepare(
            "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg, flag) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$chatId, $contrato, $rem, $dest, $msg, (int) $flag]);
    } else {
        $stmt = $PDO->prepare(
            "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$chatId, $contrato, $rem, $dest, $msg]);
    }
}

?>
