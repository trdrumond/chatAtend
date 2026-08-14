<?php
include("../cnf/session.php");

if (($_POST['msg'] ?? '') != '') {
    $_POST['msg'] = str_replace(array("\n", "\r", "\r\n"), '', $_POST['msg']);
    $msg = (string) $_POST['msg'];
    $comId = (int) ($_POST['com'] ?? 0);
    if ($comId < 1) {
        return;
    }

    $sql = "SELECT id_com, contrato_id, rem_chat, dest_chat, grupo_com from tbl_com_info where id_com=?";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$comId]);
    $infoCom = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($infoCom) || empty($infoCom['id_com'])) {
        return;
    }

    $userId = (int) ($infoUser['id_user'] ?? 0);
    $remChat = (int) ($infoCom['rem_chat'] ?? -1);
    $destChat = (int) ($infoCom['dest_chat'] ?? -1);
    $isGroup = ($remChat === 0 && $destChat === 0);
    $destId = 0;

    if ($isGroup) {
        $sqlGroupConfig = "SELECT equipe_adm, equipe_bko, equipe_ate, cols from tbl_com_config where grupo_com_id=?";
        $stmt = $PDO->prepare($sqlGroupConfig);
        $stmt->execute([$comId]);
        $infoConfigGrupo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($infoConfigGrupo)) {
            return;
        }
        $membership = 0;
        if (($infoConfigGrupo['cols'] ?? '') == '') {
            $nivelId = (int) ($infoUser['nivel_id'] ?? 99);
            if ($nivelId <= 1) {
                $membership = (int) ($infoConfigGrupo['equipe_adm'] ?? 0);
            } elseif ($nivelId == 4) {
                $membership = (int) ($infoConfigGrupo['equipe_bko'] ?? 0);
            } elseif ($nivelId == 5) {
                $membership = (int) ($infoConfigGrupo['equipe_ate'] ?? 0);
            }
        } else {
            if (stComColsHasUser($infoConfigGrupo['cols'] ?? '', $userId)) {
                $membership = 1;
            }
        }
        if ($membership != 1) {
            return;
        }
    } else {
        if ($userId !== $remChat && $userId !== $destChat) {
            return;
        }
        $contrato = (int) ($infoCom['contrato_id'] ?? 0);
        if ($contrato < 1) {
            return;
        }
        if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contrato)) {
            return;
        }
        $destId = ($userId === $remChat) ? $destChat : $remChat;
    }

    if (strpos($msg, '<img') !== false) {
        $imgSrc = null;
        if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $msg, $imgMatch)) {
            $imgSrc = trim((string) $imgMatch[2]);
        }
        if ($imgSrc !== null && $imgSrc !== '' && stripos($imgSrc, 'javascript:') !== 0) {
            $sql = "SELECT count(com_id) as qtd from tbl_com_img where com_id=?";
            $stmt = $PDO->prepare($sql);
            $stmt->execute([$comId]);
            $infoImg = $stmt->fetch(PDO::FETCH_ASSOC);
            $key = ((int) ($infoImg['qtd'] ?? 0)) + 1;

            $msg .= '<p><a href=staff/img_group.php?id=' . $comId . '&key=' . $key . ' target="_blank">Abrir imagem</a></p>';

            $sql = "INSERT INTO tbl_com_img (com_id, src, chave) VALUES (?, ?, ?)";
            $stmt = $PDO->prepare($sql);
            $stmt->execute([$comId, $imgSrc, $key]);
        }
    }

    if ($isGroup) {
        $sql = "INSERT INTO tbl_com_msg_group (chat_group, rem_id, msg) VALUES (?, ?, ?)";
        $params = [$infoCom['id_com'] ?? $comId, $userId, $msg];
    } else {
        $sql = "INSERT INTO tbl_com_msg (com_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, ?, ?, ?)";
        $params = [$comId, (int) ($infoCom['contrato_id'] ?? 0), $userId, $destId, $msg];
    }

    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute($params);
    if ($result) {
        $sql = "UPDATE tbl_com_info SET dt_update=now() where id_com=?";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$comId]);
        if ($result) {
            echo "<script>sendMessageComInd("
                . json_encode((string) $msg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
                . ", " . json_encode((string) $userId, JSON_UNESCAPED_UNICODE)
                . ", " . json_encode((string) $destId, JSON_UNESCAPED_UNICODE)
                . ", " . json_encode((string) $comId, JSON_UNESCAPED_UNICODE)
                . ", " . json_encode((string) ($_POST['nome'] ?? ''), JSON_UNESCAPED_UNICODE)
                . ", " . json_encode((string) ($_POST['img'] ?? ''), JSON_UNESCAPED_UNICODE)
                . ", " . json_encode((string) ($_POST['tk'] ?? ''), JSON_UNESCAPED_UNICODE)
                . ");</script>";
        }
    }
}
?>
