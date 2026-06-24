<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/../cnf/_cnf_ui.php';
include('cnf/rotina_pendencia.php');

if ($infoUser['nivel_id'] == 4) {
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Indisponivel');
}

$scoreUserId = (int)($_POST['user'] ?? $_SESSION['dados']['id_user']);
if ($scoreUserId <= 0) {
    $scoreUserId = (int)$_SESSION['dados']['id_user'];
}
if ((int)$_SESSION['dados']['nivel_id'] === 4 && $scoreUserId !== (int)$_SESSION['dados']['id_user']) {
    $scoreUserId = (int)$_SESSION['dados']['id_user'];
}

$stmtPerfil = $PDO->prepare(
    'SELECT a.id_user, CONCAT(a.nome, \' \', a.sobrenome) AS nome_completo,'
    .' c.nome_contrato, d.nome_municipio, d.uf, e.nome_regional, f.nome_agencia,'
    .' g.nome_nivel, DATE_FORMAT(a.data_cad, \'%d/%m/%Y\') AS data_cad, b.img,'
    .' (SELECT nome_fila FROM tbl_config_fila WHERE id_fila = a.fila_id LIMIT 1) AS nome_fila,'
    .' a.fila_id'
    .' FROM tbl_user a'
    .' INNER JOIN tbl_user_img_perfil b ON a.id_user = b.user_id'
    .' INNER JOIN tbl_contrato c ON a.contrato_id = c.id_contrato'
    .' INNER JOIN tbl_municipio d ON a.municipio_id = d.id_municipio'
    .' INNER JOIN tbl_regional e ON a.regional_id = e.id_regional'
    .' INNER JOIN tbl_agencia f ON a.agencia_id = f.id_agencia'
    .' INNER JOIN tbl_nivel g ON a.nivel_id = g.id_nivel'
    .' WHERE a.id_user = ? LIMIT 1'
);
$stmtPerfil->execute([$scoreUserId]);
$infoPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC) ?: [];

$stmtStatus = $PDO->prepare(
    'SELECT acao, DATE_FORMAT(data_hora, \'%H:%i\') AS hora_status'
    .' FROM tbl_log_atendimento'
    .' WHERE user_id = ? AND data_hora >= CURDATE() AND data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)'
    .' ORDER BY data_hora DESC LIMIT 1'
);
$stmtStatus->execute([$scoreUserId]);
$statusHoje = $stmtStatus->fetch(PDO::FETCH_ASSOC) ?: [];
$acaoAtual = (string)($statusHoje['acao'] ?? 'Sem registro');
$horaStatus = (string)($statusHoje['hora_status'] ?? '');

$statusClass = 'st-score-status--neutral';
if (in_array($acaoAtual, ['Disponivel', 'Login'], true)) {
    $statusClass = 'st-score-status--ok';
} elseif (in_array($acaoAtual, ['Tratamento', 'Pos'], true)) {
    $statusClass = 'st-score-status--busy';
} elseif (in_array($acaoAtual, ['Indisponivel', 'Pausa', 'Logout'], true)) {
    $statusClass = 'st-score-status--off';
}
?>

<style>
.st-score-shell {
    display: grid;
    grid-template-columns: minmax(240px, 280px) minmax(0, 1fr);
    gap: 16px;
    align-items: start;
}

.st-score-profile {
    background: linear-gradient(160deg, #fafafa 0%, #f0f0f0 100%);
    border: 1px solid #e4e4e4;
    border-radius: 12px;
    padding: 18px 16px;
}

.st-score-profile__avatar {
    text-align: center;
    margin-bottom: 14px;
}

.st-score-profile__avatar img {
    width: 96px;
    height: 96px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.st-score-profile__name {
    text-align: center;
    font-size: 15px;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
    line-height: 1.3;
}

.st-score-profile__status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 14px;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    background: #fff;
    border: 1px solid #e8e8e8;
}

.st-score-status-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
}

