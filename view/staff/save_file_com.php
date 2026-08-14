<?php
include("../cnf/session.php");

$comId = (int) ($_POST['com'] ?? 0);

$sql = "SELECT count(id_file) as qtd FROM tbl_com_files where com_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$comId]);
$infoQtd = $stmt->fetch(PDO::FETCH_ASSOC);

$name_file = str_pad(($infoQtd['qtd'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);

$sql = "INSERT INTO tbl_com_files (link_file, name_file, rem, com_id) VALUES (?, ?, ?, ?)";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([
    (string) ($_POST['file'] ?? ''),
    $name_file,
    (string) ($_POST['rem'] ?? ''),
    $comId,
]);

if ($result == 1) {
?>
<script>
    name_file = <?= json_encode($name_file, JSON_UNESCAPED_UNICODE) ?>;
    link = <?= json_encode((string) ($_POST['file'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php } ?>
