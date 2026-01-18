<?php
require '../../includes/auth.php';
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sales.php');
    exit;
}

$company_id = getCompanyId();
$client_id = $_POST['client_id'] ?? null;
$status = $_POST['status'] ?? 'Finalizada';
$items = json_decode($_POST['items'] ?? '[]', true);

if (!$client_id || empty($items)) {
    die('Cliente e itens da venda são obrigatórios.');
}

try {
    // 🔐 Inicia transação
    $pdo->beginTransaction();

    // 💰 Calcula total da venda
    $total = 0;
    foreach ($items as $item) {
        $total += $item['quantity'] * $item['unit_price'];
    }

    // 🧾 Insere a venda
    $stmt = $pdo->prepare("
        INSERT INTO sales (company_id, client_id, total, status, user_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$company_id, $client_id, $total, $status, $_SESSION['user_id']]);

    $sale_id = $pdo->lastInsertId();

    // 📦 Insere itens e baixa estoque
    $stmtItem = $pdo->prepare("
        INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
        VALUES (?, ?, ?, ?)
    ");

    $stmtStock = $pdo->prepare("
        UPDATE products
        SET stock = stock - ?
        WHERE id = ? AND company_id = ?
    ");

    foreach ($items as $item) {
        // Insere item
        $stmtItem->execute([
            $sale_id,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price']
        ]);

        // Baixa estoque
        $stmtStock->execute([
            $item['quantity'],
            $item['product_id'],
            $company_id
        ]);
    }

    // ✅ Finaliza
    $pdo->commit();
    header('Location: sales.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die('Erro ao processar venda: ' . $e->getMessage());
}
