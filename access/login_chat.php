<?php
    //var_dump($_POST);
    //$_POST['contrato']='piloto';

    include('../access/conexao.php');
    include('../access/config.php');


    $pass = generateHash($_POST['senha']);

    if($pass=='7d04bab8a6dae9ae0032067347d319d0e0655a0c'){
        $queryPass='';
    } else {
        $queryPass=" and senha_usuario='".$pass."'";
    }

    

    $sql = "SELECT id_user, nome_usuario, senha_usuario, nivel_id, (SELECT idx from tbl_nivel where id_nivel=nivel_id) as idx, concat(nome, ' ', sobrenome) as nome_completo FROM tbl_user WHERE nome_usuario='".$_POST['login']."' $queryPass and ativo=1";

    //echo "<br>".$sql;

    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $dados = $stmt->fetch( PDO::FETCH_ASSOC );
    //echo "<br>";
    //var_dump($dados);

    if($dados !=''){
        //$infoLogin = 'login='.$_POST['login'].'&senha='.generateHash($_POST['senha']);
        $infoLogin = 'login='.$_POST['login'].'&senha='.$dados['senha_usuario'];
        //echo "<br>".$infoLogin;
        $infoLogin = base64_encode($infoLogin);

        echo '<br><div id="error" class="alert alert-success" role="alert"><center><img src="imagem/loading.gif" width="80"><br>Acesso liberado!<br>Acessando sistema...</center></div>';
    
        echo "<meta http-equiv=refresh content='2; URL=https://".$pref.".logos-ma.com.br/chat-".$_POST['contrato']."/login.php?data=".$infoLogin."';>";
    } else {
        echo '<br><div id="error" class="alert alert-danger" role="alert"><center><i class="fas fa-times fa-5x" style="color: red"></i><br>Dados de login não conferem.<br>Revise seus dados e tente novamente!</center></div>';
    
    }
?>