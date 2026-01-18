<?php
date_default_timezone_set('America/Sao_Paulo');
$host = 'localhost';
require 'includes/auth.php';
require 'config/database.php';

$company_id = getCompanyId();

// Busca contagens básicas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalClients = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalProducts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalSales = $stmt->fetchColumn();

// --- ADICIONE ESTE BLOCO AQUI ---
// Busca o valor total de todas as vendas (Soma da coluna total)
$stmt = $pdo->prepare("SELECT SUM(total) FROM sales WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalSalesValue = $stmt->fetchColumn() ?: 0;
// -------------------------------

// Estoque total
$stmt = $pdo->prepare("SELECT SUM(stock) FROM products WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalStock = $stmt->fetchColumn() ?: 0;

// Ranking de Vendedores
$sqlVendendores = "SELECT u.name, SUM(s.total) as total_vendas
FROM sales s
JOIN users u ON s.user_id = u.id
WHERE s.company_id = ?
GROUP BY u.id
ORDER BY total_vendas DESC LIMIT 5";

$stmtV = $pdo->prepare($sqlVendendores);
$stmtV->execute([$company_id]);
$rankingData = $stmtV->fetchAll(PDO::FETCH_ASSOC);

// Cálculos (Agora com a variável definida)
$ticketMedio = $totalSales > 0 ? ($totalSalesValue / $totalSales) : 0;
$metaMensal = 50000;
$percentualMeta = min(($totalSalesValue / $metaMensal) * 100, 100);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>ERP - Dashboard</title>
    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php $basePath = './';
    include 'includes/header.php'; ?>

    <main>
        <!-- Bem-vindo -->
        <?php
        $hora = (int) date('H');
        if ($hora >= 5 && $hora < 12) {
            $saudacao = "Bom dia";
            $icone_clima = "fa-sun";
        } elseif ($hora >= 12 && $hora < 18) {
            $saudacao = "Boa tarde";
            $icone_clima = "fa-cloud-sun";
        } else {
            $saudacao = "Boa noite";
            $icone_clima = "fa-moon";
        }
        ?>

        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>
                    <i class="fas <?= $icone_clima ?> icon-weather"></i>
                    <?= $saudacao ?>, <?= htmlspecialchars($_SESSION['user_name']) ?>!
                </h2>
                <p>
                    <i class="fas fa-industry"></i>
                    Gestão da Unidade:<strong><?= getCompanyName() ?></strong>
                </p>
            </div>
            <div class="welcome-time">
                <div class="time-now"><?= date('H:i') ?></div>
                <div class="date-now">
                    <i class="far fa-calendar-check"></i>
                    <?= date('d/m/Y') ?>
                </div>
            </div>
        </div>

        <!-- Cards de resumo (Sem estilo para estudo) -->
        <div>
            <div class="dashboard-grid">
                <div class="card-stat card-blue">
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                    <div class="card-info">
                        <h3>Clientes</h3>
                        <p id="totalClients">
                            <?= $totalClients ?>
                        </p>
                    </div>
                </div>
                <div class="card-stat card-orange">
                    <div class="card-icon"><i class="fas fa-box"></i></div>
                    <div class="card-info">
                        <h3>Produtos</h3>
                        <p id="totalProducts">
                            <?= $totalProducts ?>
                        </p>
                    </div>
                </div>
                <div class="card-stat card-green">
                    <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="card-info">
                        <h3>Vendas</h3>
                        <p id="totalSales">
                            <?= $totalSales ?>
                        </p>
                    </div>
                </div>
                <div class="card-stat card-purple">
                    <div class="card-icon"><i class="fas fa-warehouse"></i></div>
                    <div class="card-info">
                        <h3>Estoque</h3>
                        <p id="totalStock">
                            <?= $totalStock ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="dashboard-grid">
                <div class="card-stat card-blue">
                    <div class="card-icon"><i class="fas fa-receipt"></i></div>
                    <div class="card-info">
                        <h3>Ticket Médio</h3>
                        <p>R$
                            <?= number_format($ticketMedio, 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
                <div class="card-stat card-green">
                    <div class="card-icon"><i class="fas fa-bullseye"></i></div>
                    <div class="card-info">
                        <h3>Meta do Mês</h3>
                        <p>
                            <?= round($percentualMeta) ?>%
                        </p>
                    </div>
                </div>
            </div>
            <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
                <div class="table-container">
                    <h3><i class="fas fa-trophy"></i> Desempenho por Vendedor</h3>
                    <canvas id="vendasVendedorChart"></canvas>
                </div>
                <div class="table-container">
                    <h3><i class="fas fa-tachometer-alt"></i> Atingimento de Meta</h3>
                    <canvas id="metaGaugeChart"></canvas>
                </div>
            </div>

            <!-- Últimas vendas -->
            <div class="table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin:0;"><i class="fas fa-history"></i> Últimas Vendas</h3>
                    <a href="modules/sales/sales.php" class="btn-link"
                        style="text-decoration: none; color: var(--primary-color); font-size: 0.9rem;">Ver todas →</a>
                </div>
                <table id="lastSalesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT s.*, c.name as client_name 
                    FROM sales s 
                    LEFT JOIN clients c ON s.client_id = c.id 
                    WHERE s.company_id = ? 
                    ORDER BY s.created_at DESC LIMIT 5";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([getCompanyId()]);

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $statusStyle = "";
                            if ($row['status'] == 'Finalizada')
                                $statusStyle = "color: #2ecc71; font-weight: bold;";
                            if ($row['status'] == 'Pendente')
                                $statusStyle = "color: #f39c12; font-weight: bold;";
                            if ($row['status'] == 'Cancelada')
                                $statusStyle = "color: #e74c3c; font-weight: bold;";

                            echo "<tr>";
                            echo "<td>#{$row['id']}</td>";
                            echo "<td>" . htmlspecialchars($row['client_name'] ?? 'Removido') . "</td>";
                            echo "<td>R$ " . number_format($row['total'], 2, ',', '.') . "</td>";
                            echo "<td><span style='{$statusStyle}'>" . htmlspecialchars($row['status']) . "</span></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.rankingNomes = <?= json_encode(array_column($rankingData, 'name')) ?>;
        window.rankingValores = <?= json_encode(array_column($rankingData, 'total_vendas')) ?>;
        window.percentualMeta = <?= (float) $percentualMeta ?>;
    </script>

    <script src="js/main.js?v=<?= time(); ?>"></script>
    <script>
        if (window.App && window.App.UI && typeof window.App.UI.renderDashboardCharts === 'function') {
            window.App.UI.renderDashboardCharts([], []);
        }
    </script>
</body>

</html>
