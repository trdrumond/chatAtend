<?php
include("../cnf/session.php");

$tokenChat = (string) ($_POST['tokenChat'] ?? '');

$sql = "SELECT count(id_file) as qtd FROM tbl_com_files where token_chat=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$tokenChat]);
$infoQtd = $stmt->fetch(PDO::FETCH_ASSOC);

$name_file = str_pad(($infoQtd['qtd'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);

$sql = "INSERT INTO tbl_com_files (link_file, name_file, resp) VALUES (?, ?, ?)";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([
    (string) ($_POST['file'] ?? ''),
    $name_file,
    (string) ($_POST['rem'] ?? ''),
]);

if ($result == 1) {
?>
<script>
    sendFile(<?= (int) ($_POST['chatId'] ?? 0) ?>);
    name_file = <?= json_encode($name_file, JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php } ?>
