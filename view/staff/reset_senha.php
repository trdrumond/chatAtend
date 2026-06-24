<?php
include("../cnf/conn.php");

//depurador($_POST);

$idu = $_POST['id'];
$flagSenha = 0;

if($_POST['senha']!=''){
//echo "<br>".$_POST['senha'];

//echo "<br>".senhaValida($_POST['senha']);

//$flagSenha = 1;
/*
    if(senhaValida($_POST['senha'])==0){
        echo "Sua nova senha deve conter:
                <ul>
                    <li>No mínimo 8 caracteres</li>
                    <li>Pelo menos uma letra maiúscula</li>
                    <li>Pelo menos uma letra minúscula</li>
                    <li>Pelo menos um caracter numérico</li>
                </ul><br><br>";
                $flagSenha = 1;
    } else {
        $sql = "SELECT user_id, pass from tbl_user_pass where user_id='".$_POST['id']."' and pass='".generateHash($_POST['senha'])."'";
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();
        $senhaAntiga = $stmt->fetch( PDO::FETCH_ASSOC );
        if($senhaAntiga['pass']!=''){
            echo "Você ja utilizou esta senha antes, escolha outra!<br><br>";
            $flagSenha = 1;
        }
    }
*/
    $sql = "SELECT user_id, pass from tbl_user_pass where user_id='".$_POST['id']."' and pass='".generateHash($_POST['senha'])."'";
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $senhaAntiga = $stmt->fetch( PDO::FETCH_ASSOC );
    if($senhaAntiga['pass']!=''){
        echo "Você ja utilizou esta senha antes, escolha outra!<br><br>";
        $flagSenha = 1;
    }
}



if($flagSenha==0){


    if($_POST['senha']==''){
        //$pass = newPass();
        $pass = $_POST['matricula']."@logos";
        $senha = generateHash($pass);
        $flagPass = 1;
    } else {
        $senha = generateHash($_POST['senha']);
        $flagPass = 0;
    }

    //echo "<br>".$senha;
    //echo "<br>".$pass;







    $sql="UPDATE tbl_user SET senha_usuario='".$senha."', flag_Pass=".$flagPass." where id_user=".$_POST['id'];

    //echo "<br>".$sql;

    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sqlInsert="INSERT INTO tbl_user_pass (user_id, date_refresh, pass) VALUES ('".$_POST['id']."', curdate(), '".$senha."')";
    //echo "<br>".$sqlInsert;
    $stmt = $PDO->prepare( $sqlInsert );
    $result = $stmt->execute();

    if($result==1){
        //echo "Teste 1";
        if($_POST['senha']!=''){
            echo "<meta http-equiv=refresh content='2; URL=index.php?sec=usu';>";
        } else {
            //echo "Teste 2";
            $sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_completo, email, nome_usuario from tbl_user where id_user='".$_POST['id']."' and ativo=1";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
            $dados = $stmt->fetch( PDO::FETCH_ASSOC );

            $nome = $dados['nome_completo'];
            $email = $dados['email'];
            $login = $dados['nome_usuario'];
            $novaSenha = $pass;
            include('../staff/newpass_email_usu.php');
        }

    ?>

<script>

<?php if($_POST['senha']=='' && ($enviado)){ ?>
Swal.fire(
    'Nova senha',
    'Uma nova senha foi enviada para o e-mail cadastrado para este usuário</strong>',
    'success'
);
<?php } else { ?>
Swal.fire(
    'Sua senha foi Alterada!',
    'Memorize sua senha para acessar o sistema com mais segurança',
    'success'
);
<?php } ?>

</script>

<?php
    }
}

    ?>
