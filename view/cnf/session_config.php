<?php
/**
 * Configuração de duração da sessão do sistema (apenas para este projeto).
 * Duração: 6 horas.
 * Deve ser incluído antes de session_start() em qualquer fluxo (login, view, access).
 */
$sessionLifetimeSeconds = 6 * 60 * 60; // 6 horas
ini_set('session.gc_maxlifetime', (string) $sessionLifetimeSeconds);
ini_set('session.cookie_lifetime', (string) $sessionLifetimeSeconds);
