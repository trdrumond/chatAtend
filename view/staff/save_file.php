<?php
include("../cnf/session.php");

$tokenChat = (string) ($_POST['tokenChat'] ?? '');
$file = (string) ($_POST['file'] ?? '');
$rem = (string) ($_POST['rem'] ?? '');
$chatId = (int) ($_POST['chatId'] ?? 0);

$stmt = $PDO->prepare("SELECT count(id_file) as qtd FROM tbl_chat_files where token_chat=?");
$stmt->execute([$tokenChat]);
$infoQtd = $stmt->fetch(PDO::FETCH_ASSOC);

$name_file = str_pad(((int) ($infoQtd['qtd'] ?? 0)) + 1, 3, '0', STR_PAD_LEFT);

$stmt = $PDO->prepare("INSERT INTO tbl_chat_files (token_chat, link_file, name_file, resp) VALUES (?, ?, ?, ?)");
$result = $stmt->execute([$tokenChat, $file, $name_file, $rem]);

if ($result == 1) {
?>
<script>
    sendFile(<?= $chatId ?>);
    name_file = <?= json_encode($name_file, JSON_UNESCAPED_UNICODE) ?>;
    //console.log(name_file);

</script>

<?php } ?>
