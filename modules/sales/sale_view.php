<?php
require '../../includes/auth.php';
require '../../config/database.php';

$sale_id = $_GET['id'] ?? 0;
$isModal = isset($_GET['modal']); // Verifica se foi chamado via AJAX para o Modal

// 1. Busca os dados da venda, cliente e os nomes de quem criou/editou
$sql = "SELECT s.*, 
               c.name as client_name, c.email as client_email, c.phone as client_phone,
               u1.name as creator_name, 
               u2.name as editor_name 
        FROM sales s
        LEFT JOIN clients c ON s.client_id = c.id
        LEFT JOIN users u1 ON s.user_id = u1.id
        LEFT JOIN users u2 ON s.updated_by = u2.id
        WHERE s.id = ? AND s.company_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$sale_id, getCompanyId()]);
$sale = $stmt->fetch();

if (!$sale) {
    die("Venda não encontrada ou você não tem permissão para vê-la.");
}

// 2. Busca os itens (produtos) desta venda específica
$sqlItems = "SELECT si.*, p.name as product_name 
             FROM sale_items si 
             LEFT JOIN products p ON si.product_id = p.id 
             WHERE si.sale_id = ?";
$stmtItems = $pdo->prepare($sqlItems);
$stmtItems->execute([$sale_id]);
$items = $stmtItems->fetchAll();
?>

<?php if (!$isModal): ?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <title>Venda #<?= $sale['id'] ?> - Detalhes</title>
        <link rel="stylesheet" href="../../css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <?php $basePath = '../../';
        include '../../includes/header.php'; ?>
        <main>
        <?php endif; ?>

        <h2 style="margin-top: 0;"><i class="fas fa-shopping-cart"></i> Detalhes da Venda #<?= $sale['id'] ?></h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 2rem;">
            <div class="card" style="padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fff;">
                <h3 style="margin-top: 0; color: #3498db;"><i class="fas fa-user"></i> Cliente</h3>
                <p><strong>Nome:</strong> <?= htmlspecialchars($sale['client_name'] ?? 'Cliente Removido') ?></p>
                <p><strong>E-mail:</strong> <?= htmlspecialchars($sale['client_email'] ?? '-') ?></p>
                <p><strong>Telefone:</strong> <?= htmlspecialchars($sale['client_phone'] ?? '-') ?></p>
            </div>
            <div class="card" style="padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fff;">
                <h3 style="margin-top: 0; color: #3498db;"><i class="fas fa-info-circle"></i> Resumo</h3>
                <p><strong>Status:</strong> <span class="badge"
                        style="background: #2ecc71; color: white; padding: 2px 8px; border-radius: 4px;"><?= $sale['status'] ?></span>
                </p>
                <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></p>
                <p><strong>Total:</strong> R$ <?= number_format($sale['total'], 2, ',', '.') ?></p>
            </div>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-box"></i> Itens do Pedido</h3>
            <table id="tableModule" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Produto</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Qtd</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Unitário</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                <?= htmlspecialchars($item['product_name'] ?? 'Não encontrado') ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= $item['quantity'] ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">R$
                                <?= number_format($item['unit_price'], 2, ',', '.') ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">R$
                                <?= number_format($item['quantity'] * $item['unit_price'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; font-size: 1.1rem; background: #fdfdfd;">
                        <td colspan="3" style="text-align: right; padding: 15px;">TOTAL FINAL:</td>
                        <td style="color: #2ecc71; padding: 15px;">R$ <?= number_format($sale['total'], 2, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div
            style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 8px; border-left: 5px solid #3498db; font-size: 0.85rem;">
            <h4 style="margin-top: 0;"><i class="fas fa-fingerprint"></i> Auditoria do Registro</h4>
            <p><i class="fas fa-plus-circle"></i> Venda registrada por:
                <strong><?= htmlspecialchars($sale['creator_name'] ?? 'Sistema') ?></strong> em
                <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></p>
            <?php if ($sale['editor_name']): ?>
                <p><i class="fas fa-pen-square"></i> Última alteração por:
                    <strong><?= htmlspecialchars($sale['editor_name']) ?></strong> em
                    <?= date('d/m/Y H:i', strtotime($sale['updated_at'])) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!$isModal): ?>
        </main>
    </body>

    </html>
<?php endif; ?>