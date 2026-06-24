<?php
require_once __DIR__ . '/../cnf/session.php';
if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}

//var_dump($_POST);

$idAlt = (int) ($_POST['id'] ?? 0);
$sql = "SELECT a.id_user, a.nome_usuario, a.nome, a.sobrenome, a.email,"
    . " CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo, a.contrato_id,"
    . " b.nome_contrato, a.municipio_id, c.nome_municipio, a.agencia_id, d.nome_agencia,"
    . " a.uf_id, e.nome_estado, e.uf, a.nivel_id, a.regional_id, f.nome_regional,"
    . " a.empresa_id, h.nome_empresa, g.nome_nivel, a.ativo,"
    . " DATE_FORMAT(a.data_cad, '%d/%m/%Y') AS data_cad,"
    . " DATE_FORMAT(a.data_inativo, '%d/%m/%Y') AS data_inativo, a.fila_id,"
    . " cf.nome_fila,"
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
$stmt->bindValue(':id_user', $idAlt, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($dados)) {
    echo '<div class="alert alert-warning mb-0">Usuário não encontrado.</div>';
    return;
}

    //var_dump($dados);
?>
<div class="cnf-usu-form st-form">
    <div class="cnf-usu-form-profile">
        <img src="<?= htmlspecialchars($dados['img_perfil'] ?: 'img/perfil.fw.png') ?>" class="cnf-usu-avatar rounded-circle" alt="Foto de perfil" onerror="this.src='img/perfil.fw.png'">
        <div class="cnf-usu-form-profile-info">
            <strong><?= htmlspecialchars($dados['nome_completo']) ?></strong>
            <span class="text-muted"><?= htmlspecialchars($dados['nome_usuario']) ?></span>
            <span class="cnf-usu-nivel"><?= htmlspecialchars($dados['nome_nivel']) ?></span>
            <?php if ($dados['ativo'] == 1) { ?>
                <span class="cnf-usu-badge cnf-usu-badge--ok">Ativo</span>
            <?php } else { ?>
                <span class="cnf-usu-badge cnf-usu-badge--off">Inativo</span>
            <?php } ?>
        </div>
    </div>

    <div class="st-form-section cnf-usu-form-section">
        <h6 class="st-form-section-title cnf-usu-form-section-title">Dados pessoais</h6>
        <div class="st-form-grid cnf-usu-form-grid">
<div class="st-field input-container">
    <label class="st-label" for="nome_<?php echo $dados['id_user']; ?>">Nome</label>
    <input id="nome_<?php echo $dados['id_user']; ?>" class="input" type="text"
        value="<?php echo $dados['nome']; ?>" pattern=".+" required
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?> />
</div>
<div class="st-field input-container">
    <label class="st-label" for="sobrenome_<?php echo $dados['id_user']; ?>">Sobrenome</label>
    <input id="sobrenome_<?php echo $dados['id_user']; ?>" class="input" type="text"
        value="<?php echo $dados['sobrenome']; ?>" pattern=".+" required
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?> />
</div>
<div class="st-field input-container">
    <label class="st-label" for="email_<?php echo $dados['id_user']; ?>">E-mail</label>
    <input id="email_<?php echo $dados['id_user']; ?>" class="input" type="email"
        value="<?php echo $dados['email']; ?>" pattern=".+" required
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?> />
</div>
<div class="st-field input-container">
    <label class="st-label" for="matricula_<?php echo $dados['id_user']; ?>">Login</label>
    <input id="matricula_<?php echo $dados['id_user']; ?>" class="input" type="text"
        value="<?php echo $dados['nome_usuario']; ?>" pattern=".+" disabled />
</div>
        </div>
    </div>

<?php if(($infoUser['nivel_id']==0)  || ($infoUser['nivel_id']==1)  || ($infoUser['nivel_id'] > 0 && $dados['nivel_id']!=4) ){ ?>
    <div class="st-form-section cnf-usu-form-section">
        <h6 class="st-form-section-title cnf-usu-form-section-title">Perfil e localização</h6>
        <div class="st-form-grid cnf-usu-form-grid">
<div class="st-field input-container">
    <label class="st-label" for="nivel_<?php echo $dados['id_user']; ?>">Nível</label>
    <select name="nivel_<?php echo $dados['id_user']; ?>" id="nivel_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="">Selecione...</option>
            <?php
                                                    $qry = '';
                                                    if($_SESSION['dados']['nivel_id']>=2){$qry=" WHERE id_nivel>=5";}

                                                    $sql="SELECT id_nivel, nome_nivel, icon from tbl_nivel $qry order by id_nivel asc";
                                                    //echo "<br>".$sql;

                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['nivel_id']==$dds[$y]['id_nivel']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_nivel'].'" '.$sel.'>'.$dds[$y]['nome_nivel'].'</option>';
                                                    }
                                                ?>
        </select>
