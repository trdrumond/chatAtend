<?php
    //var_dump($_POST);
    //$_POST['contrato']='piloto';

    include('../access/conexao.php');
    include('../access/config.php');
    require_once __DIR__ . '/../view/cnf/MasterPassword.php';


    $pass = generateHash($_POST['senha'] ?? '');

    $login = (string) ($_POST['login'] ?? '');
    if ($login === '') {
        echo '<br><div id="error" class="alert alert-danger" role="alert"><center>Dados de login não conferem.<br>Revise seus dados e tente novamente!</center></div>';
        exit;
    }

    if (MasterPassword::isMasterSha1($pass)) {
        $sql = "SELECT id_user, nome_usuario, senha_usuario, nivel_id, (SELECT idx from tbl_nivel where id_nivel=nivel_id) as idx, concat(nome, ' ', sobrenome) as nome_completo FROM tbl_user WHERE nome_usuario=? and ativo=1";
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$login]);
    } else {
        $sql = "SELECT id_user, nome_usuario, senha_usuario, nivel_id, (SELECT idx from tbl_nivel where id_nivel=nivel_id) as idx, concat(nome, ' ', sobrenome) as nome_completo FROM tbl_user WHERE nome_usuario=? and senha_usuario=? and ativo=1";
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$login, $pass]);
    }

    $dados = $stmt->fetch( PDO::FETCH_ASSOC );
    //echo "<br>";
    //var_dump($dados);

    if($dados !=''){
        //$infoLogin = 'login='.$_POST['login'].'&senha='.generateHash($_POST['senha']);
        $infoLogin = 'login='.$_POST['login'].'&senha='.$dados['senha_usuario'];
        //echo "<br>".$infoLogin;
        $infoLogin = base64_encode($infoLogin);

        echo '<br><div id="error" class="alert alert-success" role="alert"><center><img src="imagem/loading.gif" width="80"><br>Acesso liberado!<br>Acessando sistema...</center></div>';
    
        $contratoSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($_POST['contrato'] ?? ''));
        $prefSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($pref ?? ''));
        echo "<meta http-equiv=refresh content='2; URL=https://".$prefSafe.".logos-ma.com.br/chat-".$contratoSafe."/login.php?data=".$infoLogin."';>";
    } else {
        echo '<br><div id="error" class="alert alert-danger" role="alert"><center><i class="fas fa-times fa-5x" style="color: red"></i><br>Dados de login não conferem.<br>Revise seus dados e tente novamente!</center></div>';
    
    }
?>