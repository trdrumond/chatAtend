<?php
include("../api/conn.php");



$sql="SELECT user_id, date_refresh, datediff(now(), date_refresh) as dias_refresh, pass, (SELECT dias_refresh from tbl_user_pass_config) as dias_config from tbl_user_pass where user_id=$idu order by date_refresh desc limit 1";
echo "<br>".$sql;

//$stmt = $PDO->prepare($sql);
//echo "<br>Teste";
//$result = $stmt->execute();
//$info = $stmt->fetch( PDO::FETCH_ASSOC );
//var_dump($info);




?>
