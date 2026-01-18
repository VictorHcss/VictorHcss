<?php
require '../../includes/auth.php';
require '../../config/database.php';

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$params = [getCompanyId()];

// Query Unificada: Filtros + Joins de Auditoria
$sql = "SELECT s.*, 
               c.name as client_name, 
               uc.name as creator_name, 
               uu.name as updater_name 
        FROM sales s 
        LEFT JOIN clients c ON s.client_id = c.id
        LEFT JOIN users uc ON s.user_id = uc.id 
        LEFT JOIN users uu ON s.updated_by = uu.id 
        WHERE s.company_id = ?";

if ($data_inicio) {
    $sql .= " AND DATE(s.created_at) >= ?";
    $params[] = $data_inicio;
}

if ($data_fim) {
    $sql .= " AND DATE(s.created_at) <= ?";
    $params[] = $data_fim;
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Vendas - ERP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php $basePath = '../../';
    include '../../includes/header.php'; ?>

    <main>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2><i class="fas fa-list"></i> Histórico de Vendas</h2>
            <a href="add_sale.php" class="btn btn-success"><i class="fas fa-plus"></i> Nova Venda</a>
        </div>

        <div class="table-container" style="margin-bottom: 1.5rem">
            <form method="GET" action="sales.php"
                style="display: flex; gap: 1rem; align-items:flex-end; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Data Inicio:</label>
                    <input type="date" name="data_inicio" class="form-control"
                        value="<?= htmlspecialchars($data_inicio) ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Data Fim:</label>
                    <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="sales.php" class="btn btn-danger"
                    style="background: #95a5a6; text-decoration: none;">Limpar</a>
            </form>
        </div>

        <div class="table-container">
            <table id="tableModule">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $row):
                        $statusClass = '';
                        switch ($row['status']) {
                            case 'Finalizada':
                                $statusClass = 'color: #2ecc71; font-weight: bold;';
                                break;
                            case 'Pendente':
                                $statusClass = 'color: #f39c12; font-weight: bold;';
                                break;
                            case 'Cancelada':
                                $statusClass = 'color: #e74c3c; font-weight: bold;';
                                break;
                        }
                        ?>
                        <tr>
                            <td>
                                <a href="javascript:void(0)" onclick="viewSale(<?= $row['id'] ?>)" class="btn-view"
                                    title="Visualizar" style="text-decoration: none;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?= $row['id'] ?>
                            </td>
                            <td><?= htmlspecialchars($row['client_name'] ?? 'Cliente Removido') ?></td>
                            <td>R$ <?= number_format($row['total'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td style="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <a href="edit_sale.php?id=<?= $row['id'] ?>" class="action-btn btn-edit"><i
                                        class="fas fa-edit"></i> Editar</a><br>
                                <small style="color: #666;">Criado por:
                                    <?= htmlspecialchars($row['creator_name'] ?? 'Sistema') ?></small>
                                <?php if ($row['updater_name']): ?>
                                    <br><small style="color: #666;">Atualizado por:
                                        <?= htmlspecialchars($row['updater_name']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Structure -->
    <div id="saleModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalBody">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>

    <script>
        function viewSale(id) {
            const modal = document.getElementById('saleModal');
            const modalBody = document.getElementById('modalBody');

            if (!modal || !modalBody) return;

            // Show modal immediately
            modal.style.display = 'block';
            modalBody.innerHTML = '<p style="text-align:center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Carregando detalhes...</p>';

            // Fetch details
            fetch('sale_view.php?id=' + id + '&modal=1')
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<p style="color:red; text-align:center;">Erro ao carregar detalhes.</p>';
                });
        }

        function closeModal() {
            const modal = document.getElementById('saleModal');
            if (modal) modal.style.display = 'none';
        }

        // Close when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('saleModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <style>
        /* Modal CSS */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            position: relative;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-view {
            text-decoration: none !important;
            margin-right: 5px;
            display: inline-block;
        }

        .btn-view i {
            text-decoration: none;
        }
    </style>
</body>

</html>