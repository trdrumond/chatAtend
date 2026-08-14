<?php
include_once("conn.php");

if(date('H:i:s') < '08:00:00'){  

    $sql = "SELECT id, resp_id, date_in, date_out, date_format(date_disp, '%Y-%m-%d') as date_disp, chat_id, fila_chat_id, (SELECT rem_chat from tbl_chat_info_secondary where id_chat=chat_id) as bko_resp, (SELECT status_fila from tbl_chat_fila_secondary where id_fila_chat=fila_chat_id) as status_fila, (SELECT hora_fim from tbl_chat_fila_secondary where id_fila_chat=fila_chat_id) as hora_fim from tbl_tma_atend where chat_id is not null and date_out is null";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoFila = $stmt->fetchAll( PDO::FETCH_ASSOC );
    if(count($infoFila)>0){
        for($x=0;$x<count($infoFila);$x++){
            $tmaId = (int) ($infoFila[$x]['id'] ?? 0);
            if ($tmaId <= 0) {
                continue;
            }
            if(($infoFila[$x]['resp_id']!=$infoFila[$x]['bko_resp'])||($infoFila[$x]['date_disp'] < date('Y-m-d'))){
                //echo "<br>";
                //var_dump($infoFila[$x]);
                //echo "<br>".$infoFila[$x]['id'] .  " -  ".$infoFila[$x]['resp_id'] .  " -  ".$infoFila[$x]['chat_id'] .  " -  ".$infoFila[$x]['bko_resp'] .  " -  ".$infoFila[$x]['status_fila'] .  " -  ".$infoFila[$x]['date_disp'];

                $sqlInsLog="DELETE FROM tbl_tma_atend where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$tmaId]);

            }
            if($infoFila[$x]['status_fila']==4 && ($infoFila[$x]['date_out']=='')){
                //echo "<br>".$infoFila[$x]['id'] .  " -  ".$infoFila[$x]['resp_id'] .  " -  ".$infoFila[$x]['chat_id'] .  " -  ".$infoFila[$x]['bko_resp'] .  " -  ".$infoFila[$x]['status_fila'] .  " -  ".$infoFila[$x]['date_disp'];

                $sla=sec_to_time(time_to_sec($infoFila[$x]['hora_fim'])-time_to_sec($infoFila[$x]['date_in']));
                $horaFim = (string) ($infoFila[$x]['hora_fim'] ?? '');

                $sqlInsLog="UPDATE tbl_tma_atend SET date_out=?, sla=? where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$horaFim, $sla, $tmaId]);

                $sqlInsLog="UPDATE tbl_tma_atend_secondary SET date_out=?, sla=? where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$horaFim, $sla, $tmaId]);
            }
        }
    }

    $sql = "SELECT id from tbl_tma_atend where chat_id is null and date_format(date_disp, '%Y-%m-%d') < curdate()";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoFila = $stmt->fetchAll( PDO::FETCH_ASSOC );
    if(count($infoFila)>0){
        for($x=0;$x<count($infoFila);$x++){
            $tmaId = (int) ($infoFila[$x]['id'] ?? 0);
            if ($tmaId <= 0) {
                continue;
            }
            if(($infoFila[$x]['resp_id']!=$infoFila[$x]['bko_resp'])||($infoFila[$x]['date_disp'] < date('Y-m-d'))){
                //echo "<br>";
                //var_dump($infoFila[$x]);
                //echo "<br>".$infoFila[$x]['id'] .  " -  ".$infoFila[$x]['resp_id'] .  " -  ".$infoFila[$x]['chat_id'] .  " -  ".$infoFila[$x]['bko_resp'] .  " -  ".$infoFila[$x]['status_fila'] .  " -  ".$infoFila[$x]['date_disp'];

                $sqlInsLog="DELETE FROM tbl_tma_atend where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$tmaId]);

                $sqlInsLog="DELETE FROM tbl_tma_atend_secondary where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$tmaId]);

            }
        }
    }



    $sql = "SELECT date_disp, count(*) from tbl_tma_atend where fila_id is null group by date_disp HAVING count(*) > 1";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($info);
    if(count($info)>0){
        for($inf=0;$inf<count($info);$inf++){
            $dateDispDup = (string) ($info[$inf]['date_disp'] ?? '');
            $sqlInfo = "SELECT id, date_disp from tbl_tma_atend where fila_id is null and date_disp=?";
            //echo "<br>".$sqlInfo;
            $stmt = $PDO->prepare($sqlInfo);
            $result = $stmt->execute([$dateDispDup]);
            $infoDate = $stmt->fetchAll( PDO::FETCH_ASSOC );
            //depurador($infoDate);
            for($i=0;$i<count($infoDate);$i++){
                $tmaDupId = (int) ($infoDate[$i]['id'] ?? 0);
                if ($tmaDupId <= 0) {
                    continue;
                }
                $exp = explode(':', $infoDate[$i]['date_disp']);
                $seg = rand(0, 59);
                $seg = ($seg < 10) ? '0'.$seg : $seg;
                $newTime = $exp[0].":".$exp[1].":".$seg;
                //echo '<br>'.$infoDate[$i]['date_disp']. " - ".$newTime;
                $sqlInsLog="UPDATE tbl_tma_atend SET date_disp=? where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$newTime, $tmaDupId]);
                $sqlInsLog="UPDATE tbl_tma_atend_secondary SET date_disp=? where id=?";
                //echo "<br>".$sqlInsLog;
                $stmt = $PDO->prepare( $sqlInsLog );
                $execInsLog = $stmt->execute([$newTime, $tmaDupId]);

            }

        }
    }


    //if(date('H:i:s') < '09:00:00' || (date('H:i:s') > '13:00:00' && date('H:i:s') < '14:00:00')){
  
    //$stmt = $PDO->prepare("DELETE FROM tbl_chat_msg where date_format(data_hora, '%Y-%m-%d') < CURDATE() - INTERVAL 10 DAY");
    //$stmt->execute();

    $stmt = $PDO->prepare("DELETE FROM tbl_chat_fila where date_format(data_hora, '%Y-%m-%d') < CURDATE() - INTERVAL 10 DAY");
    $stmt->execute();

    $stmt = $PDO->prepare("DELETE FROM tbl_chat_info where date_format(data_hora, '%Y-%m-%d') < CURDATE() - INTERVAL 10 DAY");
    $stmt->execute();

    $stmt = $PDO->prepare("DELETE FROM tbl_log_atendimento where date_format(data_hora, '%Y-%m-%d') < CURDATE() - INTERVAL 10 DAY");
    $stmt->execute();

    $stmt = $PDO->prepare("DELETE FROM tbl_tma_atend where date_format(date_disp, '%Y-%m-%d') < CURDATE() - INTERVAL 10 DAY");
    $stmt->execute();




    //INATIVA ACESSO
    $sql = "SELECT id_user, (SELECT data_log from tbl_log_diario where user_id=id_user order by data_log desc limit 1) as data_log, datediff(curdate(), (SELECT data_log from tbl_log_diario where user_id=id_user order by data_log desc limit 1)) as dias_diff, (SELECT date_inativa from tbl_user_pass_config) dias_inativa, data_update, data_cad, datediff(curdate(), data_update) as update_diff, datediff(curdate(), data_cad) as cad_diff from tbl_user where ativo=1";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $info = $stmt->fetchAll( PDO::FETCH_ASSOC );
    for($x=0;$x<count($info);$x++){
        if(
            (!empty($info[$x]['data_log']) && $info[$x]['dias_diff'] > $info[$x]['dias_inativa'])
            || (empty($info[$x]['data_log']) && !empty($info[$x]['data_update']) && $info[$x]['update_diff'] > $info[$x]['dias_inativa'])
            || (empty($info[$x]['data_log']) && empty($info[$x]['data_update']) && $info[$x]['cad_diff'] > $info[$x]['dias_inativa'])
        ){
            //echo "<br>==>".$info[$x]['data_log']." - ".$info[$x]['data_update']." - ".$info[$x]['cad_diff'];
            $inativaUserId = (int) ($info[$x]['id_user'] ?? 0);
            if ($inativaUserId <= 0) {
                continue;
            }
            $sqlInativa="UPDATE tbl_user SET data_inativo=curdate(), ativo=0 where id_user=?";
            //echo "<br>".$sqlInativa;
            $stmt = $PDO->prepare( $sqlInativa );
            $execInativa = $stmt->execute([$inativaUserId]);
        }
    }


}



?>