.st-score-status--ok .st-score-status-dot { background: #28a745; }
.st-score-status--busy .st-score-status-dot { background: #f0ad4e; }
.st-score-status--off .st-score-status-dot { background: #adb5bd; }
.st-score-status--neutral .st-score-status-dot { background: #6c757d; }

.st-score-profile dl {
    margin: 0;
}

.st-score-profile dt {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #B7202F;
    margin-top: 10px;
}

.st-score-profile dd {
    margin: 2px 0 0;
    font-size: 12px;
    color: #444;
    line-height: 1.35;
}

#dados_user {
    min-height: 420px;
}

@media (max-width: 992px) {
    .st-score-shell {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="text/javascript">
    function loadDadosScore(user, referencia) {
        var div = '#dados_user';
        $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="120"></div>');
        $.post('staff/load_dados_score.php', { user: user, referencia: referencia }, function (valor) {
            $(div).html(valor);
        });
    }
</script>

<?php
st_page_open('Meu Score', 'Desempenho individual, produtividade e comparativos da sua fila');
st_page_header_close();
st_filter_bar_open();
cnf_field_input('referencia', 'Referência', ['type' => 'month', 'value' => date('Y-m'), 'required' => true]);
echo '<button type="button" id="btn_filter" class="btn btn-solvetask btn-sm"><i class="fas fa-filter"></i> Filtrar</button>';
st_filter_bar_close();
?>

<div class="st-score-shell">
    <aside class="st-score-profile">
        <div class="st-score-profile__avatar">
            <img src="<?= htmlspecialchars((string)($infoPerfil['img'] ?? 'img/perfil.png')) ?>" class="rounded-circle" alt="Foto do perfil">
        </div>
        <div class="st-score-profile__name"><?= htmlspecialchars(ucwords(strtolower((string)($infoPerfil['nome_completo'] ?? '')))) ?></div>
        <div class="st-score-profile__status <?= $statusClass ?>">
            <span class="st-score-status-dot" aria-hidden="true"></span>
            <span><?= htmlspecialchars($acaoAtual) ?><?= $horaStatus !== '' ? ' · ' . htmlspecialchars($horaStatus) : '' ?></span>
        </div>
        <dl>
            <dt>Fila</dt>
            <dd><?= htmlspecialchars(ucwords((string)($infoPerfil['nome_fila'] ?? '—'))) ?></dd>
            <dt>Contrato</dt>
            <dd><?= htmlspecialchars(ucwords((string)($infoPerfil['nome_contrato'] ?? '—'))) ?></dd>
            <dt>Regional / Agência</dt>
            <dd><?= htmlspecialchars(ucwords((string)($infoPerfil['nome_regional'] ?? '—'))) ?> · <?= htmlspecialchars(ucwords((string)($infoPerfil['nome_agencia'] ?? '—'))) ?></dd>
            <dt>Localidade</dt>
            <dd><?= htmlspecialchars(ucwords((string)($infoPerfil['nome_municipio'] ?? '—'))) ?> - <?= htmlspecialchars((string)($infoPerfil['uf'] ?? '')) ?></dd>
            <dt>Perfil</dt>
            <dd><?= htmlspecialchars(ucwords((string)($infoPerfil['nome_nivel'] ?? '—'))) ?></dd>
            <dt>Cadastro</dt>
            <dd><?= htmlspecialchars((string)($infoPerfil['data_cad'] ?? '—')) ?></dd>
        </dl>
    </aside>
    <div id="dados_user"></div>
</div>

<script>
    loadDadosScore(<?= (int)$scoreUserId ?>, '<?= date('Y-m') ?>');

    $(document).ready(function () {
        $('#btn_filter').click(function () {
            loadDadosScore(<?= (int)$scoreUserId ?>, $('#referencia').val());
        });
    });
</script>

<?php st_page_close(); ?>
<script type="text/javascript" src="js/load.js"></script>
