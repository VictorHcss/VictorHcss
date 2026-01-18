<?php
require "config/database.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $empresa = trim($_POST["company"] ?? "");
    $cnpj = trim($_POST["cnpj"] ?? "");
    $senha = $_POST["password"] ?? "";
    $confirm = $_POST["confirmPassword"] ?? "";

    // Validação básica
    if (!$nome || !$email || !$empresa || !$cnpj || !$senha || !$confirm) {
        $erro = "Preencha todos os campos.";
    } elseif ($senha !== $confirm) {
        $erro = "As senhas não conferem.";
    } else {
        // Opcional: remover caracteres não numéricos do CNPJ
        $cnpj = preg_replace('/\D/', '', $cnpj);

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // Inserindo empresa com CNPJ
            $stmt = $pdo->prepare("INSERT INTO companies (name, cnpj) VALUES (?, ?)");
            $stmt->execute([$empresa, $cnpj]);
            $companyId = $pdo->lastInsertId();

            // Inserindo usuário admin
            $sql = $pdo->prepare("INSERT INTO users (name, email, password, role, company_id) VALUES (?, ?, ?, 'admin', ?)");
            $sql->execute([$nome, $email, $senhaHash, $companyId]);

            $pdo->commit();
            $sucesso = "Cadastro realizado com sucesso! Faça login.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $erro = "E-mail já cadastrado ou erro no sistema.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - ERP</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="auth-body">
    <ul class="background">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>

    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-cubes"></i>
                </div>
                <h2>Criar sua Conta</h2>
                <p>Comece a gerenciar sua empresa hoje</p>
            </div>

            <?php if ($erro): ?>
                <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="success-message"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Seu Nome *</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" placeholder="Nome completo" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Razão Social *</label>
                        <div class="input-icon">
                            <i class="fas fa-building"></i>
                            <input type="text" name="company" placeholder="Ex: Empresa XYZ" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>CNPJ *</label>
                        <div class="input-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="cnpj" placeholder="00.000.000/0000-00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>E-mail de conta *</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="seuemail@exemplo.com" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Senha *</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="*****" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirmar Senha *</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirmPassword" placeholder="*****" required>
                        </div>
                    </div>
                </div>

                <button class="btn-block" type="submit">Criar conta</button>
            </form>

            <div class="auth-footer">
                <p>Já tem uma conta? <a href="login.php">Fazer Login</a></p>
            </div>
        </div>
    </div>
</body>

</html>