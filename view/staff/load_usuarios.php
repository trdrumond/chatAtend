<?php
require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/usuario_list_order.php';

/** @var array<string, mixed> $infoUser */
/** @var array<string, mixed> $infoUserConfig */
/** @var string|int $cad_cnf */

if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = ['nivel_id' => 99];
}
if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}
if (!isset($cad_cnf)) {
    $cad_cnf = '';
}

header('Content-Type: application/json; charset=utf-8');

// Parâmetros de paginação e busca
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$limit  = isset($_POST['limit']) ? (int)$_POST['limit'] : 300;
if ($limit <= 0 || $limit > 1000) {
    $limit = 300;
}

$busca = isset($_POST['busca']) ? trim($_POST['busca']) : '';

// Filtro por contrato conforme já usado em cad-usu.php
$qryContrato = '';
$cttParams = [];
if ($infoUser['nivel_id'] > 1) {
    $cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''), 'ctt');
    $qryContrato = " AND b.id_contrato IN (" . $cttIn['ph'] . ")";
    $cttParams = $cttIn['params'];
}

// Campos base
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
    " FROM tbl_user a " .
    " INNER JOIN tbl_contrato b ON a.contrato_id = b.id_contrato " .
    " INNER JOIN tbl_municipio c ON a.municipio_id = c.id_municipio " .
    " LEFT JOIN tbl_agencia d ON a.agencia_id = d.id_agencia " .
    " INNER JOIN tbl_estado e ON a.uf_id = e.id_estado " .
    " INNER JOIN tbl_nivel g ON a.nivel_id = g.id_nivel " .
    " INNER JOIN tbl_regional f ON a.regional_id = f.id_regional " .
    " INNER JOIN tbl_empresa h ON a.empresa_id = h.id_empresa " .
    " WHERE a.id_user > 1 " .
    // Ativos ou inativos com até 90 dias
    " AND (a.ativo = 1 OR (a.ativo = 0 AND a.data_inativo >= DATE_SUB(CURDATE(), INTERVAL 90 DAY))) " .
    $qryContrato;

// Filtro de busca em todos os campos exibidos
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
        " OR (a.agencia_id = 0 AND 'SEM AGÊNCIA (id=0)' LIKE :q) " .
        " OR h.nome_empresa LIKE :q " .
        " OR g.nome_nivel LIKE :q " .
        " OR (SELECT nome_fila FROM tbl_config_fila WHERE id_fila = a.fila_id) LIKE :q " .
        " OR DATE_FORMAT(a.data_cad, '%d/%m/%Y') LIKE :q " .
        " OR DATE_FORMAT(a.data_inativo, '%d/%m/%Y') LIKE :q " .
        " ) ";
    $paramsBusca[':q'] = '%' . $busca . '%';
}

// Total
$sqlCount = "SELECT COUNT(*) AS total " . $fromBase . $whereBusca;
$stmt = $PDO->prepare($sqlCount);
foreach (array_merge($cttParams, $paramsBusca) as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$rowCount = $stmt->fetch(PDO::FETCH_ASSOC);
$total = $rowCount ? (int)$rowCount['total'] : 0;

// Dados paginados (ativos por hierarquia de perfil, depois alfabético; inativos por último)
$sqlDados =
    "SELECT " . $camposBase .
    $fromBase .
    $whereBusca .
    " ORDER BY " . stUsuarioListOrderSql('a') . " " .
    " LIMIT :offset, :limit";

$stmt = $PDO->prepare($sqlDados);
foreach (array_merge($cttParams, $paramsBusca) as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows = [];

// Permissões para ícones (replicando lógica já usada no PHP da tela)
$permiteMail = ($infoUser['nivel_id'] < 1) ? 1 : 0;
$cadCnf = ($cad_cnf == 1) ? 1 : 0;

foreach ($dados as $d) {
    $agenciaIdRow = (int)$d['agencia_id'];
    $nomeAgenciaRow = $d['nome_agencia'];
    if ($agenciaIdRow <= 0 || $nomeAgenciaRow === null || trim((string)$nomeAgenciaRow) === '') {
        $nomeAgenciaRow = 'SEM AGÊNCIA (id=0)';
    }
    $rows[] = [
        'id_user'       => (int)$d['id_user'],
        'nome_usuario'  => $d['nome_usuario'],
        'nome'          => $d['nome'],
        'sobrenome'     => $d['sobrenome'],
        'email'         => $d['email'],
        'nome_completo' => $d['nome_completo'],
        'contrato_id'   => (int)$d['contrato_id'],
        'flag_mail'     => (int)$d['flag_mail'],
        'nome_contrato' => $d['nome_contrato'],
        'nome_municipio' => $d['nome_municipio'],
        'uf'            => $d['uf'],
        'agencia_id'    => $agenciaIdRow,
        'nome_agencia'  => $nomeAgenciaRow,
        'nivel_id'      => (int)$d['nivel_id'],
        'nome_regional' => $d['nome_regional'],
        'nome_empresa'  => $d['nome_empresa'],
        'nome_nivel'    => $d['nome_nivel'],
        'ativo'         => (int)$d['ativo_flag'],
        'data_cad'      => $d['data_cad'],
        'data_inativo'  => $d['data_inativo'],
        'nome_fila'     => $d['nome_fila'],
        'permite_mail'  => $permiteMail,
        'cad_cnf'       => $cadCnf,
    ];
}

echo json_encode([
    'rows'   => $rows,
    'total'  => $total,
    'hasMore' => ($offset + $limit) < $total,
]);
