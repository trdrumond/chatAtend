<?php

function stAccentTransliterationMap()
{
    return [
        'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ń' => 'N',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ń' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f',
        'ă' => 'a', 'ș' => 's', 'ț' => 't', 'Ă' => 'A', 'Ș' => 'S', 'Ț' => 'T',
    ];
}

//TRANSFORMA STRING EM NOME DE CAMPO
function nomeCampoInput(string $string)
{
    $nova_string = str_replace(' ', '_', strtr($string, stAccentTransliterationMap()));
    $nova_string = str_replace('?', '', $nova_string);
    $nova_string = str_replace('-', '', $nova_string);
    $nova_string = str_replace('!', '', $nova_string);
    $nova_string = str_replace('.', '_', $nova_string);
    $nova_string = str_replace('(', '', $nova_string);
    $nova_string = str_replace(')', '', $nova_string);
    $nova_string = str_replace('r$', '', $nova_string);
    $nova_string = str_replace('R$', '', $nova_string);
    $nova_string = str_replace('$', '', $nova_string);
    $nova_string = str_replace("'", '', $nova_string);
    $nova_string = str_replace(',', '', $nova_string);
    $nova_string = str_replace('/', '_', $nova_string);
    if (substr($nova_string, -1) === '_') {
        $nova_string = substr($nova_string, 0, -1);
    }

    return strtolower($nova_string);
}

function nomeCampo(string $string)
{
    $nova_string = str_replace('_', '', strtr($string, stAccentTransliterationMap()));
    $nova_string = str_replace("'", '', $nova_string);
    $nova_string = str_replace('"', '´´', $nova_string);

    if (substr($nova_string, -1) === '_') {
        $nova_string = substr($nova_string, 0, -1);
    }

    return strtolower($nova_string);
}

//HORA PARA SEGUNDOS
function time_to_sec(string $time)
{
    $hours = (int) substr($time, 0, -6);
    $minutes = (int) substr($time, -5, 2);
    $seconds = (int) substr($time, -2);

    return $hours * 3600 + $minutes * 60 + $seconds;
}

//SEGUNDOS PARA HORA
function sec_to_time(int $seconds)
{
    $hours = (int) floor($seconds / 3600);
    $minutes = (int) floor($seconds % 3600 / 60);
    $secs = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

//DEPURADOR
function depurador(array $array)
{
    echo '<pre>';
    var_dump($array);
    echo '</pre>';
}

function stReplaceNullable($val)
{
    return ($val === '' || $val === null) ? null : $val;
}

function stComColsHasUser($colsRaw, int $userId): bool
{
    if ($userId <= 0) {
        return false;
    }
    $parts = preg_split('/\s*,\s*/', str_replace("'", '', (string) $colsRaw), -1, PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $part) {
        if ((int) $part === $userId) {
            return true;
        }
    }
    return false;
}

/**
 * Extrai IDs inteiros de CSV legado ("'1','2'" ou "1,2").
 *
 * @return array<int, int>
 */
function stParseIdCsv($raw): array
{
    $parts = preg_split('/\D+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);
    $ids = array_map('intval', is_array($parts) ? $parts : []);
    $ids = array_values(array_unique(array_filter($ids, static function ($id) {
        return $id > 0;
    })));

    return $ids;
}

function stHtml($val): string
{
    return htmlspecialchars((string) ($val ?? ''), ENT_QUOTES, 'UTF-8');
}

function stContratoAllowed(array $infoUser, array $infoUserConfig, int $contratoId): bool
{
    if ((int) ($infoUser['nivel_id'] ?? 0) === 0 || $contratoId <= 0) {
        return true;
    }

    return in_array($contratoId, stParseIdCsv($infoUserConfig['contrato_id'] ?? ''), true);
}

/**
 * Placeholders para cláusula IN. Prefixo nomeado evita conflito com :q em listagens.
 *
 * @param array<int, int> $ids
 * @return array{ph: string, ids: array<int, int>, params: array<int|string, int>}
 */
function stSqlInBind(array $ids, string $namedPrefix = ''): array
{
    if ($ids === []) {
        $ids = [0];
    }
    $ids = array_values($ids);
    if ($namedPrefix === '') {
        return [
            'ph' => implode(',', array_fill(0, count($ids), '?')),
            'ids' => $ids,
            'params' => $ids,
        ];
    }
    $ph = [];
    $params = [];
    foreach ($ids as $i => $id) {
        $key = ':' . $namedPrefix . $i;
        $ph[] = $key;
        $params[$key] = $id;
    }

    return [
        'ph' => implode(',', $ph),
        'ids' => $ids,
        'params' => $params,
    ];
}

/**
 * Renderiza msg de chat vinda de POST: texto plano escapado; img validada com link opcional.
 */
function stChatRenderPostedMsg(string $msg, int $chatId = 0, ?PDO $pdo = null): string
{
    $isImgMsg = $msg !== ''
        && stripos($msg, '<script') === false
        && strpos($msg, '<img') !== false
        && preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $msg);

    if (!$isImgMsg) {
        return nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'), false);
    }

    if ($chatId > 0 && $pdo instanceof PDO) {
        $stmt = $pdo->prepare('SELECT count(chat_id) as qtd from tbl_img where chat_id=?');
        $stmt->execute([$chatId]);
        $infoImg = $stmt->fetch(PDO::FETCH_ASSOC);
        $key = (int) ($infoImg['qtd'] ?? 0) === 0 ? 1 : (int) $infoImg['qtd'];
        $msg .= '<p><a href=staff/img.php?id=' . $chatId . '&key=' . $key . ' target="_blank">Abrir imagem</a></p>';
    }

    return $msg;
}

function geraToken(string $matricula)
{
    return sha1($matricula);
}

function logAtendimento(PDO $PDO, int $userId, string $acao)
{
    $stmt = $PDO->prepare('SELECT id_user, contrato_id, agencia_id, fila_id FROM tbl_user WHERE id_user = ?');
    $stmt->execute([$userId]);
    $userDados = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userDados) {
        return;
    }

    $sql = "INSERT INTO tbl_log_atendimento (user_id, contrato_id, agencia_id, fila_id, acao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([
        $userDados['id_user'],
        $userDados['contrato_id'],
        $userDados['agencia_id'],
        $userDados['fila_id'],
        $acao,
    ]);

    $sql = "INSERT INTO tbl_log_atendimento_secondary (user_id, contrato_id, agencia_id, fila_id, acao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([
        $userDados['id_user'],
        $userDados['contrato_id'],
        $userDados['agencia_id'],
        $userDados['fila_id'],
        $acao,
    ]);
}

