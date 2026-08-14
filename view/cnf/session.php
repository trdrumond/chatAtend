<?php
if (defined('ST_SESSION_BOOTSTRAPPED')) {
    return;
}
define('ST_SESSION_BOOTSTRAPPED', true);

/* configuração de duração da sessão (6h) e inicia a sessão */
require_once(__DIR__ . '/session_config.php');
session_start();
  //set_time_limit(0);
  include_once("conn.php");
  //echo "<br>conexao";

  //var_dump($_SESSION);

  if (empty($_SESSION['dados']['id_user'])) {
      session_destroy();
      if (!headers_sent()) {
          header('Location: ../out.php');
      }
      exit;
  }

  $idu = (int) $_SESSION['dados']['id_user'];

  if (empty($_SESSION['st_csrf'])) {
      $_SESSION['st_csrf'] = bin2hex(random_bytes(16));
  }
  $stCsrf = (string) $_SESSION['st_csrf'];

  $stMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
  if ($stMethod === 'POST' || $stMethod === 'PUT' || $stMethod === 'PATCH' || $stMethod === 'DELETE') {
      $stCsrfHdr = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
      $stCsrfPost = (string) ($_POST['st_csrf'] ?? '');
      $stCsrfSent = $stCsrfHdr !== '' ? $stCsrfHdr : $stCsrfPost;
      if ($stCsrfSent === '' || !hash_equals($stCsrf, $stCsrfSent)) {
          http_response_code(403);
          header('Content-Type: text/plain; charset=utf-8');
          echo 'CSRF inválido';
          exit;
      }
  }

  $nome = ucwords(str_replace('.', ' ', $_SESSION['dados']['nome_usuario']));
  $id_usuario = $_SESSION['dados']['id_user'];
  $nivel_usu = $_SESSION['dados']['nivel_id'];

  $sql ="SELECT id_user, nome_usuario, nome, senha_usuario, sobrenome, nome_completo, contrato_id, id_contrato, contrato, municipio_id, municipio, agencia_id, regional_id, agencia, uf_id, uf, ufd, token, nivel_id, nivel, idx, icon, img_perfil, fila_id, multichat, fila_status, comunicacao, new_conv, grupos, men_massa, resp_men, env_img, env_file, flag_pass from info_user where id_user=?";

  //echo "<br>".$sql;
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute([$idu]);
  /** @var array<string, mixed> $infoUser */
  $infoUser = $stmt->fetch( PDO::FETCH_ASSOC );
  if (!is_array($infoUser) || empty($infoUser['id_user'])) {
      session_destroy();
      if (!headers_sent()) {
          header('Location: ../out.php');
      }
      exit;
  }
  //depurador($infoUser);

  //echo "<script>console.log('".depurador($infoUser)."')</script>";

  $sqlMensagem ="SELECT id_campo, contrato_id, assunto_id, titulo_men, txt, data_hora, ativo from tbl_config_men_ini where ativo=1";

  //echo "<br>".$sqlMensagem;
  $stmt = $PDO->prepare($sqlMensagem);
  $result = $stmt->execute();
  $infoMeRapida = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($infoMeRapida);

  $sql ="SELECT mosaico, menu_idx, menu_cnf, cad_cnf from tbl_nivel where id_nivel=?";

  //echo "<br>".$sql;
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute([(int) $infoUser['nivel_id']]);
  $infoNivel = $stmt->fetch( PDO::FETCH_ASSOC );
  if (!is_array($infoNivel)) {
      $infoNivel = [
          'mosaico' => '0,0,0,0,0',
          'menu_idx' => '1,1,1,1,1,1',
          'menu_cnf' => '1,1,1,1,1,1',
          'cad_cnf' => '1',
      ];
  }

  $mosaico = explode(",", (string)($infoNivel['mosaico'] ?? '0,0,0,0,0'));
  $menu_idx = explode(",", (string)($infoNivel['menu_idx'] ?? '1,1,1,1,1,1'));
  while (count($menu_idx) < 7) {
      $menu_idx[] = '1';
  }
  $menu_cnf = explode(",", (string)($infoNivel['menu_cnf'] ?? '1,1,1,1,1,1'));
  $cad_cnf = $infoNivel['cad_cnf'] ?? '';


  include('../api/verif.php');

   //verifData($idu);


  if($infoUser['flag_pass']==1){
    if(($_GET['sec'] ?? '') !='usu'){
        //echo 'FlagSenha = 1';
        echo "<meta http-equiv=refresh content='0; URL=index.php?sec=usu&op=1';>";
    }
  }




  if($infoUser['img_perfil']==''){
    $sqlInsImg="INSERT tbl_user_img_perfil (user_id, img) VALUES (?, ?)";
    $stmt = $PDO->prepare( $sqlInsImg );
    $execInsImg = $stmt->execute([$idu, $img_vazio]);
    if($infoUser['id_user']==3324){
        //depurador($infoUser);
        //echo "<br>".$sqlInsImg;
      }
  }

  $infoUser['img_perfil'] = ($infoUser['img_perfil']=='') ? $img_vazio : $infoUser['img_perfil'];
  if($infoUser['id_user']==3324){
    //depurador($infoUser);
  }

  $sql ="SELECT chat from tbl_permissao where user_id=?";
  //echo "<br>".$sql;
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute([$idu]);
  $userPermiss = $stmt->fetch( PDO::FETCH_ASSOC );
  if (!is_array($userPermiss)) {
      $userPermiss = ['chat' => 0];
  }


  /*

    VERIFICA LOGIN DO DIA
    SE = '' -> GRAVA LOG
    SE != '' -> ATUALIZA DATE UP

  */
  $infoUser['fila_id'] = ($infoUser['fila_id']=='') ? 0 : $infoUser['fila_id'];
  $sqlLog ="SELECT user_id  from tbl_log_diario where data_log=curdate() and user_id=?";
  //echo "<br>".$sqlLog;
  $stmt = $PDO->prepare($sqlLog);
  $result = $stmt->execute([$idu]);
  $infoLog = $stmt->fetch( PDO::FETCH_ASSOC );
  if(!is_array($infoLog) || ($infoLog['user_id'] ?? '')==''){
    $sqlInsLog="INSERT tbl_log_diario (user_id, data_log, ip, date_up, contrato_id, fila_id, municipio_id, regional_id, agencia_id, uf_id, nivel_id) VALUES (?, now(), ?, now(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $PDO->prepare( $sqlInsLog );
    $execInsLog = $stmt->execute([
        $idu,
        (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        $infoUser['contrato_id'],
        $infoUser['fila_id'],
        $infoUser['municipio_id'],
        $infoUser['regional_id'],
        $infoUser['agencia_id'],
        $infoUser['uf_id'],
        $infoUser['nivel_id'],
    ]);
  } else {
    $nowTs = time();
    $lastLogUp = (int) ($_SESSION['st_log_up_ts'] ?? 0);
    if ($nowTs - $lastLogUp >= 60) {
        $_SESSION['st_log_up_ts'] = $nowTs;
        $sqlInsLog="UPDATE tbl_log_diario SET ip=?, date_up=now(), date_out = null, contrato_id=?, fila_id=?, municipio_id=?, regional_id=?, agencia_id=?, uf_id=?, nivel_id=?  where user_id=? and data_log=curdate()";
        $stmt = $PDO->prepare( $sqlInsLog );
        $execInsLog = $stmt->execute([
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            $infoUser['contrato_id'],
            $infoUser['fila_id'],
            $infoUser['municipio_id'],
            $infoUser['regional_id'],
            $infoUser['agencia_id'],
            $infoUser['uf_id'],
            $infoUser['nivel_id'],
            $idu,
        ]);
    }
  }


    if((date('H')>5) && date('H')<12){ $men ="Bom dia"; } else
    if((date('H')>=12) && date('H')<=18){ $men ="Boa tarde"; } else
    { $men ="Boa noite"; }


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
include('rotina_files.php');
include('rotina.php');



if ( $_SESSION['dados']['id_user']=='' )
{
  session_destroy();
  echo '<meta http-equiv="refresh" content="0;url=../out.php" />';
}

?>
