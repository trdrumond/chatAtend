<?php
include("conn.php");
include("../cnf/func.php");

header('Content-Type: application/json; charset=utf-8');

$apiVersion = 'solvetask-cadastro-v1-2026-04-17';
header('X-SolveTask-API-Version: ' . $apiVersion);

function responder($statusCode, $payload)
{
    if (function_exists('http_response_code')) {
        http_response_code($statusCode);
    } else {
        header('X-PHP-Response-Code: ' . (int) $statusCode, true, (int) $statusCode);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, array('ok' => false, 'mensagem' => 'Método não permitido. Use POST com JSON.'));
}

$data = array();
$contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower((string) $_SERVER['CONTENT_TYPE']) : '';

if (strpos($contentType, 'application/json') !== false) {
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        responder(400, array('ok' => false, 'mensagem' => 'Body vazio. Envie um JSON válido.'));
    }
    $data = json_decode($rawBody, true);
    if (!is_array($data)) {
        responder(400, array('ok' => false, 'mensagem' => 'JSON inválido.'));
    }
} else {
    if (!empty($_POST) && is_array($_POST)) {
        $data = $_POST;
    } else {
        $rawBody = file_get_contents('php://input');
        $fallback = array();
        if (is_string($rawBody) && trim($rawBody) !== '') {
            parse_str($rawBody, $fallback);
        }
        if (!empty($fallback) && is_array($fallback)) {
            $data = $fallback;
        }
    }
    if (!is_array($data) || empty($data)) {
        responder(400, array('ok' => false, 'mensagem' => 'Body vazio. Envie JSON, multipart/form-data ou x-www-form-urlencoded.'));
    }
}

$requiredFields = array(
    'nome_usuario',
    'nome',
    'sobrenome',
    'email',
    'contrato_id',
    'empresa_id',
    'municipio_id',
    'regional_id',
    'agencia_id',
    'nivel_id',
    'uf_id',
);

$missing = array();
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    responder(422, array(
        'ok' => false,
        'mensagem' => 'Campos obrigatórios ausentes.',
        'campos' => $missing,
    ));
}

$nomeUsuario = strtolower(trim((string) $data['nome_usuario']));
$nome = ucwords(strtolower(trim((string) $data['nome'])));
$sobrenome = ucwords(strtolower(trim((string) $data['sobrenome'])));
$email = trim((string) $data['email']);

$contratoId = (int) $data['contrato_id'];
$empresaId = (int) $data['empresa_id'];
$municipioId = (int) $data['municipio_id'];
$regionalId = (int) $data['regional_id'];
$agenciaId = (int) $data['agencia_id'];
$nivelId = (int) $data['nivel_id'];
$ufId = (int) $data['uf_id'];
$filaId = isset($data['fila_id']) ? trim((string) $data['fila_id']) : '0';

