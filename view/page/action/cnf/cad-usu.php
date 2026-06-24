<?php
require_once __DIR__ . '/../../../cnf/session.php';
if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}
if (!isset($cad_cnf)) {
    $cad_cnf = 0;
}
//depurador($_SERVER);


$url = $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'];
$url = str_replace("action.php", "", $url);
$urlDownload = $url . 'staff/base.xlsx';

?>

<script>
    function resetPass(id, matricula) {
        Swal.fire({
            title: 'Nova Senha',
            text: "Tem certeza que deseja gerar uma nova senha para este usuário?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, resetar!',
            cancelButtonText: 'Não'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Gerando nova senha',
                    showConfirmButton: false,
                });
                $.post("staff/reset_senha.php", {
                        id,
                        matricula
                    },
                    function(valor) {
                        $("#passNew").html(valor);
                    }
                );
            }
        })
    }
</script>

<?php $limitInicial = 200; ?>

<div class="cnf-page cnf-usu-page">
    <header class="cnf-usu-header">
        <div>
            <h5 class="cnf-usu-title">Cadastro de Usuários</h5>
            <p class="cnf-usu-sub">Gestão completa de usuários, filas e acessos</p>
        </div>
        <?php if ($cad_cnf == 1) { ?>
        <div class="cnf-usu-actions">
            <button type="button" class="btn btn-solvetask btn-sm" data-bs-toggle="modal" data-bs-target="#new_registro" title="Novo usuário">
                <i class="fas fa-plus"></i> Novo
            </button>
            <button type="button" id="downBase" class="btn btn-outline-secondary btn-sm" title="Download da base modelo">
                <i class="fas fa-file-export"></i> Modelo
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#new_import" title="Importar usuários em massa">
                <i class="fas fa-file-import"></i> Importar
            </button>
        </div>
        <?php } ?>
    </header>

    <div class="cnf-usu-toolbar">
        <div class="cnf-usu-search-wrap">
            <i class="fas fa-search cnf-usu-search-icon" aria-hidden="true"></i>
            <input id="buscaUsuarios" class="form-control cnf-usu-search" type="search" placeholder="Pesquisar em todos os campos..." autocomplete="off">
        </div>
        <button type="button" id="btnExportUsuarios" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-file-excel"></i> Exportar Excel
        </button>
        <div class="cnf-usu-load-meta">
            <span id="infoUsuarios">Carregando usuários...</span>
            <div class="cnf-usu-progress" id="usuariosProgressWrap" aria-hidden="true">
                <div class="cnf-usu-progress-bar" id="usuariosProgressBar" style="width:0%"></div>
            </div>
        </div>
    </div>

    <div class="cnf-usu-table-wrap">
        <table id="tabela" class="table table-sm cnf-usu-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Login</th>
                    <th>E-mail</th>
                    <th>Local</th>
                    <th>UF</th>
                    <th>Empresa</th>
                    <th>Nível</th>
                    <th>Fila</th>
                    <th>Cadastro</th>
                    <th>Inativação</th>
                    <th class="d-none">Situação txt</th>
                    <th class="text-center">Situação</th>
                    <th class="text-center cnf-usu-col-act" title="Filas">Filas</th>
                    <th class="text-center cnf-usu-col-act" title="E-mail">Mail</th>
                    <th class="text-center cnf-usu-col-act" title="Editar">Editar</th>
                    <th class="text-center cnf-usu-col-act" title="Senha">Senha</th>
                </tr>
            </thead>
            <tbody id="tbody-usuarios">
            </tbody>
        </table>
        <div id="usuariosLoadingMore" class="cnf-usu-loading-more d-none">
            <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
            <span>Carregando mais registros...</span>
        </div>
    </div>
