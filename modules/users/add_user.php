<?php
require '../../includes/auth.php';
require '../../includes/admin_only.php';
require "../../config/database.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!$name || !$email || !$password) {
        $erro = "Preencha todos os campos.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insere com o company_id do admin logado
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hash, $role, getCompanyId()]);
            $sucesso = "Usuário cadastrado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar usuário. Email possivelmente já em uso.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <?php $basePath = '../../'; include '../../includes/header.php'; ?>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-user-plus"></i> Novo Usuário</h2>
        </div>

        <?php if ($erro): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Digite o nome completo" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite o email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Senha *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Digite a senha" required>
                </div>

                <div class="form-group">
                    <label for="role">Função</label>
                    <select id="role" name="role" class="form-control">
                        <option value="user">Usuário Padrão</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="users.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Cadastrar Usuário</button>
            </div>
        </form>
    </main>
</body>
</html>