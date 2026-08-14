<?php
include_once("conn.php");

$sql = "SELECT distinct(fila_id) as id_fila from tbl_fila_horario";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoFila = $stmt->fetchAll( PDO::FETCH_ASSOC );
if(count($infoFila)>0){
    for($x=0;$x<count($infoFila);$x++){
        $ls = $infoFila[$x];
        $filaIdHr = (int) ($ls['id_fila'] ?? 0);
        $sqlHorario = "SELECT id_hr, inicio_hr, fim_hr, ativo from tbl_fila_horario where ativo=1 and (inicio_hr < date_format(now(), '%H:%i:%s') and date_format(now(), '%H:%i:%s') < fim_hr ) and fila_id=?";
        //echo "<br>".$sqlHorario;
        $stmt = $PDO->prepare($sqlHorario);
        $result = $stmt->execute([$filaIdHr]);
        $infoHr = $stmt->fetch( PDO::FETCH_ASSOC );
        //depurador($infoHr);
        if($infoHr['id_hr']!=''){
            //echo "<br> - Dentro do Horario";
            $sql="UPDATE tbl_config_fila SET ativo=1 where id_fila=?";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute([$filaIdHr]);
        } else {
            //echo "<br> - Fora do Horario";
            $sql="UPDATE tbl_config_fila SET ativo=0 where id_fila=?";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute([$filaIdHr]);
        }
    }
}



?>
