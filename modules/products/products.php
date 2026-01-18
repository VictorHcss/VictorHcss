<?php
require '../../includes/auth.php';
require '../../config/database.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Produtos - ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <?php $basePath = '../../';
    include '../../includes/header.php'; ?>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Gestão de Produtos</h2>
            <a href="add_product.php" class="btn btn-success"><i class="fas fa-box-open"></i> Novo Produto</a>
        </div>

        <div class="table-container">
            <table id="tableModule">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Categoria</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT p.*, uc.name as creator_name, uu.name as updater_name 
                                          FROM products p 
                                          LEFT JOIN users uc ON p.created_by = uc.id 
                                          LEFT JOIN users uu ON p.updated_by = uu.id 
                                          WHERE p.company_id = ? 
                                          ORDER BY p.id DESC");
                    $stmt->execute([getCompanyId()]);

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>R$ " . number_format($row['price'], 2, ',', '.') . "</td>";
                        echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category'] ?? 'N/A') . "</td>";
                        echo "<td>
                                <a href='edit_product.php?id={$row['id']}' class='action-btn btn-edit'><i class='fas fa-edit'></i> Editar</a>
                                <a href='delete_product.php?id={$row['id']}' class='action-btn btn-delete' onclick='return confirm(\"Tem certeza?\")'><i class='fas fa-trash'></i> Excluir</a>
                                <br><small style='color: #666;'>Criado por: " . htmlspecialchars($row['creator_name'] ?? 'Sistema') . "</small>";
                        if ($row['updater_name']) {
                            echo "<br><small style='color: #666;'>Atualizado por: " . htmlspecialchars($row['updater_name']) . "</small>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</php>