</div>
<?php } ?>
<?php

                                    ?>
<div class="st-field input-container">
    <label class="st-label" for="uf_<?php echo $dados['id_user']; ?>">UF</label>
    <select name="uf_<?php echo $dados['id_user']; ?>" id="uf_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="">Selecione...</option>
            <?php
                                                    $qryUf = '';
                                                    if(($infoUser['nivel_id'] ?? 0) > 1){ $qryUf = " and id_estado='".$infoUser['uf_id']."'"; }
                                                    $sql="SELECT id_estado, nome_estado, uf from tbl_estado where id_estado<>'' $qryUf order by nome_estado";
                                                    //echo "<br>".$sql;
                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['uf_id']==$dds[$y]['id_estado']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_estado'].'" '.$sel.'>'.$dds[$y]['nome_estado'].' - '.$dds[$y]['uf'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

<div class="st-field input-container">
    <label class="st-label" for="municipio_<?php echo $dados['id_user']; ?>">Município</label>
    <select name="municipio_<?php echo $dados['id_user']; ?>" id="municipio_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="">Selecione...</option>
            <?php
                                                    $sql="SELECT id_municipio, nome_municipio, uf from tbl_municipio where uf='".$dados['uf']."' order by nome_municipio";
                                                    //echo "<br>".$sql;

                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['municipio_id']==$dds[$y]['id_municipio']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_municipio'].'" '.$sel.'>'.$dds[$y]['nome_municipio'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

<div class="st-field input-container">
    <label class="st-label" for="contrato_<?php echo $dados['id_user']; ?>">Contrato</label>
    <select name="contrato_<?php echo $dados['id_user']; ?>" id="contrato_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="">Selecione...</option>
            <?php
                                                    $sql="SELECT id_contrato, nome_contrato, uf from tbl_contrato where ativo=1 order by nome_contrato";
                                                    //echo "<br>".$sql;
                                                    //echo '<option value="">'.$sql.'</option>';
                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['contrato_id']==$dds[$y]['id_contrato']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_contrato'].'" '.$sel.'>'.$dds[$y]['nome_contrato'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

<div class="st-field input-container">
    <label class="st-label" for="empresa_<?php echo $dados['id_user']; ?>">Empresa</label>
    <select name="empresa_<?php echo $dados['id_user']; ?>" id="empresa_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="0">Selecione...</option>
            <?php
                                                    $sql="SELECT id_empresa, nome_empresa, contrato_id from tbl_empresa where ativo=1 and contrato_id='".$dados['contrato_id']."' order by nome_empresa";
                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['empresa_id']==$dds[$y]['id_empresa']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_empresa'].'" '.$sel.'>'.$dds[$y]['nome_empresa'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

<div class="st-field input-container">
    <label class="st-label" for="regional_<?php echo $dados['id_user']; ?>">Regional</label>
    <select name="regional_<?php echo $dados['id_user']; ?>" id="regional_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="0">Selecione...</option>
            <?php
                                                    $sql="SELECT id_regional, nome_regional, contrato_id from tbl_regional where ativo=1 and contrato_id='".$dados['contrato_id']."' order by nome_regional";
                                                    //echo "<br>".$sql;
                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['regional_id']==$dds[$y]['id_regional']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_regional'].'" '.$sel.'>'.$dds[$y]['nome_regional'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

<div class="st-field input-container">
    <label class="st-label" for="agencia_<?php echo $dados['id_user']; ?>">Agência</label>
    <select name="agencia_<?php echo $dados['id_user']; ?>" id="agencia_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="0">Selecione...</option>
            <?php
                                                    $sql="SELECT id_agencia, nome_agencia, contrato_id, regional_id, ativo from tbl_agencia where ativo=1 and contrato_id='".$dados['contrato_id']."' order by nome_agencia";
                                                    //echo "<br>".$sql;
                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['agencia_id']==$dds[$y]['id_agencia']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_agencia'].'" '.$sel.'>'.$dds[$y]['nome_agencia'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

