<?php
/**
 * Configuração de duração da sessão do sistema (apenas para este projeto).
 * Duração: 6 horas.
 * Deve ser incluído antes de session_start() em qualquer fluxo (login, view, access).
 */
$sessionLifetimeSeconds = 6 * 60 * 60; // 6 horas
ini_set('session.gc_maxlifetime', (string) $sessionLifetimeSeconds);
ini_set('session.cookie_lifetime', (string) $sessionLifetimeSeconds);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if ($secureCookie) {
    ini_set('session.cookie_secure', '1');
}

if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => $sessionLifetimeSeconds,
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
