<?php
include("../cnf/session.php");

$userId = (int) ($_POST['id'] ?? 0);
$formId = (int) ($_POST['form'] ?? 0);

if ($userId < 1) {
    return;
}

$sql = "UPDATE tbl_user SET form_id=? where id_user=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$formId, $userId]);

if ($result == 1) {
    ?>
<script>
    Swal.fire(
                'Sua Demanda foi alterada!',
                '',
                'success'
            );
</script>
<?php
}

?>

