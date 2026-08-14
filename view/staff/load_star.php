<?php
include("../cnf/session.php");

//depurador($_POST);
//depurador($infoUser);
$day = (date('Y-m-d')<'2021-12-06') ? 1 : 5;
$idUser = (int) $infoUser['id_user'];

$sql = "SELECT format(avg(star), 1) as star from tbl_classificacao where ate=? and star is not null and star<>''  and date_format(data_hora, '%Y-%m-%d') BETWEEN '0001-01-01' and date_sub(CURDATE(), INTERVAL $day DAY)";
//echo "<br>".$sql;

$stmt = $PDO->prepare($sql);
$result = $stmt->execute([$idUser]);
$star = $stmt->fetch( PDO::FETCH_ASSOC );

$star['star'] = (date('Y-m-d')<'2021-12-11' && $star['star'] < '2.5') ? ' -.- ' : $star['star'];
$star['star'] = ($star['star']=='') ? ' -.- ' : $star['star'];

echo '<i class="fas fa-star" style="color: #D2D200"></i> '.$star['star'];
?>