</div>
<script>
    var usuariosOffset = 0;
    var usuariosLimit = <?= $limitInicial; ?>;
    var usuariosLoading = false;
    var usuariosHasMore = true;
    var usuariosBusca = '';
    var usuariosTotal = 0;
    var usuariosScrollHandlerAttached = false; // não será mais usado (scroll infinito desativado)
    var usuariosAutoLoadTimer = null;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getUsuarioRowClasses(u) {
        if (u.ativo != 1) {
            return 'cnf-usu-row-inactive';
        }
        var nivelId = parseInt(u.nivel_id, 10);
        if (isNaN(nivelId) || nivelId < 0 || nivelId > 5) {
            nivelId = 99;
        }
        return 'cnf-usu-row-active cnf-usu-row-nivel-' + nivelId;
    }

    function renderUsuarios(rows) {
        if (!rows || !rows.length) return;
        var $tbody = $("#tbody-usuarios");
        rows.forEach(function(u) {
            var ativoBadge = u.ativo == 1
                ? '<span class="cnf-usu-badge cnf-usu-badge--ok" title="Ativo"><i class="fas fa-check"></i></span>'
                : '<span class="cnf-usu-badge cnf-usu-badge--off" title="Inativo"><i class="fas fa-times"></i></span>';
            var ativoString = u.ativo == 1 ? 'Ativo' : 'Inativo';

            var nomeFila = '';
            if (u.nome_fila) {
                var filaStr = String(u.nome_fila).trim().toLowerCase();
                if (filaStr !== 'null' && filaStr !== 'undefined') {
                    nomeFila = u.nome_fila;
                }
            }

            var filasIcon = '';
            if (u.nivel_id == 4) {
                filasIcon = '<button type="button" class="cnf-usu-icon-btn" title="Configurar filas" data-bs-toggle="modal" data-bs-target="#modal_filas" onclick="loadFilas(' + u.id_user + ')"><i class="fas fa-list"></i></button>';
            }

            var mailIcon = '';
            if (u.permite_mail == 1) {
                if (u.flag_mail == 0) {
                    mailIcon = '<button type="button" id="mail_' + u.id_user + '" class="cnf-usu-icon-btn" title="E-mail não enviado" data-bs-toggle="modal" data-bs-target="#modal_mail" onclick="loadMail(' + u.id_user + ')"><i class="far fa-envelope"></i></button>';
                } else {
                    mailIcon = '<button type="button" id="mail_' + u.id_user + '" class="cnf-usu-icon-btn cnf-usu-icon-btn--sent" title="Reenviar e-mail" data-bs-toggle="modal" data-bs-target="#modal_mail" onclick="loadMail(' + u.id_user + ')"><i class="fas fa-paper-plane"></i></button>';
                }
            }

            var editIcon = (u.cad_cnf == 1)
                ? '<button type="button" class="cnf-usu-icon-btn" title="Alterar usuário" data-bs-toggle="modal" data-bs-target="#modal_alt" onclick="loadAlt(' + u.id_user + ')"><i class="fas fa-edit"></i></button>'
                : '<button type="button" class="cnf-usu-icon-btn" title="Ver detalhes" data-bs-toggle="modal" data-bs-target="#modal_alt" onclick="loadAlt(' + u.id_user + ')"><i class="fas fa-info-circle"></i></button>';

            var loginSafe = escapeHtml(u.nome_usuario).replace(/'/g, "\\'");
            var resetIcon = '<button type="button" class="cnf-usu-icon-btn cnf-usu-icon-btn--key" title="Resetar senha" id="reset_' + u.id_user + '" onclick="resetPass(' + u.id_user + ', \'' + loginSafe + '\')"><i class="fas fa-key"></i></button>';

            var dataInat = '';
            if (u.data_inativo) {
                var di = String(u.data_inativo).trim().toLowerCase();
                if (di !== 'null' && di !== 'undefined' && di !== '0000-00-00' && di !== '00/00/0000') {
                    dataInat = u.data_inativo;
                }
            }

            var rowClass = ' class="' + getUsuarioRowClasses(u) + '"';

            var tr = '<tr' + rowClass + ' data-nivel-id="' + escapeHtml(u.nivel_id) + '" data-ativo="' + escapeHtml(u.ativo) + '">' +
                '<td class="cnf-usu-col-name"><strong>' + escapeHtml(u.nome_completo) + '</strong></td>' +
                '<td><code class="cnf-usu-login">' + escapeHtml(u.nome_usuario) + '</code></td>' +
                '<td class="cnf-usu-col-email">' + escapeHtml(u.email) + '</td>' +
                '<td>' + escapeHtml(u.nome_agencia) + '</td>' +
                '<td class="text-center">' + escapeHtml(u.uf) + '</td>' +
                '<td>' + escapeHtml(u.nome_empresa) + '</td>' +
                '<td><span class="cnf-usu-nivel">' + escapeHtml(u.nome_nivel) + '</span></td>' +
                '<td>' + escapeHtml(nomeFila) + '</td>' +
                '<td class="text-center text-nowrap">' + escapeHtml(u.data_cad) + '</td>' +
                '<td class="text-center text-nowrap">' + escapeHtml(dataInat) + '</td>' +
                '<td class="d-none">' + escapeHtml(ativoString) + '</td>' +
                '<td class="text-center">' + ativoBadge + '</td>' +
                '<td class="text-center">' + filasIcon + '</td>' +
                '<td class="text-center">' + mailIcon + '</td>' +
                '<td class="text-center">' + editIcon + '</td>' +
                '<td class="text-center">' + resetIcon + '</td>' +
                '</tr>';
            $tbody.append(tr);
        });
    }

    function atualizarProgressoUsuarios() {
        var total = usuariosTotal || 0;
        var pct = total > 0 ? Math.min(100, Math.round((usuariosOffset / total) * 100)) : 0;
        $("#usuariosProgressBar").css('width', pct + '%');
        if (usuariosHasMore && usuariosLoading) {
            $("#usuariosProgressWrap").attr('aria-hidden', 'false').show();
        } else if (!usuariosHasMore) {
            $("#usuariosProgressBar").css('width', '100%');
            setTimeout(function() { $("#usuariosProgressWrap").fadeOut(200); }, 400);
        }
    }

    function atualizarInfoUsuarios() {
        var texto;
        if (usuariosLoading && usuariosOffset === 0) {
            texto = 'Carregando usuários...';
        } else if (usuariosHasMore) {
            texto = 'Carregados ' + usuariosOffset + ' de ' + (usuariosTotal || '?') + ' usuários';
        } else if (usuariosTotal === 0) {
            texto = 'Nenhum usuário encontrado';
        } else {
            texto = usuariosTotal + ' usuário(s) exibidos';
        }
        $("#infoUsuarios").text(texto);
        atualizarProgressoUsuarios();
    }

    function loadUsuarios(reset) {
        if (usuariosLoading) return;
        usuariosLoading = true;

        if (usuariosAutoLoadTimer) {
            clearTimeout(usuariosAutoLoadTimer);
            usuariosAutoLoadTimer = null;
        }

        if (reset) {
            usuariosOffset = 0;
            usuariosHasMore = true;
            $("#tbody-usuarios").empty();
            $("#usuariosProgressWrap").show();
            $("#usuariosProgressBar").css('width', '0%');
        }

        $("#usuariosLoadingMore").toggleClass('d-none', reset);
        if (!reset) $("#usuariosLoadingMore").removeClass('d-none');
        atualizarInfoUsuarios();

        $.post("staff/load_usuarios.php", {
            offset: usuariosOffset,
            limit: usuariosLimit,
            busca: usuariosBusca
        }, function(resp) {
            usuariosLoading = false;
            $("#usuariosLoadingMore").addClass('d-none');
            try {
                var data = typeof resp === "string" ? JSON.parse(resp) : resp;
                if (Array.isArray(data.rows)) {
                    renderUsuarios(data.rows);
                    usuariosOffset += data.rows.length;
                    usuariosTotal = data.total || usuariosTotal;
                    usuariosHasMore = data.hasMore === true;
                    atualizarInfoUsuarios();

                    if (usuariosHasMore) {
                        usuariosAutoLoadTimer = setTimeout(function() {
                            loadUsuarios(false);
                        }, 350);
                    }
                } else {
                    $("#infoUsuarios").text('Não foi possível carregar os usuários.');
                }
            } catch (e) {
                console.error("Erro ao processar retorno de load_usuarios.php", e, resp);
                $("#infoUsuarios").text('Erro ao carregar os usuários.');
            }
        }).fail(function() {
            usuariosLoading = false;
            $("#usuariosLoadingMore").addClass('d-none');
            $("#infoUsuarios").text('Erro ao carregar os usuários.');
        });
    }

    $(document).ready(function() {
        // Carga inicial
        loadUsuarios(true);

        // Busca em todos os campos (na base completa, via backend)
        var buscaTimeout = null;
        $("#buscaUsuarios").on("keyup", function() {
            var val = $(this).val();
            usuariosBusca = val;
            if (buscaTimeout) clearTimeout(buscaTimeout);
            buscaTimeout = setTimeout(function() {
                loadUsuarios(true);
            }, 500);
        });

        // Exportação Excel da base completa (considerando o filtro atual, se houver)
        $("#btnExportUsuarios").on("click", function() {
            var q = encodeURIComponent(usuariosBusca || '');
            window.open("staff/export_usuarios_excel.php?busca=" + q, "_blank");
        });
    });
</script>

<!-- CAD USUÁRIO -->
<div class="modal fade" id="new_registro" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content cnf-usu-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-user-plus"></i> Cadastro de Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body cnf-usu-form st-form">
                <p class="st-form-hint">Preencha os dados do novo usuário. Campos com cascata (UF → município → contrato) atualizam automaticamente.</p>
                <div class="st-form-section cnf-usu-form-section">
                    <h6 class="st-form-section-title cnf-usu-form-section-title">Dados pessoais</h6>
                    <div class="st-form-grid cnf-usu-form-grid">
                <div class="st-field input-container">
                    <label class="st-label" for="nome">Nome <span class="st-required">*</span></label>
                    <input id="nome" class="input" type="text" pattern=".+" required />
                </div>
                <div class="st-field input-container">
                    <label class="st-label" for="sobrenome">Sobrenome <span class="st-required">*</span></label>
                    <input id="sobrenome" class="input" type="text" pattern=".+" required />
                </div>
                <div class="st-field input-container">
                    <label class="st-label" for="email">E-mail <span class="st-required">*</span></label>
                    <input id="email" class="input" type="email" pattern=".+" required />
                </div>
                <div class="st-field input-container">
                    <label class="st-label" for="matricula">Login <span class="st-required">*</span></label>
                    <input id="matricula" class="input" type="text" pattern=".+" onkeyup="minuscula(this)" required />
                </div>
                    </div>
                </div>
                <?php
                $qry = '';
                if ($_SESSION['dados']['nivel_id'] >= 2) {
                    $qry = " WHERE id_nivel>=5";
                }
                if ($_SESSION['dados']['nivel_id'] == 1) {
                    $qry = " WHERE id_nivel>=2";
                }
                $sql = "SELECT id_nivel, nome_nivel, icon from tbl_nivel $qry order by id_nivel asc";
                //echo "<br>".$sql;
                ?>
                <div class="st-form-section cnf-usu-form-section">
                    <h6 class="st-form-section-title cnf-usu-form-section-title">Perfil e localização</h6>
                    <div class="st-form-grid cnf-usu-form-grid">
                <div class="st-field input-container">
                    <label class="st-label" for="nivel">Nível <span class="st-required">*</span></label>
                    <select name="nivel" id="nivel">
                            <option value="">Selecione...</option>
                            <?php

                            $stmt = $PDO->prepare($sql);
                            $result = $stmt->execute();
                            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            for ($x = 0; $x < count($dados); $x++) {
                                echo '<option value="' . $dados[$x]['id_nivel'] . '">' . $dados[$x]['icon'] . ' ' . $dados[$x]['nome_nivel'] . '</option>';
                            }
                            ?>
                        </select>
                </div>
                <div class="st-field input-container">
                    <label class="st-label" for="uf">UF <span class="st-required">*</span></label>
                    <select name="uf" id="uf">
                            <option value="">Selecione...</option>
                            <?php
                            $qryUf = '';
                            if (($infoUser['nivel_id'] ?? 0) > 1) {
                                $qryUf = " and id_estado='" . $infoUser['uf_id'] . "'";
                            }
                            $sql = "SELECT id_estado, nome_estado, uf from tbl_estado where id_estado<>'' $qryUf order by nome_estado";
                            //echo "<br>".$sql;
                            $stmt = $PDO->prepare($sql);
                            $result = $stmt->execute();
                            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            for ($x = 0; $x < count($dados); $x++) {
                                echo '<option value="' . $dados[$x]['id_estado'] . '">' . $dados[$x]['nome_estado'] . ' - ' . $dados[$x]['uf'] . '</option>';
                            }
                            ?>
                        </select>
                </div>

                <div class="st-field input-container">
                    <label class="st-label" for="municipio">Município <span class="st-required">*</span></label>
                    <select name="municipio" id="municipio">
                            <option value="">Selecione...</option>
                        </select>
                </div>

                <div class="st-field input-container">
                    <label class="st-label" for="contrato">Contrato <span class="st-required">*</span></label>
                    <select name="contrato" id="contrato">
                            <option value="">Selecione...</option>
                        </select>
                </div>

                <div class="st-field input-container">
                    <label class="st-label" for="empresa">Empresa</label>
                    <select name="empresa" id="empresa">
                            <option value="0">Selecione...</option>
                        </select>
                </div>

                <div class="st-field input-container">
                    <label class="st-label" for="regional">Regional</label>
                    <select name="regional" id="regional">
                            <option value="0">Selecione...</option>
                        </select>
                </div>

                <div class="st-field input-container">
                    <label class="st-label" for="agencia">Agência</label>
                    <select name="agencia" id="agencia">
                            <option value="0">Selecione...</option>
                        </select>
                </div>

                <div id="div_fila" class="st-field input-container" style="display: none;">
                    <label class="st-label" for="fila">Fila</label>
                    <select name="fila" id="fila">
                            <option value="0">Selecione...</option>
                        </select>
                </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer cnf-usu-modal-footer">
                <div id="save_feed" class="cnf-usu-feed"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                <button type="button" id="save" class="btn btn-solvetask"><i class="fas fa-save"></i> Salvar</button>
            </div>

        </div>
    </div>
</div>

<!-- IMPORT DE USUÁRIOS -->
<div class="modal fade" id="new_import" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content cnf-usu-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-file-import"></i> Importação em massa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body cnf-usu-form st-form">
                <p class="st-form-hint">Selecione o contrato, empresa e UF antes de enviar a planilha (.xlsx).</p>
                <div class="st-form-grid cnf-usu-form-grid">
                <div class="st-field input-container">
                        <?php
                        $sql = "SELECT id_contrato, nome_contrato, uf, ativo from tbl_contrato where ativo=1 order by nome_contrato";
                        //echo "<br>".$sql;
                        $stmt = $PDO->prepare($sql);
                        $result = $stmt->execute();
                        ?>
                    <label class="st-label" for="contrato_import">Contrato <span class="st-required">*</span></label>
                    <select name="contrato_import" id="contrato_import">
                            <option value="">Selecione...</option>
                            <?php
                            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            for ($x = 0; $x < count($dados); $x++) {
                                echo '<option value="' . $dados[$x]['id_contrato'] . '">' . $dados[$x]['nome_contrato'] . ' - ' . $dados[$x]['uf'] . '</option>';
                            }
                            ?>
                        </select>
                </div>

                <div class="st-field input-container">
                    <label class="st-label" for="empresa_import">Empresa</label>
                    <select name="empresa_import" id="empresa_import">
                            <option value="0">Selecione...</option>
                        </select>
                </div>
                <div class="st-field input-container">
                    <label class="st-label" for="uf_import">UF <span class="st-required">*</span></label>
                    <select name="uf_import" id="uf_import">
                            <option value="">Selecione...</option>
                            <?php
                            $qryUf = '';
                            if (($infoUser['nivel_id'] ?? 0) >= 1) {
                                $qryUf = " and id_estado='" . $infoUser['uf_id'] . "'";
                            }
                            $sql = "SELECT id_estado, nome_estado, uf from tbl_estado where id_estado<>'' $qryUf order by nome_estado";
                            //echo "<br>".$sql;
                            $stmt = $PDO->prepare($sql);
                            $result = $stmt->execute();
                            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            for ($x = 0; $x < count($dados); $x++) {
                                echo '<option value="' . $dados[$x]['uf'] . '">' . $dados[$x]['nome_estado'] . ' - ' . $dados[$x]['uf'] . '</option>';
                            }
                            ?>
                        </select>
                </div>
                <div class="st-field input-container">
                    <label class="st-label" for="file_import">Planilha (.xlsx) <span class="st-required">*</span></label>
                    <input type="file" id="file_import" name="file_import" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                </div>
                </div>
            </div>
            <div class="modal-footer cnf-usu-modal-footer">
                <div id="save_feed_import" class="cnf-usu-feed"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                <button type="button" id="save_import" class="btn btn-solvetask"><i class="fas fa-upload"></i> Importar</button>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $("#downBase").click(function() {
            var valFileDownloadPath = '<?= $urlDownload; ?>';

            window.open(valFileDownloadPath, '_blank');
        });

        $("#save").click(function() {
            //console.log('Clicou save');

            var nome = $('#nome').val();
            var sobrenome = $('#sobrenome').val();
            var matricula = $('#matricula').val();
            var uf = $('#uf').val();
            var municipio = $('#municipio').val();
            var nivel = $('#nivel').val();
            var contrato = $('#contrato').val();
            var regional = $('#regional').val();
            var empresa = $('#empresa').val();
            var agencia = $('#agencia').val();
            var email = $('#email').val();
            var fila = $('#fila').val();


            saveUser(nome, sobrenome, email, matricula, uf, municipio, nivel, contrato, empresa, regional,
                agencia,
                fila);
        });

        var form;
        $('#file_import').change(function(event) {
            form = new FormData();
            form.append('file_import', event.target.files[0]); // para apenas 1 arquivo
            let token = new Date();
            token = JSON.stringify(token);
            token = btoa(token);
            //console.log( token );
            form.append('token', token);
            //console.log(form);

        });

        $("#save_import").click(function() {
            $('#save_feed_import').html('<div id="load"><center><img src="../view/img/loading.gif" width="150" alt="Carregando..."><br>Aguarde, pode levar algum tempo para importar todos os dados...</center></div>');

            form.append('contrato', $('#contrato_import').val());
            form.append('empresa', $('#empresa_import').val());
            form.append('uf', $('#uf_import').val());
            //console.log(form);


            $.ajax({
                url: 'staff/import_users.php', // Url do lado server que vai receber o arquivo
                data: form,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function(data) {
                    $('#save_feed_import').html(data);
                }
            });


        });

        $("#nivel").change(function() {
            var nivel = $('#nivel').val();
            if (nivel == 4) {
                $('#div_fila').show();
            } else {
                $('#div_fila').hide();
            }

        });


        function saveUser(nome, sobrenome, email, matricula, uf, municipio, nivel, contrato, empresa, regional,
            agencia,
            fila) {


            $("#save_feed").html(
                '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
            );
            if (nome == '') {
                menAlert('Nome');
            } else
            if (sobrenome == '') {
                menAlert('Sobrenome');
            } else
            if (matricula == '') {
                menAlert('Login');
            } else
            if (nivel == '') {
                menAlert('Nível');
            } else
            if (email == '') {
                menAlert('E-mail');
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

            if (nivel == 4 && fila == '') {
                menAlert('Fila');
            } else

            {
                $.post("staff/save_user.php", {
                        nome,
                        sobrenome,
                        email,
                        matricula,
                        uf,
                        municipio,
                        nivel,
                        contrato,
                        empresa,
                        regional,
                        agencia,
                        fila
                    },
                    function(valor) {
                        //console.log(valor);
                        $("#save_feed").html(valor);
                    });
            }

        }


        function menAlert(campo) {
            $("#save_feed").html('<div style="color: red">O campo <strong>' + campo +
                '</strong> deve ser preenchido corretamente</div>');
        }

        $("#contrato").change(function() {
            var contrato = $('#contrato').val();
            var nivel = $('#nivel').val();
            loadRegional(contrato);
            loadEmpresa(contrato);
            loadDemandas(contrato);
            if (nivel == 4) {
                loadFila(contrato);
            }

        });

        $("#contrato_import").change(function() {
            var contrato = $('#contrato_import').val();
            loadEmpresaImport(contrato);

        });

        $("#uf").change(function() {
            var uf = $('#uf').val();
            loadUf(uf);
        });

        $("#regional").change(function() {
            var regional = $('#regional').val();
            loadAgencia(regional);
        });

        $("#nome").keyup(function() {
            $("#nome").val(capitalize($("#nome").val()))
        });

        $("#sobrenome").keyup(function() {
            $("#sobrenome").val(capitalize($("#sobrenome").val()))
        });


        function loadRegional(contrato) {
            $("#regional").html('Carregando...');
            //console.log("Contrato escolhido: "+contrato);
            $.post("staff/load_regional.php", {
                    contrato
                },
                function(valor) {
                    $("#regional").html(valor);
                });
        }

        function loadEmpresa(contrato) {
            $("#empresa").html('Carregando...');
            //console.log("Contrato escolhido: "+contrato);
            $.post("staff/load_empresa.php", {
                    contrato
                },
                function(valor) {
                    $("#empresa").html(valor);
                });
        }

        function loadEmpresaImport(contrato) {
            $("#empresa_import").html('Carregando...');
            //console.log("Contrato escolhido: "+contrato);
            $.post("staff/load_empresa.php", {
                    contrato
                },
                function(valor) {
                    $("#empresa_import").html(valor);
                });
        }

        function loadAgencia(regional) {
            $("#agencia").html('Carregando...');
            //console.log("Regional escolhido: "+regional);
            $.post("staff/load_agencia.php", {
                    regional
                },
                function(valor) {
                    $("#agencia").html(valor);
                });
        }

        function loadUf(uf) {
            $("#contrato").html('Carregando...');
            $("#municipio").html('Carregando...');
            //console.log("Contrato escolhido: "+uf);
            $.post("staff/load_contrato.php", {
                    uf
                },
                function(valor) {
                    $("#contrato").html(valor);
                });

            $.post("staff/load_municipio.php", {
                    uf
                },
                function(valor) {
                    $("#municipio").html(valor);
                });
        }

        function loadDemandas(contrato) {
            $("#form").html('<option value="">Carregando Demandas...</option>');
            $.post("staff/import_load_form.php", {
                    contrato: contrato
                },
                function(valor) {
                    $("#form").html(valor);
                });

        }

        function loadFila(contrato) {
            $("#fila").html('<option value="">Carregando Filas...</option>');
            $.post("staff/load_fila_user.php", {
                    contrato: contrato
                },
                function(valor) {
                    $("#fila").html(valor);
                });

        }



    });
