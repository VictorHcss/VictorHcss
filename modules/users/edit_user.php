<?php
require '../../includes/auth.php';
require '../../includes/admin_only.php';
require "../../config/database.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: users.php");
    exit;
}

// Verifica se usuário existe E pertence à mesma empresa
$stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ? AND company_id = ?");
$stmt->execute([$id, getCompanyId()]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);

    if (!$name || !$email || !$role) {
        $erro = "Preencha todos os campos.";
    } else {
        try {
            // Atualiza apenas se pertencer à mesma empresa
            $stmt = $pdo->prepare(
                "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ? AND company_id = ?"
            );
            $stmt->execute([$name, $email, $role, $id, getCompanyId()]);
            $sucesso = "Usuário atualizado com sucesso!";

            // Atualiza os dados exibidos
            $user['name'] = $name;
            $user['email'] = $email;
            $user['role'] = $role;
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar usuário.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <?php $basePath = '../../'; include '../../includes/header.php'; ?>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>
        </div>

        <?php if ($erro): ?>
            <div class="error-message" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div style="color: green; text-align: center; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="role">Função</label>
                    <select id="role" name="role" class="form-control">
                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Usuário Padrão</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="users.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Salvar Alterações</button>
            </div>
        </form>
    </main>
</body>
</html>