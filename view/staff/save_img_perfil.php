<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

$userId = (int) ($_POST['id'] ?? 0);
$imgText = (string) ($_POST['imgText'] ?? '');

if ($userId <= 0) {
    exit;
}

$stmt = $PDO->prepare("SELECT img from tbl_user_img_perfil where user_id=?");
$stmt->execute([$userId]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (is_array($info) && ($info['img'] ?? '') !== '') {
    $stmt = $PDO->prepare("UPDATE tbl_user_img_perfil SET img=? where user_id=?");
    $result = $stmt->execute([$imgText, $userId]);
} else {
    $stmt = $PDO->prepare("INSERT INTO tbl_user_img_perfil (user_id, img) values (?, ?)");
    $result = $stmt->execute([$userId, $imgText]);
}

if ($result == 1) {
    clearUserLayoutCache($userId);
    $imgJs = json_encode($imgText, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script>
    
        Swal.fire(
                'Imagem alterada!',
                'Sua imagem foi carregada corretamente!',
                'success'
            );
    $("#user_img_perfil").html('<img src=' + <?= $imgJs ?> + ' class="rounded-circle" alt="perfil" height="80" width="80" style="margin: auto;">');
    
    


</script>
<?php
    }

    ?>
