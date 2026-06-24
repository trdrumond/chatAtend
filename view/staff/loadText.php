<?php


//depurador($_POST);

if (strpos($_POST['msg'], '<img') !== false && preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $_POST['msg'])) {
    include("../cnf/conexao.php");
    $chatId = (int) $_POST['chat_id'];

    $sql = "SELECT count(chat_id) as qtd from tbl_img where chat_id=" . $chatId;
    $stmt = $PDO->prepare($sql);
    $stmt->execute();
    $infoImg = $stmt->fetch(PDO::FETCH_ASSOC);

    if ((int) $infoImg['qtd'] === 0) {
        $key = 1;
    } else {
        $key = (int) $infoImg['qtd'];
    }

    $_POST['msg'] .= '<p><a href=staff/img.php?id=' . $chatId . '&key=' . $key . ' target="_blank">Abrir imagem</a></p>';
}
    

echo $_POST['msg'];
echo '<script>
        var testLoad=1;
      </script>';

 



