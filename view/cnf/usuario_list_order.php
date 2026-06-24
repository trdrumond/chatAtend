<?php

/**
 * ORDER BY para listagem/exportação de usuários:
 * ativos primeiro; perfis hierárquicos (0→3); backoffice e solicitante por último; alfabético; inativos no final.
 */
function stUsuarioListOrderSql(string $userAlias = 'a'): string
{
    $ativo = $userAlias . '.ativo';
    $nivel = $userAlias . '.nivel_id';

    return $ativo . ' DESC, CASE ' . $nivel
        . ' WHEN 0 THEN 0 WHEN 1 THEN 1 WHEN 2 THEN 2 WHEN 3 THEN 3'
        . ' WHEN 4 THEN 90 WHEN 5 THEN 91 ELSE 50 END ASC, nome_completo ASC';
}
