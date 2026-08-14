<?php
include("../cnf/session.php");

$filaId = (int) ($_POST['fila'] ?? 0);
$stm = $PDO->prepare("SELECT assuntos_id, contrato_id from tbl_config_fila where id_fila=?");
$stm->execute([$filaId]);
$ass = $stm->fetch(PDO::FETCH_ASSOC);
if (!is_array($ass) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($ass['contrato_id'] ?? 0))) {
    echo '<option value="">Assuntos</option>';
    return;
}

$assuntosRaw = array_values(array_filter(array_map('intval', explode(',', (string) ($ass['assuntos_id'] ?? '')))));
if (count($assuntosRaw) === 0) {
    echo '<option value="">Assuntos</option>';
    return;
}

$placeholders = implode(',', array_fill(0, count($assuntosRaw), '?'));
$sql = "SELECT id_assunto, titulo_assunto from tbl_assunto where ativo=1 and id_assunto IN (".$placeholders.") order by titulo_assunto asc";

$stmt = $PDO->prepare($sql);
$result = $stmt->execute($assuntosRaw);
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($dados) > 0) {
    echo '<option value="">Todos</option>';
    for ($x = 0; $x < count($dados); $x++) {
        echo '<option value="' . htmlspecialchars((string) $dados[$x]['id_assunto'], ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars((string) $dados[$x]['titulo_assunto'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
} else {
    echo '<option value="">Assuntos</option>';
}
?>
