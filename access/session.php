<?php
/* configuração de duração da sessão (6h) e inicia a sessão */
require_once(__DIR__ . '/../view/cnf/session_config.php');
session_start();
  //set_time_limit(0);
  include("conn.php");
  //echo "<br>conexao";

  //var_dump($_SESSION);

  $idu = $_SESSION['dados']['id_user'];

  $nome = ucwords(str_replace('.', ' ', $_SESSION['dados']['nome_usuario']));
  $id_usuario = $_SESSION['dados']['id_user'];
  $nivel_usu = $_SESSION['dados']['nivel_id'];

  $sql ="SELECT id_user, nome_usuario, nome, sobrenome, concat(nome, ' ', sobrenome) as nome_completo, contrato_id, contrato_id as id_contrato, (SELECT nome_contrato from tbl_contrato where id_contrato=contrato_id) as contrato, municipio_id, (SELECT nome_municipio from tbl_municipio where id_municipio=municipio_id) as municipio, agencia_id, regional_id, (SELECT nome_agencia from tbl_agencia where id_agencia=agencia_id) as agencia, uf_id, (SELECT nome_estado from tbl_estado where id_estado=uf_id) as uf, (SELECT uf from tbl_estado where id_estado=uf_id) as ufd, token, nivel_id, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel, (SELECT idx from tbl_nivel where id_nivel=nivel_id) as idx, (SELECT icon from tbl_nivel where id_nivel=nivel_id) as icon, (SELECT img from tbl_user_img_perfil where user_id=id_user) as img_perfil, fila_id, (SELECT multichat from tbl_config_fila where id_fila=fila_id) as multichat, (SELECT com from tbl_contrato where id_contrato=contrato_id) as comunicacao, (SELECT new_conv from tbl_contrato where id_contrato=contrato_id) as new_conv, (SELECT grupos from tbl_contrato where id_contrato=contrato_id) as grupos, (SELECT men_massa from tbl_contrato where id_contrato=contrato_id) as men_massa, (SELECT resp_men from tbl_contrato where id_contrato=contrato_id) as resp_men from tbl_user where id_user=".$idu;

  //echo "<br>".$sql;
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute();
  $infoUser = $stmt->fetch( PDO::FETCH_ASSOC );
  //depurador($infoUser);

  ///echo  "<br>". $infoUser['id_form'];

  //if($infoUser['img_perfil']==''){$infoUser['img_perfil']='img/perfil.fw.png';}

  $infoUser['img_perfil'] = ($infoUser['img_perfil']=='') ? 'img/perfil.fw.png' : $infoUser['img_perfil'];

  $sql ="SELECT * from tbl_permissao where user_id=".$idu;
  //echo "<br>".$sql;
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute();
  $userPermiss = $stmt->fetch( PDO::FETCH_ASSOC );


  /*

    VERIFICA LOGIN DO DIA
    SE = '' -> GRAVA LOG
    SE != '' -> ATUALIZA DATE UP

  */
  $infoUser['fila_id'] = ($infoUser['fila_id']=='') ? 0 : $infoUser['fila_id'];
  $sqlLog ="SELECT user_id  from tbl_log_diario where data_log=curdate() and user_id=".$idu;
  //echo "<br>".$sqlLog;
  $stmt = $PDO->prepare($sqlLog);
  $result = $stmt->execute();
  $infoLog = $stmt->fetch( PDO::FETCH_ASSOC );
  if($infoLog['user_id']==''){
    $sqlInsLog="INSERT tbl_log_diario (user_id, data_log, ip, date_up, contrato_id, fila_id, municipio_id, regional_id, agencia_id, uf_id, nivel_id) VALUES ('".$idu."', now(), '".$_SERVER['REMOTE_ADDR']."', now(), '".$infoUser['contrato_id']."', '".$infoUser['fila_id']."', '".$infoUser['municipio_id']."', '".$infoUser['regional_id']."', '".$infoUser['agencia_id']."', '".$infoUser['uf_id']."', '".$infoUser['nivel_id']."')";
  } else {
    $sqlInsLog="UPDATE tbl_log_diario SET ip='".$_SERVER['REMOTE_ADDR']."', date_up=now(), date_out = null, contrato_id='".$infoUser['contrato_id']."', fila_id='".$infoUser['fila_id']."', municipio_id='".$infoUser['municipio_id']."', regional_id='".$infoUser['regional_id']."', agencia_id='".$infoUser['agencia_id']."', uf_id='".$infoUser['uf_id']."', nivel_id='".$infoUser['nivel_id']."'  where user_id=".$idu." and data_log=curdate()";
  }
  //echo "<br>".$sqlInsLog;
  $stmt = $PDO->prepare( $sqlInsLog );
  $execInsLog = $stmt->execute();


    if((date('H')>5) && date('H')<12){ $men ="Bom dia"; } else
    if((date('H')>=12) && date('H')<=18){ $men ="Boa tarde"; } else
    if((date('H')>18) && date('H')<=5){ $men ="Boa noite"; }


    //echo "<br>".$infoUser['nivel_id'];
    if($infoUser['nivel_id']==0){
        $sql="SELECT id_contrato, nome_contrato, ativo from tbl_contrato where ativo=1 order by nome_contrato asc";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoContratoMaster = $stmt->fetchAll( PDO::FETCH_ASSOC );
        //depurador($infoContratoMaster);
        $contrat='';
        for($x=0;$x<count($infoContratoMaster);$x++){
            $contrat = $contrat . "'".$infoContratoMaster[$x]['id_contrato']."',";
        }
        $contrat=rtrim($contrat, ", ");
        //echo "<br>".$contrat;
        $infoUserConfig['contrato_id']=$contrat;
    } else

    {
        $infoUserConfig['contrato_id']="'".$infoUser['contrato_id']."'";
    }


    //$infoUserConfig['contrato_id'];



include('rotina_pendencia.php');
include('rotina_ocio.php');
include('horario_fila.php');
//include('rotina.php');
//include('com.php');




  //$_POST['id_form']=4;
  //var_dump($userPermiss);

  if ( $_SESSION['dados']['id_user']=='' )
  {
    session_destroy();
    echo '<meta http-equiv="refresh" content="0;url=../out.php" />';
  }

?>
