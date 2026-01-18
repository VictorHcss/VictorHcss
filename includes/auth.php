<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Helper para verificar role
function hasRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper para obter company_id
function getCompanyId()
{
    return $_SESSION['company_id'] ?? null;
}

function getCompanyName()
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
    $stmt->execute([getCompanyId()]);
    return $stmt->fetchColumn() ?: "Empresa Desconhecida";
}
?>