<?php
require '../../includes/auth.php';
require '../../config/database.php';

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? '';

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO products (company_id, name, price, stock, category, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([getCompanyId(), $name, $price, $stock, $category, $_SESSION['user_id']])) {
            header("Location: products.php");
            exit;
        } else {
            $msg = "Erro ao cadastrar.";
        }
    } else {
        $msg = "Nome obrigatório.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <title>Novo Produto</title>
        <link rel="stylesheet" href="../../css/style.css">
    </head>

    <body>
        <?php $basePath = '../../'; include '../../includes/header.php'; ?>
        <main>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2><i class="fas fa-box-open"></i> Novo Produto</h2>
            </div>

            <?php if ($msg)
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> $msg</div>"; ?>
            
            <form method="POST" class="form-container">
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label>Nome do Produto *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Cadeira de Escritório">
                    </div>
                    
                    <div class="form-group">
                        <label>Categoria</label>
                        <input type="text" name="category" class="form-control" placeholder="Ex: Móveis">
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label>Preço (R$)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Estoque Inicial</label>
                        <input type="number" name="stock" class="form-control" value="0">
                    </div>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                    <a href="products.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Produto</button>
                </div>
            </form>
        </main>
    </body>
</php>