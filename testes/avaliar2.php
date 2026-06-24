<?php

$arquivo = 'C:\\xampp\\htdocs\\ia_scripts\\arquivo.txt';
$comando = 'C:\\xampp\\htdocs\\ia_scripts\\venv\\Scripts\\python.exe C:\\xampp\\htdocs\\ia_scripts\\sentimento.py ' . escapeshellarg($arquivo);
$output = shell_exec($comando);
echo "<pre>$output</pre>";

echo "<hr>";
// Caminhos
$arquivoTxt = 'C:\\xampp\\htdocs\\ia_scripts\\arquivo.txt';
$python = 'C:\\xampp\\htdocs\\ia_scripts\\venv\\Scripts\\python.exe';
$scriptPython = 'C:\\xampp\\htdocs\\ia_scripts\\avaliador.py';

// Monta comando
$comando = "$python $scriptPython " . escapeshellarg($arquivoTxt);

// Executa
exec($comando, $saida);

// Junta resposta
$respostaJson = implode("", $saida);

// Tenta decodificar JSON
$resultado = json_decode($respostaJson, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo "<h3>Avaliação do Atendimento:</h3><ul>";
    foreach ($resultado as $criterio => $nota) {
        echo "<li><strong>$criterio:</strong> $nota</li>";
    }
    if (isset($resultado['sugestao'])) {
            echo "<p><strong>Sugestão:</strong> " . $resultado['sugestao'] . "</p>";
        }
    echo "</ul>";
} else {
    echo "<pre>Erro ao processar resposta do Python:\n$respostaJson</pre>";
}
?>
