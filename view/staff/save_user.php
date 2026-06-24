<?php
include("../cnf/session.php");


//depurador($_POST);




//$senha = $_POST['matricula']."@logos";
$pass = newPass();
$senha = generateHash($pass);
$_POST['senha'] = $senha;
$_POST['token'] = geraToken(trim($_POST['matricula']));



$sql="INSERT INTO tbl_user (nome_usuario, senha_usuario, nome, sobrenome, email, contrato_id, municipio_id, empresa_id, regional_id, agencia_id, uf_id, token, nivel_id, fila_id, data_update)"
." VALUES ('".trim($_POST['matricula'])."', '".$_POST['senha']."', '".trim($_POST['nome'])."', '".trim($_POST['sobrenome'])."', '".trim($_POST['email'])."', '".$_POST['contrato']."', '".$_POST['municipio']."', '".$_POST['empresa']."', '".$_POST['regional']."', '".$_POST['agencia']."', '".$_POST['uf']."', '".$_POST['token']."', '".$_POST['nivel']."', '".$_POST['fila']."', curdate())";

//echo $sql;

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();







if($result==1){

    $sql = "SELECT id_user, contrato_id, (SELECT nome_contrato from tbl_contrato where id_contrato=contrato_id) as contrato,  municipio_id, (SELECT nome_municipio from tbl_municipio where id_municipio=municipio_id) as municipio, regional_id, (SELECT nome_regional from tbl_regional where id_regional=regional_id) as regional, agencia_id, (SELECT nome_agencia from tbl_agencia where id_agencia=agencia_id) as agencia  from tbl_user where nome_usuario='".$_POST['matricula']."' and ativo=1";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    $dados = $stmt->fetch( PDO::FETCH_ASSOC );

    $sqlInsert="INSERT INTO tbl_user_pass (user_id, date_refresh, pass) VALUES ('".$dados['id_user']."', curdate(), '".$senha."')";
    //echo "<br>".$sqlInsert;
    $stmt = $PDO->prepare( $sqlInsert );
    $result = $stmt->execute();

    $nome = trim($_POST['nome']). " ".trim($_POST['sobrenome']);
    $email = trim($_POST['email']);
    $login = trim($_POST['matricula']);
    $contrato = $dados['contrato'];
    $municipio = $dados['municipio'];
    $regional = $dados['regional'];
    $agencia = $dados['agencia'];
    include('../staff/newpass_email_cad_usu.php');
    if($enviado){
        $sqlmAIL="UPDATE tbl_user SET flag_mail=1 where id_user=".$dados['id_user'];
        //echo "<br>".$sqlmAIL;
        $stmt = $PDO->prepare( $sqlmAIL );
        $result = $stmt->execute();

    ?>

<script>
Swal.fire({
    position: 'bottom-start',
    icon: 'success',
    title: 'Usuário cadastrado com sucesso!',
    showConfirmButton: false,
    timer: 1500
});
$("#new_registro").modal('hide');
actionPage('cad-usu', 'cnf');



function actionPage(action, sec) {
    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
    //console.log('A ação é: ' + action);
    $.post("action.php", {
            action: action,
            sec: sec
        },
        function(valor) {
            $("#action-page").html(valor);
        });
}
</script>
<?php
    } else {
        echo "<div style='color: red'>Usuário foi cadastrado, porém tivemos problemas em enviar o e-mail com as informações do mesmo. <br> Senha gerada: $pass</div>";
    }

} else {
    echo "<div style='color: red'>Houve algum problema ao salvar os dados, verifique se estão corretos ou se não há nenhum registro já criado com algum dos dados no cadastro e tente novamente</div>";
}

?>
