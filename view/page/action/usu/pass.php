<?php

require_once __DIR__ . '/../../../cnf/session.php';

require_once __DIR__ . '/../cnf/_cnf_ui.php';



if ($infoUser['nivel_id'] == 4) {

    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Indisponivel');

}



$idUser = (int) $_SESSION['dados']['id_user'];



$sql = "SELECT a.id_user, a.nome_usuario, a.nome, a.sobrenome, a.email,"

    . " CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo,"

    . " a.contrato_id, b.nome_contrato,"

    . " a.municipio_id, c.nome_municipio,"

    . " a.agencia_id, d.nome_agencia,"

    . " a.uf_id, e.nome_estado, e.uf,"

    . " a.nivel_id, g.nome_nivel,"

    . " a.regional_id, f.nome_regional,"

    . " a.empresa_id, h.nome_empresa,"

    . " a.fila_id, cf.nome_fila,"

    . " a.ativo,"

    . " DATE_FORMAT(a.data_cad, '%d/%m/%Y') AS data_cad,"

    . " DATE_FORMAT(a.data_inativo, '%d/%m/%Y') AS data_inativo,"

    . " (SELECT img FROM tbl_user_img_perfil WHERE user_id = a.id_user) AS img_perfil"

    . " FROM tbl_user a"

    . " LEFT JOIN tbl_contrato b ON b.id_contrato = a.contrato_id"

    . " LEFT JOIN tbl_municipio c ON c.id_municipio = a.municipio_id"

    . " LEFT JOIN tbl_agencia d ON d.id_agencia = a.agencia_id"

    . " LEFT JOIN tbl_estado e ON e.id_estado = a.uf_id"

    . " LEFT JOIN tbl_regional f ON f.id_regional = a.regional_id"

    . " LEFT JOIN tbl_nivel g ON g.id_nivel = a.nivel_id"

    . " LEFT JOIN tbl_empresa h ON h.id_empresa = a.empresa_id"

    . " LEFT JOIN tbl_config_fila cf ON cf.id_fila = a.fila_id"

    . " WHERE a.id_user = :id_user";



$stmt = $PDO->prepare($sql);

$stmt->bindValue(':id_user', $idUser, PDO::PARAM_INT);

$stmt->execute();

$dados = $stmt->fetch(PDO::FETCH_ASSOC);



if (!is_array($dados)) {

    $dados = [

        'nome' => $infoUser['nome'] ?? '',

        'sobrenome' => $infoUser['sobrenome'] ?? '',

        'nome_completo' => $infoUser['nome_completo'] ?? '',

        'email' => '',

        'nome_usuario' => $infoUser['nome_usuario'] ?? '',

        'nome_nivel' => $infoUser['nivel'] ?? '',

        'nivel_id' => (int) ($infoUser['nivel_id'] ?? 0),

        'nome_contrato' => $infoUser['contrato'] ?? '',

        'nome_empresa' => '',

        'nome_estado' => $infoUser['uf'] ?? '',

        'uf' => $infoUser['ufd'] ?? '',

        'nome_municipio' => $infoUser['municipio'] ?? '',

        'nome_regional' => '',

        'nome_agencia' => $infoUser['agencia'] ?? '',

        'nome_fila' => '',

        'fila_id' => (int) ($infoUser['fila_id'] ?? 0),

        'ativo' => 1,

        'data_cad' => '',

        'data_inativo' => '',

        'img_perfil' => $infoUser['img_perfil'] ?? '',

    ];

}



$ufParts = array_filter([
    trim((string) ($dados['nome_estado'] ?? '')),
    trim((string) ($dados['uf'] ?? '')),
], static function ($v) {
    return $v !== '';
});
$ufLabel = $ufParts ? implode(' / ', $ufParts) : '—';



$situacao = ((int) ($dados['ativo'] ?? 0) === 1) ? 'Ativo' : 'Inativo';

$showFila = in_array((int) ($dados['nivel_id'] ?? 0), [4, 5], true);



$senhaAlert = ($_GET['op'] ?? '') == '1' || ($_GET['op'] ?? '') == '2';

