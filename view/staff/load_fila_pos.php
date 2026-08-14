<?php
include("../cnf/session.php");

$chatId = isset($_POST['chatId']) ? (int)$_POST['chatId'] : 0;
$pausaField = $chatId > 0 ? '#pausa_bko_' . $chatId : '#pausa_bko';

$sql = "SELECT count(id_fila_chat) AS qtd FROM tbl_chat_fila WHERE status_fila = 1 AND fila_id = ?";
$stm = $PDO->prepare($sql);
$stm->execute([(int) ($_POST['fila'] ?? 0)]);
$fila = $stm->fetch(PDO::FETCH_ASSOC);

if ((int)$fila['qtd'] >= 1) {
    ?>
<script>
    Swal.fire({
        title: 'Em fila: <?= (int)$fila['qtd'] ?>',
        text: 'Deseja realizar pausa agora?',
        showDenyButton: true,
        showCancelButton: false,
        confirmButtonText: 'Sim',
        denyButtonText: 'Não'
    }).then(function(result) {
        if (result.isConfirmed) {
            $('<?= $pausaField ?>').val(1);
        } else if (result.isDenied) {
            $('<?= $pausaField ?>').val(0);
        }
    });
</script>
<?php } ?>
