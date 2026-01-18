<?php
require '../../includes/auth.php';
require '../../includes/admin_only.php'; // Garante que apenas admin acesse
require "../../config/database.php";

$id = $_GET['id'] ?? null;

if ($id) {
    // Garante que só pode excluir produtos da mesma empresa

    // 1. Verifica se existem movimentações de estoque vinculadas
    // Isso evita quebra de histórico e erros de integridade
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_movement WHERE product_id = ? AND company_id = ?");
    $stmt->execute([$id, getCompanyId()]);

    if ($stmt->fetchColumn() > 0) {
        // Não pode excluir se tiver histórico de estoque
        header("Location: products.php?error=movimentacoes_vinculadas");
        exit;
    }

    // 2. Se não houver impedimentos, vamos inativar o produto selecionado
    $stmt = $pdo->prepare("UPDATE products SET active = 0 WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, getCompanyId()]);
}

header("Location: products.php");
exit;
?>