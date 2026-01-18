<?php
// Requer que auth.php já tenha sido incluído ou a sessão iniciada
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Acesso negado. Apenas administradores podem acessar esta página.");
}
?>