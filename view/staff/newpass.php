<?php
include("../cnf/conn.php");

header('Access-Control-Allow-Origin: *');

$login = (string) ($_POST['login'] ?? '');
$email = (string) ($_POST['email'] ?? '');

$sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_completo, email, nome_usuario from tbl_user where nome_usuario=? and ativo=1";
$stmt = $PDO->prepare($sql);
$stmt->execute([$login]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);
if (is_array($dados) && ($dados['email'] ?? '') !== '') {
    if ($dados['email'] == $email) {
        $pass = newPass();
        $newsenha = generateHash($pass);
        $sql = "UPDATE tbl_user SET senha_usuario=?, flag_pass=1 where id_user=?";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$newsenha, (int) $dados['id_user']]);
        if ($result == 1) {
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
