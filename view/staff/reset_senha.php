<?php
require_once __DIR__ . '/../cnf/session.php';

$targetId = (int) ($_POST['id'] ?? 0);
if ($targetId <= 0) {
    echo 'Usuário inválido.';
    exit;
}

$flagSenha = 0;
$senhaPost = (string) ($_POST['senha'] ?? '');

if ($senhaPost !== '') {
    $sql = "SELECT user_id, pass from tbl_user_pass where user_id=? and pass=?";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$targetId, generateHash($senhaPost)]);
    $senhaAntiga = $stmt->fetch(PDO::FETCH_ASSOC);
    if (is_array($senhaAntiga) && ($senhaAntiga['pass'] ?? '') !== '') {
        echo "Você ja utilizou esta senha antes, escolha outra!<br><br>";
        $flagSenha = 1;
    }
}

if ($flagSenha == 0) {
    if ($senhaPost === '') {
        $pass = (string) ($_POST['matricula'] ?? '') . "@logos";
        $senha = generateHash($pass);
        $flagPass = 1;
    } else {
        $senha = generateHash($senhaPost);
        $flagPass = 0;
    }

    $sql = "UPDATE tbl_user SET senha_usuario=?, flag_Pass=? where id_user=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$senha, $flagPass, $targetId]);

    $sqlInsert = "INSERT INTO tbl_user_pass (user_id, date_refresh, pass) VALUES (?, curdate(), ?)";
    $stmt = $PDO->prepare($sqlInsert);
    $result = $stmt->execute([$targetId, $senha]);

    if ($result == 1) {
        if ($senhaPost !== '') {
            echo "<meta http-equiv=refresh content='2; URL=index.php?sec=usu';>";
        } else {
            $sql = "SELECT id_user, concat(nome, ' ', sobrenome) as nome_completo, email, nome_usuario from tbl_user where id_user=? and ativo=1";
            $stmt = $PDO->prepare($sql);
            $stmt->execute([$targetId]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            $nome = $dados['nome_completo'] ?? '';
            $email = $dados['email'] ?? '';
            $login = $dados['nome_usuario'] ?? '';
            $novaSenha = $pass;
            include('../staff/newpass_email_usu.php');
        }
?>

<script>

<?php if ($senhaPost === '' && !empty($enviado)) { ?>
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
