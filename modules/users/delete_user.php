<?php
require '../../includes/auth.php';
require '../../includes/admin_only.php';
require "../../config/database.php";

$id = $_GET['id'] ?? null;

if ($id) {
    // Garante que só pode excluir usuários da mesma empresa
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, getCompanyId()]);
}

header("Location: users.php");
exit;
?>