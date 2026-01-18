<?php
require "../../config/database.php";
require '../../includes/auth.php';
require '../../includes/admin_only.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Sistema ERP - Usuários</title>
  <link rel="stylesheet" href="../../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <?php $basePath = '../../';
  include '../../includes/header.php'; ?>

  <main>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <h2>Gestão de Usuários (Empresa <?= getCompanyName() ?>)</h2>
      <?php if (hasRole('admin')): ?>
        <a href="add_user.php" class="btn btn-success"><i class="fas fa-user-plus"></i> Novo Usuário</a>
      <?php endif; ?>
    </div>

    <!-- Tabela de dados -->
    <div class="table-container">
      <table id="tableModule">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Função</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Filtra usuários pela empresa do usuário logado
          $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE company_id = ?");
          $stmt->execute([getCompanyId()]);

          while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "<td>
                        <a href='edit_user.php?id={$user['id']}' class='action-btn btn-edit'><i class='fas fa-edit'></i> Editar</a>
                        <a href='delete_user.php?id={$user['id']}' class='action-btn btn-delete' onclick='return confirm(\"Tem certeza?\")'><i class='fas fa-trash'></i> Excluir</a>
                      </td>";
            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- JS removido pois agora é PHP puro -->
</body>

</php>