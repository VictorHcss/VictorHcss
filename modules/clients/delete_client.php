<?php
require '../../includes/auth.php';
require '../../includes/admin_only.php'; // Garante que apenas admin acesse
require "../../config/database.php";

$id = $_GET['id'] ?? null;

if ($id) {
    // Garante que só pode excluir clientes da mesma empresa
    // Verifica se existem vendas vinculadas para evitar erro de FK ou inconsistência
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE client_id = ? AND company_id = ?");
    $stmt->execute([$id, getCompanyId()]);
    
    if ($stmt->fetchColumn() > 0) {
        // Não pode excluir se tiver vendas
        // Como é um redirecionamento simples, ideal seria passar mensagem na sessão, 
        // mas para manter simples (sem alterar toda estrutura de msg), vamos apenas não excluir ou redirecionar com erro na URL.
        header("Location: clients.php?error=vendas_vinculadas");
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, getCompanyId()]);
}

header("Location: clients.php");
exit;
?>