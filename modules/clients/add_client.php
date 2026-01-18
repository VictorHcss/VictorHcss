<?php
require '../../includes/auth.php';
require '../../config/database.php';

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tipo de cliente
    $type = $_POST['type'] ?? 'fisica';

    // Campos PF
    $name = trim($_POST['name'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');

    // Campos PJ
    $company_name = trim($_POST['company_name'] ?? '');
    $fantasy_name = trim($_POST['fantasy_name'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $ie = trim($_POST['ie'] ?? '');
    $segment = trim($_POST['segment'] ?? '');

    // Campos comuns
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $complement = trim($_POST['complement'] ?? '');
    $neighborhood = trim($_POST['neighborhood'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');

    // Validação básica
    if ($type === 'fisica' && (!$name || !$cpf)) {
        $error = "Nome e CPF são obrigatórios para Pessoa Física.";
    } elseif ($type === 'juridica' && (!$company_name || !$cnpj)) {
        $error = "Razão Social e CNPJ são obrigatórios para Pessoa Jurídica.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clients 
                (company_id, type, name, cpf, company_name, fantasy_name, cnpj, ie, segment,
                email, phone, cep, street, number, complement, neighborhood, city, state, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                getCompanyId(),
                $type,
                $name,
                $cpf,
                $company_name,
                $fantasy_name,
                $cnpj,
                $ie,
                $segment,
                $email,
                $phone,
                $cep,
                $street,
                $number,
                $complement,
                $neighborhood,
                $city,
                $state,
                $_SESSION['user_id']
            ]);

            header("Location: clients.php");
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao cadastrar cliente: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Novo Cliente - ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php $basePath = '../../'; include '../../includes/header.php'; ?>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-user-plus"></i> Novo Cliente</h2>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <h3
                style="margin-bottom: 1.5rem; color: var(--primary-color); border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5rem;">
                Informações Básicas</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de Cliente</label>
                    <select name="type" id="clientType" class="form-control" required>
                        <option value="fisica" <?= ($_POST['type'] ?? '') === 'fisica' ? 'selected' : '' ?>>Pessoa Física
                        </option>
                        <option value="juridica" <?= ($_POST['type'] ?? '') === 'juridica' ? 'selected' : '' ?>>Pessoa
                            Jurídica</option>
                    </select>
                </div>
            </div>

            <!-- Pessoa Física -->
            <div class="fisica form-row">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="name" class="form-control"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00" maxlength="14"
                        value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>">
                </div>
            </div>

            <!-- Pessoa Jurídica -->
            <div class="juridica form-row" style="display:none;">
                <div class="form-group">
                    <label>Razão Social</label>
                    <input type="text" name="company_name" class="form-control"
                        value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Nome Fantasia</label>
                    <input type="text" name="fantasy_name" class="form-control"
                        value="<?= htmlspecialchars($_POST['fantasy_name'] ?? '') ?>">
                </div>
            </div>

            <div class="juridica form-grid-3" style="display:none;">
                <div class="form-group">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0000-00" maxlength="18"
                        value="<?= htmlspecialchars($_POST['cnpj'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Inscrição Estadual</label>
                    <input type="text" name="ie" class="form-control"
                        value="<?= htmlspecialchars($_POST['ie'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Segmento/Atividade</label>
                    <input type="text" name="segment" class="form-control"
                        value="<?= htmlspecialchars($_POST['segment'] ?? '') ?>">
                </div>
            </div>

            <h3
                style="margin: 2rem 0 1.5rem; color: var(--primary-color); border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5rem;">
                Contato e Endereço</h3>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="phone" class="form-control" maxlength="15"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="cep" class="form-control"
                        value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label>Logradouro</label>
                    <input type="text" name="street" class="form-control"
                        value="<?= htmlspecialchars($_POST['street'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Número</label>
                    <input type="text" name="number" class="form-control"
                        value="<?= htmlspecialchars($_POST['number'] ?? '') ?>">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Complemento</label>
                    <input type="text" name="complement" class="form-control"
                        value="<?= htmlspecialchars($_POST['complement'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" name="neighborhood" class="form-control"
                        value="<?= htmlspecialchars($_POST['neighborhood'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="city" class="form-control"
                        value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>UF</label>
                    <input type="text" name="state" class="form-control"
                        value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="clients.php" class="btn btn-danger" style="background-color: #95a5a6;">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Cadastrar Cliente</button>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('clientType').addEventListener('change', function () {
            const tipo = this.value;
            document.querySelectorAll('.fisica').forEach(el => el.style.display = tipo === 'fisica' ? 'grid' : 'none');
            document.querySelectorAll('.juridica').forEach(el => el.style.display = tipo === 'juridica' ? 'grid' : 'none');
        });

        window.addEventListener('load', () => {
            const tipo = document.getElementById('clientType').value;
            document.querySelectorAll('.fisica').forEach(el => el.style.display = tipo === 'fisica' ? 'grid' : 'none');
            document.querySelectorAll('.juridica').forEach(el => el.style.display = tipo === 'juridica' ? 'grid' : 'none');
        });

        function maskDocument(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            }
            input.value = value;
        }

        document.querySelector('input[name="cpf"]').addEventListener('input', e => maskDocument(e.target));
        document.querySelector('input[name="cnpj"]').addEventListener('input', e => maskDocument(e.target));

        function maskPhone(input) {
            let v = input.value.replace(/\D/g, '');
            if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            else v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
            input.value = v;
        }
        document.querySelector('input[name="phone"]').addEventListener('input', e => maskPhone(e.target));

        document.querySelector('input[name="cep"]').addEventListener('blur', function () {
            let cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.erro) {
                            document.querySelector('input[name="street"]').value = data.logradouro;
                            document.querySelector('input[name="neighborhood"]').value = data.bairro;
                            document.querySelector('input[name="city"]').value = data.localidade;
                            document.querySelector('input[name="state"]').value = data.uf;
                        }
                    });
            }
        });
    </script>
</body>

</html>