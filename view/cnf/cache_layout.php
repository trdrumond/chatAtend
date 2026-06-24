<?php
/**
 * Sistema de Cache Simples para Layout
 * Cacheia partes estáticas do layout para melhorar performance
 */

/**
 * @param string   $cacheKey Chave única do cache
 * @param callable $callback Função que gera o conteúdo se não estiver em cache
 * @param int      $ttl      Tempo de vida do cache em segundos (padrão: 300 = 5 minutos)
 */
function getCachedLayout($cacheKey, $callback, $ttl = 300)
{
    $cacheDir = __DIR__ . '/../cache/layout/';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . md5($cacheKey) . '.cache';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return file_get_contents($cacheFile);
    }

    ob_start();
    $content = call_user_func($callback);
    $buffered = ob_get_clean();

    if ($content === null || $content === false || $content === '') {
        $content = $buffered;
    }

    @file_put_contents($cacheFile, $content);

    return $content;
}

/**
 * @param string|null $cacheKey Chave específica ou null para limpar tudo
 */
function clearLayoutCache($cacheKey = null)
{
    $cacheDir = __DIR__ . '/../cache/layout/';

    if ($cacheKey === null) {
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*.cache');
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return;
    }

    $cacheFile = $cacheDir . md5($cacheKey) . '.cache';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
}

/**
 * Limpa cache de layout de um usuário (sidebar + topbar).
 *
 * @param int|string $userId
 */
function clearUserLayoutCache($userId): void
{
    $userId = (int) $userId;
    if ($userId <= 0) {
        return;
    }

    foreach (['v1', 'v2'] as $version) {
        clearLayoutCache('layout_suspended_' . $version . '_' . $userId);
        foreach (['idx', 'cnf', 'usu'] as $sec) {
            clearLayoutCache('layout_perfil_' . $version . '_' . $userId . '_' . $sec);
        }
    }
}

/**
 * Limpa cache de layout de todos os usuários de um nível.
 *
 * @param PDO        $PDO
 * @param int|string $nivelId
 */
function clearLayoutCacheByNivel(PDO $PDO, $nivelId): void
{
    $nivelId = (int) $nivelId;
    if ($nivelId <= 0 || !$PDO instanceof PDO) {
        return;
    }

    $stmt = $PDO->prepare('SELECT id_user FROM tbl_user WHERE nivel_id = :nivel_id');
    $stmt->execute(['nivel_id' => $nivelId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        clearUserLayoutCache($row['id_user']);
    }
}

/**
 * Limpa cache de layout de todos os usuários de um contrato.
 *
 * @param PDO        $PDO
 * @param int|string $contratoId
 */
function clearLayoutCacheByContrato(PDO $PDO, $contratoId): void
{
    $contratoId = (int) $contratoId;
    if ($contratoId <= 0 || !$PDO instanceof PDO) {
        return;
    }

    $stmt = $PDO->prepare('SELECT id_user FROM tbl_user WHERE contrato_id = :contrato_id');
    $stmt->execute(['contrato_id' => $contratoId]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        clearUserLayoutCache($row['id_user']);
    }
}

/**
 * @param string   $cacheKey Chave única do cache
 * @param callable $callback Função que executa a query e retorna o resultado
 * @param int      $ttl      Tempo de vida do cache em segundos (padrão: 600 = 10 minutos)
 * @return mixed
 */
function getCachedData($cacheKey, $callback, $ttl = 600)
{
    try {
        $cacheDir = __DIR__ . '/../cache/data/';
        if (!is_dir($cacheDir)) {
            if (!@mkdir($cacheDir, 0755, true)) {
                return call_user_func($callback) ?: [];
            }
        }

        $cacheFile = $cacheDir . md5($cacheKey) . '.cache';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $cachedContent = @file_get_contents($cacheFile);
            if ($cachedContent !== false) {
                $cached = @unserialize($cachedContent);
                if ($cached !== false && $cached !== null) {
                    return $cached;
                }
            }
        }

        $data = call_user_func($callback);
        if ($data === null || $data === false) {
            $data = [];
        }

        @file_put_contents($cacheFile, serialize($data));

        return $data;
    } catch (Exception $e) {
        try {
            $data = call_user_func($callback);
            return ($data === null || $data === false) ? [] : $data;
        } catch (Exception $e2) {
            return [];
        }
    }
}

/**
 * @param string|null $cacheKey Chave específica ou null para limpar tudo
 */
function clearDataCache($cacheKey = null)
{
    $cacheDir = __DIR__ . '/../cache/data/';

    if ($cacheKey === null) {
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*.cache');
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return;
    }

    $cacheFile = $cacheDir . md5($cacheKey) . '.cache';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
}