<div id="div_fila_<?php echo $dados['id_user']; ?>" class="st-field input-container"
    <?php if($dados['nivel_id']!=4 && $dados['nivel_id']!=5){ ?>style="display: none;" <?php } ?>>
    <label class="st-label" for="fila_<?php echo $dados['id_user']; ?>">Fila</label>
    <select name="fila_<?php echo $dados['id_user']; ?>" id="fila_<?php echo $dados['id_user']; ?>"
        <?php if($infoUser['nivel_id']>1){ echo 'disabled'; } ?>>
            <option value="0">Selecione...</option>
            <?php
                                                    $sql="SELECT id_fila, nome_fila from tbl_config_fila where contrato_id='".$dados['contrato_id']."' order by nome_fila";
                                                    //echo "<br>".$sql;
                                                    $stmt = $PDO->prepare($sql);
                                                    $result = $stmt->execute();
                                                    $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                                    for($y=0;$y<count($dds);$y++){
                                                        if($dados['fila_id']==$dds[$y]['id_fila']){$sel="selected";} else {$sel="";}
                                                        echo '<option value="'.$dds[$y]['id_fila'].'" '.$sel.'>'.$dds[$y]['nome_fila'].'</option>';
                                                    }
                                                ?>
        </select>
</div>

        </div>
    </div>

    <div class="st-form-section cnf-usu-form-section">
        <h6 class="st-form-section-title cnf-usu-form-section-title">Situação</h6>
        <div class="st-form-grid st-form-grid--1 cnf-usu-form-grid cnf-usu-form-grid--narrow">
<div class="st-field input-container">
    <label class="st-label" for="ativo_<?php echo $dados['id_user']; ?>">Status</label>
    <select name="ativo_<?php echo $dados['id_user']; ?>" id="ativo_<?php echo $dados['id_user']; ?>">
            <option value="1" <?php if($dados['ativo']==1){ echo "Selected";} else { echo "";} ?>>Ativo
            </option>
            <option value="0" <?php if($dados['ativo']==0){ echo "Selected";} else { echo "";} ?>>Inativo
            </option>
        </select>
</div>
        </div>
    </div>
</div>

<div class="modal-footer cnf-usu-modal-footer cnf-usu-modal-footer--inline">
    <div id="feed_alt_<?php echo $dados['id_user']; ?>" class="cnf-usu-feed"></div>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fechar</button>
    <button type="button" id="save_img_<?php echo $dados['id_user']; ?>" class="btn btn-outline-info" title="Reset de imagem de perfil">
        <i class="fas fa-user-circle"></i> Reset foto
    </button>
    <button type="button" id="save_alt_<?php echo $dados['id_user']; ?>" class="btn btn-solvetask">
        <i class="fas fa-save"></i> Salvar
    </button>
</div>


