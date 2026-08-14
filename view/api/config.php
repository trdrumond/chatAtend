<?php
include("conn.php");


  $sql ="SELECT * from tbl_user_pass";
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute();
  $config = $stmt->fetch( PDO::FETCH_ASSOC );

  //VERIFICA SE TABELA EXISTE, CASO NÃO, CRIA NO BANCO DE DADOS
  if($config == false){
        $create="CREATE TABLE `tbl_user_pass` ("
            ."`user_id` int(11) NOT NULL DEFAULT 0,"
            ."`date_refresh` date DEFAULT NULL,"
            ."`pass` text DEFAULT NULL,"
            ."UNIQUE KEY `idx` (`user_id`,`date_refresh`,`pass`(10))"
            .") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        //echo "<br>".$create;
        $stmt = $PDO->prepare( $create );
        $result = $stmt->execute();

        $config="CREATE TABLE `tbl_user_pass_config` (
                    `dias_refresh` int(3) NOT NULL DEFAULT 15,
                    `date_inativa` int(3) NOT NULL DEFAULT 30
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        //echo "<br>".$create;
        $stmt = $PDO->prepare( $config );
        $result = $stmt->execute();

        $sqlInstConfig="INSERT INTO tbl_user_pass_config (dias_refresh, date_inativa) VALUES (45, 40)";
        //echo "<br>".$sqlInstConfig;
        $stmt = $PDO->prepare( $sqlInstConfig );
        $result = $stmt->execute();


        $sql ="SELECT $cmpUser, $cmpPass from $tableUser where ativo=1 and $cmpUser>1";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $users = $stmt->fetchAll( PDO::FETCH_ASSOC );
        //echo "<br>".count($users);

        for($x=0;$x <= count($users);$x++){
            if (empty($users[$x]['id_user'])) {
                continue;
            }
            $sqlInsert="INSERT INTO tbl_user_pass (user_id, date_refresh, pass) VALUES (?, curdate(), ?)";
            //echo "<br>".$sqlInsert;
            $stmt = $PDO->prepare( $sqlInsert );
            $result = $stmt->execute([(int) $users[$x]['id_user'], (string) ($users[$x]['senha_usuario'] ?? '')]);
        }

        //echo "Configuração inicial realizada com sucesso!";
  }
  //else {
    //echo 'Configuração inicial ja realizada!';
  //}








?>
