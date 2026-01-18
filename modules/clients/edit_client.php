<?php
require '../../includes/auth.php';
require '../../config/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND company_id = ?");
$stmt->execute([$id, getCompanyId()]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    die("Cliente não encontrado.");
}

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (!$name) {
        $error = "Nome obrigatório.";
    } else {
        $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ?, phone = ?, updated_by = ? WHERE id = ? AND company_id = ?");
        if ($stmt->execute([$name, $email, $phone, $_SESSION['user_id'], $id, getCompanyId()])) {
            header("Location: clients.php");
            exit;
        } else {
            $error = "Erro ao atualizar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php $basePath = '../../'; include '../../includes/header.php'; ?>
    
    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-user-edit"></i> Editar Cliente</h2>
            <a href="clients.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
        
        <?php if($error): ?>
            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-color); border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5rem;">Informações de Contato</h3>
            
            <div class="form-group">
                <label>Nome Completo *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($client['name']) ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email']) ?>">
                </div>
                
                <div class="form-group">
                    <label>Telefone *</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($client['phone']) ?>">
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="clients.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Alterações</button>
            </div>
        </form>
    </main>
</body>
</html>