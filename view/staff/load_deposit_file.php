<?php
include("../cnf/session.php");

$chatId = (int) ($_POST['chatId'] ?? 0);

if ($chatId < 1) {
    return;
}

$sql = "SELECT a.id_file, a.token_chat, a.link_file, a.name_file, a.resp, a.data_hora, b.id_chat from tbl_chat_files a, tbl_chat_info b where a.token_chat=b.token_chat and b.id_chat=? order by id_file asc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$chatId]);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($dados) > 0) {
    for ($x = 0; $x < count($dados); $x++) {
        if ($dados[$x]['link_file'] != '') {
            $href = (string) $dados[$x]['link_file'];
            if (!preg_match('#^file/[A-Za-z0-9._\-]+$#', $href)) {
                continue;
            }
            echo '<div class="file_chat"><a href="' . stHtml($href) . '" target="_blank" rel="noopener"><i class="fas fa-file-alt fa-2x"></i><br>' . stHtml($dados[$x]['name_file']) . '</a></div>';
        }
    }
} else {
    echo '<br><br><center><h6>Os arquivos ficam disponíveis no sistema por 90 dias.</h6></center>';
}
?>

