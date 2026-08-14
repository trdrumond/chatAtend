<?php
include("../api/conn.php");
//include("../api/config.php");

/** @var int|string $idu Definido em cnf/session.php antes do include */
/** @var PDO $PDO */
if (!isset($idu)) {
    $idu = (int)($_SESSION['dados']['id_user'] ?? 0);
}

$sql="SELECT user_id, date_refresh, datediff(now(), date_refresh) as dias_refresh, pass, (SELECT dias_refresh from tbl_user_pass_config) as dias_config from tbl_user_pass where user_id=? order by date_refresh desc limit 1";
//echo "<br>".$sql;

$stmt = $PDO->prepare($sql);
//echo "<br>Teste";
$result = $stmt->execute([(int) $idu]);
$info = $stmt->fetch( PDO::FETCH_ASSOC );

if (is_array($info) && isset($info['dias_refresh'], $info['dias_config']) && (int)$info['dias_refresh'] > (int)$info['dias_config']) {
    echo "<meta http-equiv=refresh content='0; URL=index.php?sec=usu&op=2';>";
}





?>
