<?php



declare(strict_types=1);



final class MonitoraAuth

{

    /** @var array<string, mixed> */

    private $config;



    /** @param array<string, mixed> $config */

    public function __construct(array $config)

    {

        $this->config = $config;

    }



    public function validar(): void

    {

        $esperado = trim((string) ($this->config['token'] ?? ''));

        if ($esperado === '' || $esperado === 'solvetask-monitora-altere-este-token') {

            MonitoraResponse::erro(503, 'INDISPONIVEL', 'Token da API Monitora não configurado. Defina config.local.php.');

        }



        $recebido = $this->extrairToken();

        if ($recebido === null) {

            MonitoraResponse::erro(

                401,

                'NAO_AUTENTICADO',

                'Token ausente. Envie Authorization: Bearer <token> ou X-Api-Key: <token>.'

            );

        }



        if (!hash_equals($esperado, $recebido)) {

            MonitoraResponse::erro(401, 'NAO_AUTENTICADO', 'Token inválido.');

        }

    }



    private function extrairToken(): ?string

    {

        $modo = (string) ($this->config['auth_mode'] ?? 'both');



        if ($modo === 'bearer' || $modo === 'both') {

            $bearer = $this->extrairBearer($this->obterHeader('Authorization'));

            if ($bearer !== null) {

                return $bearer;

            }

        }



        if ($modo === 'api_key' || $modo === 'both') {

            $apiKey = trim($this->obterHeader('X-Api-Key'));

            if ($apiKey !== '') {

                return $apiKey;

            }

        }



        return null;

    }



    private function extrairBearer(string $auth): ?string

    {

        $auth = trim($auth);

        if ($auth === '') {

            return null;

        }



        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {

            return trim($m[1]);

        }



        return null;

    }



    private function obterHeader(string $nome): string

    {

        $nomeLower = strtolower($nome);

        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $nome));



        if (!empty($_SERVER[$serverKey])) {

            return trim((string) $_SERVER[$serverKey]);

        }



        if ($nomeLower === 'authorization') {

            if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {

                return trim((string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);

            }



            if (!empty($_SERVER['Authorization'])) {

                return trim((string) $_SERVER['Authorization']);

            }



            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {

                return trim((string) $_SERVER['HTTP_AUTHORIZATION']);

            }



            if (!empty($_SERVER['PHP_AUTH_DIGEST'])) {

                return trim((string) $_SERVER['PHP_AUTH_DIGEST']);

            }

        }



        $headers = $this->listarHeaders();

        foreach ($headers as $chave => $valor) {

            if (strtolower((string) $chave) === $nomeLower) {

                return trim((string) $valor);

            }

        }



        return '';

    }



    /** @return array<string, string> */

    private function listarHeaders(): array

    {

        if (function_exists('getallheaders')) {

            $headers = getallheaders();

            if (is_array($headers)) {

                return $headers;

            }

        }



        if (function_exists('apache_request_headers')) {

            $headers = apache_request_headers();

            if (is_array($headers)) {

                return $headers;

            }

        }



        $headers = [];

        foreach ($_SERVER as $chave => $valor) {

            if (strpos($chave, 'HTTP_') === 0) {

                $nome = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($chave, 5)))));

                $headers[$nome] = (string) $valor;

            }

        }



        return $headers;

    }

}


