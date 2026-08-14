<!--<script type="text/javascript" src='chat/assets/js/script_com_ind.js?<?= time() ?>' defer></script>-->
<?php
include("../cnf/session.php");

if ((int) ($infoUser['nivel_id'] ?? 99) >= 2 || (int) ($infoUser['men_massa'] ?? 0) !== 1) {
    return;
}

$contratoSessao = (int) ($infoUser['contrato_id'] ?? 0);

if (($_POST['msg'] ?? '') != '') {
    $_POST['msg'] = str_replace(array("\n", "\r", "\r\n"), '', $_POST['msg']);
    $colPost = $_POST['col'] ?? [];
    if (!is_array($colPost)) {
        $colPost = [$colPost];
    }
    $userId = (int) ($infoUser['id_user'] ?? 0);
    $stmtDest = $PDO->prepare("SELECT id_user, contrato_id FROM tbl_user WHERE id_user=?");
    for ($x = 0; $x < count($colPost); $x++) {
        $destId = (int) $colPost[$x];
        if ($destId < 1 || $destId === $userId) {
            continue;
        }
        $stmtDest->execute([$destId]);
        $destUser = $stmtDest->fetch(PDO::FETCH_ASSOC);
        if (!is_array($destUser) || empty($destUser['id_user'])) {
            continue;
        }
        $contratoDest = (int) ($destUser['contrato_id'] ?? 0);
        if ($contratoDest < 1) {
            continue;
        }
        if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoDest)) {
            continue;
        }

        $tk = strtotime(date('Y-m-d H:i:s')) . $destId;
        $id_com = '';
        $contratoCom = 0;
        $sql_1 = "SELECT id_com, contrato_id from tbl_com_info where rem_chat=? and dest_chat=?";
        $stmt = $PDO->prepare($sql_1);
        $result = $stmt->execute([$userId, $destId]);
        $info_1 = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql_2 = "SELECT id_com, contrato_id from tbl_com_info where dest_chat=? and rem_chat=?";
        $stmt = $PDO->prepare($sql_2);
        $result = $stmt->execute([$userId, $destId]);
        $info_2 = $stmt->fetch(PDO::FETCH_ASSOC);

        if (($info_1['id_com'] ?? '') != '') {
            $id_com = $info_1['id_com'];
            $contratoCom = (int) ($info_1['contrato_id'] ?? 0);
        }
        if (($info_2['id_com'] ?? '') != '') {
            $id_com = $info_2['id_com'];
            $contratoCom = (int) ($info_2['contrato_id'] ?? 0);
        }
        if ($id_com != '') {
            if ($contratoCom < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoCom)) {
                continue;
            }
        }
        if ($id_com == '') {
            if ($contratoSessao < 1 || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoSessao)) {
                continue;
            }
            $sql = "INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES (?, ?, ?, ?, ?)";
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute([
                $contratoSessao,
                $userId,
                $destId,
                '',
                '',
            ]);
            if ($result == 1) {
                $sql_3 = "SELECT id_com from tbl_com_info where rem_chat=? and dest_chat=?";
                $stmt = $PDO->prepare($sql_3);
                $result = $stmt->execute([$userId, $destId]);
                $info_3 = $stmt->fetch(PDO::FETCH_ASSOC);
                if (($info_3['id_com'] ?? '') != '') {
                    $id_com = $info_3['id_com'];
                }
            }
        }

        if ($id_com != '') {
            ?>
                <div id="feed_massa_<?= (int) $id_com ?>"></div>
                <script>
                    chat_com_<?= (int) $id_com ?>();

                    function chat_com_<?= (int) $id_com ?>(){

                        var msg = <?= json_encode((string) $_POST['msg'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
                        var rem = <?= json_encode((string) $userId, JSON_UNESCAPED_UNICODE) ?>;
                        var dest = <?= json_encode((string) $destId, JSON_UNESCAPED_UNICODE) ?>;
                        var com = <?= json_encode((string) $id_com, JSON_UNESCAPED_UNICODE) ?>;
                        var nome = <?= json_encode((string) ($_POST['nome'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
                        var img = <?= json_encode((string) ($_POST['img'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
                        var tk = <?= json_encode((string) $tk, JSON_UNESCAPED_UNICODE) ?>;

                        saveMsgComMassa(msg, rem, dest, com, nome, img, tk);

                    }


                </script>
            <?php
        }
    }
}
?>