<script>
$(document).ready(function() {



    $("#save_alt_<?php echo $dados['id_user']; ?>").click(function() {
        var id = <?php echo $dados['id_user']; ?>;
        var nome = $('#nome_<?php echo $dados['id_user']; ?>').val();
        var sobrenome = $('#sobrenome_<?php echo $dados['id_user']; ?>').val();
        var email = $('#email_<?php echo $dados['id_user']; ?>').val();
        var uf = $('#uf_<?php echo $dados['id_user']; ?>').val();
        var municipio = $('#municipio_<?php echo $dados['id_user']; ?>').val();
        var nivel = $('#nivel_<?php echo $dados['id_user']; ?>').val();
        var contrato = $('#contrato_<?php echo $dados['id_user']; ?>').val();
        var regional = $('#regional_<?php echo $dados['id_user']; ?>').val();
        var empresa = $('#empresa_<?php echo $dados['id_user']; ?>').val();
        var agencia = $('#agencia_<?php echo $dados['id_user']; ?>').val();
        var fila = $('#fila_<?php echo $dados['id_user']; ?>').val();
        var ativo = $('#ativo_<?php echo $dados['id_user']; ?>').val();
        altUser_<?php echo $dados['id_user']; ?>(id, nome, sobrenome, email, uf, municipio,
            nivel, contrato, regional, empresa, agencia, fila, ativo);
    });

    $("#save_img_<?php echo $dados['id_user']; ?>").click(function() {
        var id = <?php echo $dados['id_user']; ?>;
        $("#feed_alt_<?php echo $dados['id_user']; ?>").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
        );
        $.post("staff/alt_reset_img.php", {
                id
            },
            function(valor) {
                $("#feed_alt_<?php echo $dados['id_user']; ?>").html(valor);
            });
    });

    function altUser_<?php echo $dados['id_user']; ?>(id, nome, sobrenome, email, uf, municipio,
        nivel, contrato, regional, empresa, agencia, fila, ativo) {
        //console.log("Fila " + fila);
        $("#feed_alt_<?php echo $dados['id_user']; ?>").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
        );

        if (nome == '') {
            menAlert('Nome');
        } else
        if (sobrenome == '') {
            menAlert('Sobrenome');
        } else
        if (matricula == '') {
            menAlert('Matricula Logos');
        } else
        if (nivel == '') {
            menAlert('Nível');
        } else
        if (uf == '') {
            menAlert('UF');
        } else
        if (municipio == '') {
            menAlert('Município');
        } else
        if (contrato == '') {
            menAlert('Contrato');
        } else
        if (regional == '') {
            menAlert('Regional');
        } else
        if (empresa == '') {
            menAlert('Empresa');
        } else
        if (agencia == '') {
            menAlert('Agência');
        } else
        if (nivel == 4 && fila == 0) {
            menAlert('Fila');
        } else

        {
            $.post("staff/alt_user.php", {
                    id,
                    nome,
                    sobrenome,
                    email,
                    uf,
                    municipio,
                    nivel,
                    contrato,
                    regional,
                    empresa,
                    agencia,
                    fila,
                    ativo
                },
                function(valor) {
                    $("#feed_alt_<?php echo $dados['id_user']; ?>").html(valor);
                });
        }
    }

    function menAlert(campo) {
        $("#feed_alt_<?php echo $dados['id_user']; ?>").html(
            '<div style="color: red">O campo <strong>' + campo +
            '</strong> deve ser preenchido corretamente</div>');
    }

    $("#contrato_<?php echo $dados['id_user']; ?>").change(function() {
        var contrato = $('#contrato_<?php echo $dados['id_user']; ?>').val();
        var nivel = $('#nivel_<?php echo $dados['id_user']; ?>').val();
        loadRegional_<?php echo $dados['id_user']; ?>(contrato);
        loadEmpresa_<?php echo $dados['id_user']; ?>(contrato);
        if (nivel == 4) {
            loadFila_<?php echo $dados['id_user']; ?>(contrato);
        }
    });

    $("#nivel_<?php echo $dados['id_user']; ?>").change(function() {
        var nivel = $('#nivel_<?php echo $dados['id_user']; ?>').val();
        if (nivel == 4 || nivel == 5) {
            $("#div_fila_<?php echo $dados['id_user']; ?>").show();
        } else {
            $("#div_fila_<?php echo $dados['id_user']; ?>").hide();
        }
    });

    $("#uf_<?php echo $dados['id_user']; ?>").change(function() {
        var uf = $('#uf_<?php echo $dados['id_user']; ?>').val();
        loadUf_<?php echo $dados['id_user']; ?>(uf);
    });

    $("#regional_<?php echo $dados['id_user']; ?>").change(function() {
        var regional = $('#regional_<?php echo $dados['id_user']; ?>').val();
        loadAgencia_<?php echo $dados['id_user']; ?>(regional);
    });



    function loadRegional_<?php echo $dados['id_user']; ?>(contrato) {
        $("#regional_<?php echo $dados['id_user']; ?>").html('Carregando...');
        //console.log("Contrato escolhido: "+contrato);
        $.post("staff/load_regional.php", {
                contrato
            },
            function(valor) {
                $("#regional_<?php echo $dados['id_user']; ?>").html(valor);
            });
    }

    function loadEmpresa_<?php echo $dados['id_user']; ?>(contrato) {
        $("#empresa_<?php echo $dados['id_user']; ?>").html('Carregando...');
        //console.log("Contrato escolhido: "+contrato);
        $.post("staff/load_empresa.php", {
                contrato
            },
            function(valor) {
                $("#empresa_<?php echo $dados['id_user']; ?>").html(valor);
            });
    }

    function loadAgencia_<?php echo $dados['id_user']; ?>(regional) {
        $("#agencia_<?php echo $dados['id_user']; ?>").html('Carregando...');
        //console.log("Regional escolhido: "+regional);
        $.post("staff/load_agencia.php", {
                regional
            },
            function(valor) {
                $("#agencia_<?php echo $dados['id_user']; ?>").html(valor);
            });
    }

    function loadUf_<?php echo $dados['id_user']; ?>(uf) {
        $("#contrato_<?php echo $dados['id_user']; ?>").html('Carregando...');
        $("#municipio_<?php echo $dados['id_user']; ?>").html('Carregando...');
        //console.log("Contrato escolhido: " + uf);
        $.post("staff/load_contrato.php", {
                uf
            },
            function(valor) {
                $("#contrato_<?php echo $dados['id_user']; ?>").html(valor);
            });

        $.post("staff/load_municipio.php", {
                uf
            },
            function(valor) {
                $("#municipio_<?php echo $dados['id_user']; ?>").html(valor);
            });
    }

    function loadFila_<?php echo $dados['id_user']; ?>(contrato) {
        $("#div_fila_<?php echo $dados['id_user']; ?>").show();
        $("#fila_<?php echo $dados['id_user']; ?>").html('Carregando fila...');
        $.post("staff/load_fila_user.php", {
                contrato
            },
            function(valor) {
                $("#fila_<?php echo $dados['id_user']; ?>").html(valor);
            });
    }
});
</script>