$cardSenhaClass = $senhaAlert ? 'st-form-card st-form-card--alert' : 'st-form-card';

$imgPerfil = st_display_val($dados['img_perfil'] ?? $infoUser['img_perfil'] ?? '', '');

if ($imgPerfil === '—' || $imgPerfil === '') {

    $imgPerfil = 'img/perfil.fw.png';

}



st_page_open('Minha conta', 'Dados do perfil, senha e foto');

st_page_header_close();

?>



<div class="st-form-card">

    <h6 class="st-form-card-title">Informações do usuário</h6>

    <div class="cnf-usu-form-profile mb-3">

        <img src="<?= htmlspecialchars($imgPerfil) ?>" class="cnf-usu-avatar rounded-circle" alt="Foto de perfil"

            onerror="this.src='img/perfil.fw.png'">

        <div class="cnf-usu-form-profile-info">

            <strong><?= htmlspecialchars(st_display_val($dados['nome_completo'])) ?></strong>

            <span class="text-muted"><?= htmlspecialchars(st_display_val($dados['nome_usuario'])) ?></span>

            <span class="cnf-usu-nivel"><?= htmlspecialchars(st_display_val($dados['nome_nivel'])) ?></span>

            <span class="cnf-usu-badge <?= (int) ($dados['ativo'] ?? 0) === 1 ? 'cnf-usu-badge--ok' : 'cnf-usu-badge--off' ?>"><?= $situacao ?></span>

        </div>

    </div>

    <div class="st-form cnf-form">

        <div class="st-form-section cnf-usu-form-section">

            <h6 class="st-form-section-title cnf-usu-form-section-title">Dados pessoais</h6>

            <div class="st-form-grid cnf-form-grid">

                <?php

                cnf_field_input('nome', 'Nome', ['value' => st_display_val($dados['nome']), 'disabled' => true]);

                cnf_field_input('sobrenome', 'Sobrenome', ['value' => st_display_val($dados['sobrenome']), 'disabled' => true]);

                cnf_field_input('email', 'E-mail', ['value' => st_display_val($dados['email']), 'disabled' => true]);

                cnf_field_input('matricula', 'Login / matrícula', ['value' => st_display_val($dados['nome_usuario']), 'disabled' => true]);

                ?>

            </div>

        </div>

        <div class="st-form-section cnf-usu-form-section">

            <h6 class="st-form-section-title cnf-usu-form-section-title">Perfil e localização</h6>

            <div class="st-form-grid cnf-form-grid">

                <?php

                cnf_field_input('nivel', 'Nível', ['value' => st_display_val($dados['nome_nivel']), 'disabled' => true]);

                cnf_field_input('contrato', 'Contrato', ['value' => st_display_val($dados['nome_contrato']), 'disabled' => true]);

                cnf_field_input('empresa', 'Empresa', ['value' => st_display_val($dados['nome_empresa']), 'disabled' => true]);

                cnf_field_input('uf', 'Estado / UF', ['value' => $ufLabel, 'disabled' => true]);

                cnf_field_input('municipio', 'Município', ['value' => st_display_val($dados['nome_municipio']), 'disabled' => true]);

                cnf_field_input('regional', 'Regional', ['value' => st_display_val($dados['nome_regional']), 'disabled' => true]);

                cnf_field_input('agencia', 'Agência', ['value' => st_display_val($dados['nome_agencia']), 'disabled' => true]);

                if ($showFila) {

                    cnf_field_input('fila', 'Fila', ['value' => st_display_val($dados['nome_fila']), 'disabled' => true]);

                }

                ?>

            </div>

        </div>

        <div class="st-form-section cnf-usu-form-section">

            <h6 class="st-form-section-title cnf-usu-form-section-title">Situação</h6>

            <div class="st-form-grid cnf-form-grid">

                <?php

                cnf_field_input('situacao', 'Status', ['value' => $situacao, 'disabled' => true]);

                cnf_field_input('data_cad', 'Data de cadastro', ['value' => st_display_val($dados['data_cad']), 'disabled' => true]);

                cnf_field_input('data_inativo', 'Data de inativação', ['value' => st_display_val($dados['data_inativo']), 'disabled' => true]);

                ?>

            </div>

        </div>

    </div>

