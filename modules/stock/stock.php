<?php
require '../../includes/auth.php';
require '../../config/database.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Estoque - ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <?php $basePath = '../../';
    include '../../includes/header.php'; ?>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-exchange-alt"></i> Movimentações de Estoque</h2>
            <a href="add_stock_movement.php" class="btn btn-success"><i class="fas fa-plus"></i> Registrar
                Movimentação</a>
        </div>

        <div class="table-container">
            <table id="tableModule">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Tipo</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT s.*, p.name as product_name, u.name as user_name 
                            FROM stock_movements s 
                            LEFT JOIN products p ON s.product_id = p.id 
                            LEFT JOIN users u ON s.user_id = u.id 
                            WHERE s.company_id = ? 
                            ORDER BY s.created_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([getCompanyId()]);

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $color = $row['type'] == 'entrada' ? '#2ecc71' : '#e74c3c';
                        $icon = $row['type'] == 'entrada' ? '<i class="fas fa-arrow-up"></i>' : '<i class="fas fa-arrow-down"></i>';

                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>" . htmlspecialchars($row['product_name'] ?? 'Produto Removido') . "</td>";
                        echo "<td style='color:$color; font-weight: bold;'>{$row['quantity']}</td>";
                        echo "<td><span style='color:$color'>$icon " . ucfirst($row['type']) . "</span></td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "<br><small style='color: #666;'>Por: " . htmlspecialchars($row['user_name'] ?? 'Sistema') . "</small></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</php>