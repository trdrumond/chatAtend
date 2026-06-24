<?php
$arquivo = 'C:\\xampp\\htdocs\\ia_scripts\\39006.txt';
$comando = 'C:\\xampp\\htdocs\\ia_scripts\\venv\\Scripts\\python.exe C:\\xampp\\htdocs\\ia_scripts\\sentimento.py ' . escapeshellarg($arquivo);
$output = shell_exec($comando);
echo "<pre>$output</pre>";
?>

