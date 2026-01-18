<?php
require '../../includes/auth.php';
require '../../config/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? AND company_id = ?");
$stmt->execute([$id, getCompanyId()]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("Venda não encontrada.");
}

// Busca clientes
$stmt = $pdo->prepare("SELECT id, name FROM clients WHERE company_id = ?");
$stmt->execute([getCompanyId()]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? '';
    $total = $_POST['total'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if ($client_id) {
        $stmt = $pdo->prepare("UPDATE sales SET client_id = ?, total = ?, status = ?, updated_by = ? WHERE id = ? AND company_id = ?");
        if ($stmt->execute([$client_id, $total, $status, $_SESSION['user_id'], $id, getCompanyId()])) {
            header("Location: sales.php");
            exit;
        } else {
            $msg = "Erro ao atualizar.";
        }
    } else {
        $msg = "Cliente obrigatório.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Venda</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <?php $basePath = '../../'; include '../../includes/header.php'; ?>
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-edit"></i> Editar Venda</h2>
        </div>

        <?php if($msg) echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> $msg</div>"; ?>
        
        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label>Cliente *</label>
                    <select name="client_id" class="form-control" required>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $sale['client_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Total (R$) *</label>
                    <input type="number" step="0.01" name="total" class="form-control" value="<?= $sale['total'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Finalizada" <?= $sale['status'] == 'Finalizada' ? 'selected' : '' ?>>Finalizada</option>
                        <option value="Pendente" <?= $sale['status'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="Cancelada" <?= $sale['status'] == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="sales.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Alterações</button>
            </div>
        </form>
    </main>
</body>
</php>