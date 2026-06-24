<?php
require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/usuario_list_order.php';

/** @var array<string, mixed> $infoUser */
/** @var array<string, mixed> $infoUserConfig */

if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = ['nivel_id' => 99];
}
if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}

// Forçamos download de CSV (Excel abre normalmente)
$filename = "usuarios_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Cabeçalho
fputcsv($output, [
    'NOME COMPLETO',
    'LOGIN',
    'E-MAIL',
    'LOCAL',
    'UF',
    'EMPRESA',
    'NÍVEL',
    'FILA',
    'DATA CAD',
    'DATA INAT',
    'SITUAÇÃO',
], ';');

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Filtro por contrato
$qryContrato = '';
if ($infoUser['nivel_id'] > 1) {
    $qryContrato = " AND b.id_contrato IN (" . $infoUserConfig['contrato_id'] . ")";
}

$camposBase =
    " a.id_user, a.nome_usuario, a.nome, a.sobrenome, a.email, " .
    " CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo, a.contrato_id, a.flag_mail," .
    " b.nome_contrato, a.municipio_id, c.nome_municipio, c.uf, a.agencia_id, d.nome_agencia, " .
    " a.nivel_id, a.regional_id, f.nome_regional, a.empresa_id, h.nome_empresa," .
    " g.nome_nivel, a.ativo, " .
    " DATE_FORMAT(a.data_cad, '%d/%m/%Y') AS data_cad, " .
    " DATE_FORMAT(a.data_inativo, '%d/%m/%Y') AS data_inativo, " .
    " a.ativo AS ativo_flag, a.fila_id, " .
    " (SELECT nome_fila FROM tbl_config_fila WHERE id_fila = a.fila_id) AS nome_fila ";

$fromBase =
    " FROM tbl_user a, tbl_contrato b, tbl_municipio c, tbl_agencia d, " .
    " tbl_estado e, tbl_nivel g, tbl_regional f, tbl_empresa h " .
    " WHERE a.contrato_id = b.id_contrato " .
    " AND a.municipio_id = c.id_municipio " .
    " AND a.agencia_id = d.id_agencia " .
    " AND a.uf_id = e.id_estado " .
    " AND a.regional_id = f.id_regional " .
    " AND a.empresa_id = h.id_empresa " .
    " AND a.nivel_id = g.id_nivel " .
    " AND a.id_user > 1 " .
    // Ativos ou inativos com até 90 dias
    " AND (a.ativo = 1 OR (a.ativo = 0 AND a.data_inativo >= DATE_SUB(CURDATE(), INTERVAL 90 DAY))) " .
    $qryContrato;

$whereBusca = '';
$paramsBusca = [];
if ($busca !== '') {
    $whereBusca = " AND ( " .
        " a.nome_usuario LIKE :q " .
        " OR a.nome LIKE :q " .
        " OR a.sobrenome LIKE :q " .
        " OR CONCAT(a.nome, ' ', a.sobrenome) LIKE :q " .
        " OR a.email LIKE :q " .
        " OR c.nome_municipio LIKE :q " .
        " OR c.uf LIKE :q " .
        " OR d.nome_agencia LIKE :q " .
        " OR h.nome_empresa LIKE :q " .
        " OR g.nome_nivel LIKE :q " .
        " OR (SELECT nome_fila FROM tbl_config_fila WHERE id_fila = a.fila_id) LIKE :q " .
        " OR DATE_FORMAT(a.data_cad, '%d/%m/%Y') LIKE :q " .
        " OR DATE_FORMAT(a.data_inativo, '%d/%m/%Y') LIKE :q " .
        " ) ";
    $paramsBusca[':q'] = '%' . $busca . '%';
}

$sql =
    "SELECT " . $camposBase .
    $fromBase .
    $whereBusca .
    " ORDER BY " . stUsuarioListOrderSql('a');

$stmt = $PDO->prepare($sql);
foreach ($paramsBusca as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();

while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ativoString = ((int)$d['ativo_flag'] === 1) ? 'ONLINE' : 'OFFLINE';

    // Normaliza fila e data de inativação: qualquer valor "nulo" sai em branco
    $nomeFila = '';
    if (!empty($d['nome_fila'])) {
        $filaStr = strtolower(trim((string)$d['nome_fila']));
        if ($filaStr !== 'null' && $filaStr !== 'undefined') {
            $nomeFila = $d['nome_fila'];
        }
    }

    $dataInat = '';
    if (!empty($d['data_inativo'])) {
        $di = strtolower(trim((string)$d['data_inativo']));
        if ($di !== 'null' && $di !== 'undefined' && $di !== '0000-00-00' && $di !== '00/00/0000') {
            $dataInat = $d['data_inativo'];
        }
    }

    fputcsv($output, [
        $d['nome_completo'],
        $d['nome_usuario'],
        $d['email'],
        $d['nome_agencia'],
        $d['uf'],
        $d['nome_empresa'],
        $d['nome_nivel'],
        $nomeFila,
        $d['data_cad'],
        $dataInat,
        $ativoString,
    ], ';');
}

fclose($output);
exit;