if (
    $contratoId <= 0 || $empresaId <= 0 || $municipioId <= 0 ||
    $regionalId <= 0 || $agenciaId <= 0 || $ufId <= 0 || $nivelId < 0
) {
    responder(422, array('ok' => false, 'mensagem' => 'IDs inválidos no payload.'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(422, array('ok' => false, 'mensagem' => 'E-mail inválido.'));
}

if ($nomeUsuario === '') {
    responder(422, array('ok' => false, 'mensagem' => 'Login inválido.'));
}

$pass = newPass();
$senhaHash = generateHash($pass);
$token = geraToken($nomeUsuario);

try {
    $stmt = $PDO->prepare("SELECT id_user FROM tbl_user WHERE nome_usuario = :nome_usuario LIMIT 1");
    $stmt->bindValue(':nome_usuario', $nomeUsuario);
    $stmt->execute();
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        responder(409, array('ok' => false, 'mensagem' => 'Já existe um usuário com este login.'));
    }

    $PDO->beginTransaction();

    $sqlInsertUser = "INSERT INTO tbl_user
        (nome_usuario, senha_usuario, nome, sobrenome, email, contrato_id, empresa_id, municipio_id, regional_id, agencia_id, fila_id, uf_id, token, nivel_id, data_update)
        VALUES
        (:nome_usuario, :senha_usuario, :nome, :sobrenome, :email, :contrato_id, :empresa_id, :municipio_id, :regional_id, :agencia_id, :fila_id, :uf_id, :token, :nivel_id, CURDATE())";

    $stmt = $PDO->prepare($sqlInsertUser);
    $stmt->bindValue(':nome_usuario', $nomeUsuario);
    $stmt->bindValue(':senha_usuario', $senhaHash);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':sobrenome', $sobrenome);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':contrato_id', $contratoId, PDO::PARAM_INT);
    $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
    $stmt->bindValue(':municipio_id', $municipioId, PDO::PARAM_INT);
    $stmt->bindValue(':regional_id', $regionalId, PDO::PARAM_INT);
    $stmt->bindValue(':agencia_id', $agenciaId, PDO::PARAM_INT);
    $stmt->bindValue(':fila_id', $filaId);
    $stmt->bindValue(':uf_id', $ufId, PDO::PARAM_INT);
    $stmt->bindValue(':token', $token);
    $stmt->bindValue(':nivel_id', $nivelId, PDO::PARAM_INT);
    $stmt->execute();

    $userId = (int) $PDO->lastInsertId();
    if ($userId <= 0) {
        throw new Exception('Não foi possível identificar o usuário criado.');
    }

    $sqlInsertPass = "INSERT INTO tbl_user_pass (user_id, date_refresh, pass) VALUES (:user_id, CURDATE(), :pass)";
    $stmt = $PDO->prepare($sqlInsertPass);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':pass', $senhaHash);
    $stmt->execute();

    $sqlDadosEmail = "SELECT
            c.nome_contrato AS contrato,
            m.nome_municipio AS municipio,
            r.nome_regional AS regional,
            a.nome_agencia AS agencia
        FROM tbl_user u
        LEFT JOIN tbl_contrato c ON c.id_contrato = u.contrato_id
        LEFT JOIN tbl_municipio m ON m.id_municipio = u.municipio_id
        LEFT JOIN tbl_regional r ON r.id_regional = u.regional_id
        LEFT JOIN tbl_agencia a ON a.id_agencia = u.agencia_id
        WHERE u.id_user = :id_user
        LIMIT 1";
    $stmt = $PDO->prepare($sqlDadosEmail);
    $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $dadosEmail = $stmt->fetch(PDO::FETCH_ASSOC);

    $PDO->commit();

    $nome = trim($nome . ' ' . $sobrenome);
    $login = $nomeUsuario;
    $contrato = isset($dadosEmail['contrato']) ? (string) $dadosEmail['contrato'] : '';
    $municipio = isset($dadosEmail['municipio']) ? (string) $dadosEmail['municipio'] : '';
    $regional = isset($dadosEmail['regional']) ? (string) $dadosEmail['regional'] : '';
    $agencia = isset($dadosEmail['agencia']) ? (string) $dadosEmail['agencia'] : '';
    $enviado = false;
    $erroEmail = null;

    $emailScript = dirname(__FILE__) . "/enviar_email_cadastro.php";
    if (!is_file($emailScript)) {
        $erroEmail = 'Arquivo de envio não encontrado: ' . basename($emailScript);
    } else {
        include $emailScript;
        $enviado = ($enviado === true);
        if ($erroEmail === null && !$enviado) {
            $erroEmail = 'Falha ao enviar e-mail de confirmação.';
        }
    }

    if ($enviado) {
        $stmt = $PDO->prepare("UPDATE tbl_user SET flag_mail = 1 WHERE id_user = :id_user");
        $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    $mensagem = $enviado
        ? 'Cadastro concluído com sucesso. E-mail de confirmação enviado.'
        : 'Cadastro concluído com sucesso. O e-mail de confirmação não foi enviado; verifique erro_email.';

    $response = array(
        'ok' => true,
        'cadastro_ok' => true,
        'mensagem' => $mensagem,
        'api_version' => $apiVersion,
        'id_user' => $userId,
        'nome_usuario' => $nomeUsuario,
        'token' => $token,
        'email_enviado' => $enviado,
    );

    if ($erroEmail !== null && $erroEmail !== '') {
        $response['erro_email'] = $erroEmail;
    }

    responder(201, $response);
} catch (Exception $e) {
    if ($PDO->inTransaction()) {
        $PDO->rollBack();
    }

    responder(500, array(
        'ok' => false,
        'mensagem' => 'Erro ao cadastrar usuário.',
        'erro' => $e->getMessage(),
    ));
}
