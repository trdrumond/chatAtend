<?php
require_once __DIR__ . '/../cnf/session.php';

echo senhaValida((string) ($_POST['senha'] ?? ''));
