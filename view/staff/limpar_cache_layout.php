<?php
/**
 * Limpa o cache de layout do piloto_2.0.
 * Use após alterações em head, scripts ou assets estáticos do layout.
 */

require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/cache_layout.php';

clearLayoutCache();
clearDataCache();

echo '<h2>Cache do Layout Limpo</h2>';
echo '<p style="color: green;">Todo o cache do layout foi limpo com sucesso.</p>';
echo '<p>O sistema irá regenerar o cache na próxima requisição.</p>';
echo '<p><a href="javascript:history.back()">Voltar</a></p>';
