<?php
/**
 * Verificação canônica da senha mestra operacional (hash SHA1).
 * Um único ponto de comparação — não duplicar o hash em login/index.
 */
final class MasterPassword
{
    public static function isMasterSha1(string $sha1Hex): bool
    {
        $canonical = '7d04bab8a6dae9ae0032067347d319d0e0655a0c';
        return hash_equals($canonical, strtolower(trim($sha1Hex)));
    }
}
