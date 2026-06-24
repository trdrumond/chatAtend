<?php
include("../cnf/conn.php");


//echo "Teste";
 header('Access-Control-Allow-Origin: *');
//depurador($_POST);

$sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_completo, email, nome_usuario from tbl_user where nome_usuario='".$_POST['login']."' and ativo=1";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetch( PDO::FETCH_ASSOC );
if($dados['email']!=''){
    if($dados['email']==$_POST['email']){
        //echo '<center class="a-reset"><h6>Dados correspondentes!</h6></center>';
        $pass = newPass();
        $newsenha = generateHash($pass);
        $sql="UPDATE tbl_user SET senha_usuario='".$newsenha."', flag_pass=1 where id_user=".$dados['id_user'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();
        if($result==1){
            //echo '<br><br><center class="a-reset"><h6>Senha resetada!</h6></center>';
            $nome = $dados['nome_completo'];
            $email = $dados['email'];
            $login = $dados['nome_usuario'];
            $novaSenha = $pass;
            include('../staff/newpass_email.php');
        }

    } else {
        echo '<br><br><center class="a-reset"><div id="error" class="alert alert-info" role="alert"><h6>O email cadastrado para este usuário é diferente. <br>Solicite ao seu gestor um chamado na central de serviços para atualização de seus dados!</h6></div></center>';
    }
} else {
    echo '<br><br><center class="a-reset"><div id="error" class="alert alert-info" role="alert"><h6>Não existem informações cadastradas para o usuário informado!</h6></div></center>';
}

?>
<br><br><br>