</script>
<div id="passNew"></div>
<div class="modal fade" id="modal_alt" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content cnf-usu-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><i class="fas fa-user-edit"></i> Alteração de cadastro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="div_alt_cad">



            </div>
            <!--
            <div class="modal-footer">
                <div id="feed_alt_<?php echo $dados[$x]['id_user']; ?>"></div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                        class="fas fa-times-circle"></i></button>
                <button type="button" id="save_img_<?php echo $dados[$x]['id_user']; ?>" class="btn btn-info"><i
                        class="fas fa-user-circle" title="Reset de Imagem de Perfil"></i></button>
                <button type="button" id="save_alt_<?php echo $dados[$x]['id_user']; ?>" class="btn btn-success"><i
                        class="fas fa-save"></i></button>
            </div>
            -->


        </div>
    </div>

</div>
<script>
    function loadAlt(id) {
        $("#div_alt_cad").html(
            '<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>'
        );
        $.post("staff/alt_cad_usu.php", {
                id
            },
            function(valor) {
                $("#div_alt_cad").html(valor);
            });
    }
</script>

<div class="modal fade" id="modal_filas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content cnf-usu-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><i class="fas fa-list"></i> Configuração de filas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="div_alt_fil">

            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modal_mail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content cnf-usu-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><i class="fas fa-envelope"></i> E-mail de cadastro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close_mail"></button>
            </div>
            <div class="modal-body" id="div_alt_mail">

            </div>
        </div>
    </div>

</div>
<script>
    function loadFilas(id) {
        $("#div_alt_fil").html(
            '<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>'
        );
        $.post("staff/alt_config_filas.php", {
                id
            },
            function(valor) {
                $("#div_alt_fil").html(valor);
            });
    }

    function loadMail(id) {
        $("#div_alt_mail").html(
            '<center><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></center>'
        );
        $.post("staff/alt_config_mail.php", {
                id
            },
            function(valor) {
                $("#div_alt_mail").html(valor);
            });
    }
</script>
<script type="text/javascript" src="js/load.js"></script>