</div>



<div class="<?= $cardSenhaClass ?>">

    <h6 class="st-form-card-title">Alteração de senha</h6>

    <div class="st-form cnf-form">

        <div class="st-form-grid st-form-grid--1 cnf-form-grid--narrow">

            <div class="st-field input-container<?= ($_GET['op'] ?? '') == '1' ? ' is-error' : '' ?>">

                <label class="st-label" for="senha">Nova senha</label>

                <input id="senha" class="input" type="password" placeholder="Digite a nova senha" autocomplete="new-password" />

            </div>

        </div>

        <div class="mt-2">

            <button type="button" id="reset" class="btn btn-solvetask" disabled><i class="fas fa-save"></i> Salvar senha</button>

        </div>

        <div id="feed_pass" class="cnf-feed mt-2"></div>

    </div>

    <?php if (($_GET['op'] ?? '') == '1') { ?>

    <script>

    Swal.fire('Atualização de senha', 'Identificamos que você está utilizando a senha gerada pelo sistema. Devido às políticas de segurança do Grupo Logos, é necessário que sua senha seja alterada para continuar utilizando a plataforma.', 'warning');

    </script>

    <?php } ?>

    <?php if (($_GET['op'] ?? '') == '2') { ?>

    <script>

    Swal.fire('Atualização de senha', 'Identificamos que você está utilizando a mesma senha há muito tempo. Devido às políticas de segurança do Grupo Logos, é necessário que sua senha seja alterada para continuar utilizando a plataforma.', 'warning');

    </script>

    <?php } ?>

</div>



<div class="st-form-card">

    <h6 class="st-form-card-title">Imagem de perfil</h6>

    <div class="st-form cnf-form">

        <div class="st-upload-wrap">

            <label id="img_load" for="imgPerfil" title="Selecionar imagem">

                <img src="img/up-imagem.fw.png" width="80" height="80" class="rounded-circle" alt="Upload">

            </label>

            <input id="imgPerfil" name="imgPerfil" type="file" accept="image/*" onchange="encodeImg(this)" />

            <input type="hidden" id="imgText" />

            <button type="button" id="save_img" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar foto</button>

        </div>

        <div id="feed_img" class="cnf-feed"></div>

    </div>

</div>



<?php st_page_close(); ?>



<script src="js/resize.js"></script>

<script>

var resize = new window.resize();

resize.init();



function encodeImg(element) {

    var file = element.files[0];

    $("#img_load").html('<center><i class="fas fa-spinner fa-spin fa-2x"></i></center>');

    resize.photo(file, 300, 'dataURL', function(imagem) {

        $("#img_load").html('<img src="' + imagem + '" width="80" height="80" class="rounded-circle" alt="Preview">');

        $("#imgText").val(imagem);

    });

}



$(document).ready(function() {

    $("#reset").click(function() {

        var id = '<?= $idUser ?>';

        var matricula = '<?= htmlspecialchars($_SESSION['dados']['nome_usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>';

        var senha = $('#senha').val();

        if (senha == '') {

            Swal.fire('Alerta!', 'Preencha o campo de senha com um valor válido.', 'error');

        } else {

            $.post("staff/reset_senha.php", { id, matricula, senha }, function(valor) {

                $("#feed_pass").html(valor);

            });

        }

    });



    $("#senha").keyup(function() {

        $.post("staff/verifica_senha.php", { senha: $('#senha').val() }, function(valor) {

            $("#feed_pass").html(valor);

        });

    });



    $("#save_img").click(function() {

        var id = '<?= $idUser ?>';

        var imgText = $('#imgText').val();

        if (imgText == '') {

            Swal.fire('Alerta!', 'Selecione uma imagem para gravar.', 'error');

        } else {

            $.post("staff/save_img_perfil.php", { id, imgText }, function(valor) {

                $("#feed_img").html(valor);

            });

        }

    });

});

</script>

<script type="text/javascript" src="js/load.js"></script>