function newPass()
{
    $comb = 'abcdefghijklmnopqrstuvwxyz1234567890@#&';
    $pass = [];
    $combLen = strlen($comb) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $combLen);
        $pass[] = $comb[$n];
    }

    return implode('', $pass);
}

function senhaValida(string $senha)
{
    $prog = 0;
    if (preg_match('/[^a-zA-Z\d]/', $senha)) {
        $chkCaracteres = '<br><i class="fas fa-check" style="color: green"></i> Tem Caracteres Especiais';
        $prog++;
    } else {
        $chkCaracteres = '<br><i class="fas fa-times" style="color: red"></i> NÃO Tem Caracteres Especiais';
    }

    if (preg_match('/[a-z]+/', $senha)) {
        $chkLetrasMin = '<br><i class="fas fa-check" style="color: green"></i> Tem letras Minúsculas';
        $prog++;
    } else {
        $chkLetrasMin = '<br><i class="fas fa-times" style="color: red"></i> NÃO Tem letras Minúsculas';
    }

    if (preg_match('/[A-Z]+/', $senha)) {
        $chkLetrasMaius = '<br><i class="fas fa-check" style="color: green"></i> Tem letras Maiusculas';
        $prog++;
    } else {
        $chkLetrasMaius = '<br><i class="fas fa-times" style="color: red"></i> NÃO Tem letras Maiusculas';
    }

    if (preg_match('/[0-9]+/', $senha)) {
        $chkNumeros = '<br><i class="fas fa-check" style="color: green"></i> Tem Números';
        $prog++;
    } else {
        $chkNumeros = '<br><i class="fas fa-times" style="color: red"></i> NÃO Tem Números';
    }

    if (strlen($senha) > 8 && strlen($senha) < 20) {
        $chkTamanho = '<br><i class="fas fa-check" style="color: green"></i> Tem 8 caracteres ou mais';
        $prog++;
    } else {
        $chkTamanho = '<br><i class="fas fa-times" style="color: red"></i> NÃO Tem 8 caracteres ou mais';
    }

    $progress = $prog * 20;
    $color = 'bg-danger';
    $ativaButton = '<script>$("#reset").prop("disabled", true);</script>';

    if ($progress <= 40) {
        $color = 'bg-danger';
        $ativaButton = '<script>$("#reset").prop("disabled", true);</script>';
    } elseif ($progress <= 60) {
        $color = '';
        $ativaButton = '<script>$("#reset").prop("disabled", true);</script>';
    } elseif ($progress <= 80) {
        $color = 'bg-warning';
        $ativaButton = '<script>$("#reset").prop("disabled", true);</script>';
    } elseif ($progress === 100) {
        $color = 'bg-success';
        $ativaButton = '<script>$("#reset").prop("disabled", false);</script>';
    }

    echo 'Devido as políticas de segurança do Grupo Logos, as novas senhas devem respeitar as exigências abaixo para maior segurança das informações:<br><br>';
    echo '<div class="progress" style="height: 5px;"><div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $progress . '%;" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100"></div></div>';
    echo $chkCaracteres;
    echo $chkLetrasMaius;
    echo $chkLetrasMin;
    echo $chkNumeros;
    echo $chkTamanho;
    echo $ativaButton;
}

/** Prioridade de exibição da equipe BKO no dashboard (menor = primeiro). */
function stDashBkoStatusPriority(string $status): int
{
    static $order = [
        'atendimento' => 0,
        'pos' => 1,
        'pausa' => 2,
        'online' => 3,
        'indisp' => 4,
        'logout' => 5,
        'offline' => 6,
    ];

    return $order[$status] ?? 99;
}

/**
 * Ordena equipe: atendimento → pós → pausa → livre → indisp. → logout → offline; alfabético no empate.
 *
 * @param array<int, array<string, mixed>> $tiles
 */
function stDashSortBkoTiles(array $tiles, string $statusKey = 'status', string $nameKey = 'nome'): array
{
    usort($tiles, function ($a, $b) use ($statusKey, $nameKey) {
        $pa = stDashBkoStatusPriority((string) ($a[$statusKey] ?? 'offline'));
        $pb = stDashBkoStatusPriority((string) ($b[$statusKey] ?? 'offline'));
        if ($pa !== $pb) {
            return $pa - $pb;
        }

        return strcasecmp((string) ($a[$nameKey] ?? ''), (string) ($b[$nameKey] ?? ''));
    });

    return $tiles;
}
