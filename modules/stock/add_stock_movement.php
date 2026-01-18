<?php
require '../../includes/auth.php';
require '../../config/database.php';

// Busca produtos
$stmt = $pdo->prepare("SELECT id, name FROM products WHERE company_id = ?");
$stmt->execute([getCompanyId()]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $type = $_POST['type'] ?? 'entrada'; // entrada ou saida

    if ($product_id && $quantity > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Registra movimentação
            $stmt = $pdo->prepare("INSERT INTO stock_movements (company_id, product_id, quantity, type, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([getCompanyId(), $product_id, $quantity, $type, $_SESSION['user_id']]);

            // 2. Atualiza estoque do produto
            if ($type == 'entrada') {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ? AND company_id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND company_id = ?");
            }
            $stmt->execute([$quantity, $product_id, getCompanyId()]);

            $pdo->commit();
            header("Location: stock.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Erro ao registrar movimentação: " . $e->getMessage();
        }
    } else {
        $msg = "Selecione um produto e quantidade válida.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Movimentação de Estoque</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <?php $basePath = '../../';
    include '../../includes/header.php'; ?>
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-dolly-flatbed"></i> Registrar Movimentação</h2>
        </div>

        <?php if ($msg)
            echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> $msg</div>"; ?>

        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label>Produto *</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">Selecione um produto</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Quantidade *</label>
                    <input type="number" name="quantity" class="form-control" min="1" required placeholder="0">
                </div>

                <div class="form-group">
                    <label>Tipo de Movimentação</label>
                    <select name="type" class="form-control">
                        <option value="entrada">Entrada (Adicionar ao Estoque)</option>
                        <option value="saida">Saída (Remover do Estoque)</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="stock.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Movimentação</button>
            </div>
        </form>
    </main>
</body>
</php>