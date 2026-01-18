<?php
require '../../includes/auth.php';
require '../../config/database.php';

// Clientes
$stmt = $pdo->prepare("SELECT id, name FROM clients WHERE company_id = ?");
$stmt->execute([getCompanyId()]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Produtos
$stmt = $pdo->prepare("
    SELECT id, name, price, stock
    FROM products
    WHERE company_id = ?
    ORDER BY name
");
$stmt->execute([getCompanyId()]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Ajustes locais para add_sale.php */
        
        /* Ajuste para Selects dentro de .input-icon */
        .input-icon select.form-control {
            padding-left: 2.8rem;
            appearance: none; /* Remove seta padrão */
            background-image: none; /* Remove seta padrão se houver conflito */
            width: 100%;
            height: 45px; /* Altura consistente */
        }
        
        /* Garantir que a seta customizada do CSS funcione se aplicável, 
           mas como .input-icon sobrepõe, vamos simplificar */
        .input-icon select {
            cursor: pointer;
        }

        /* Estilo do botão de remover */
        .btn-remove {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove:hover {
            background-color: #ef5350;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }

        /* Card do Total Geral */
        .total-summary-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(67, 97, 238, 0.2);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.1);
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
            min-width: 250px;
            position: relative;
            overflow: hidden;
        }

        .total-summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-color);
        }

        .total-summary-card .label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .total-summary-card .amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }

        .total-summary-card .currency {
            font-size: 1.2rem;
            vertical-align: super;
            margin-right: 5px;
        }

        /* Ajustes de layout */
        /* Header padrão restaurado para fundo branco */
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .page-header h2 {
            margin: 0;
            color: var(--text-color);
        }
        
        .page-header p {
            margin: 0;
            color: var(--text-muted);
        }

        /* Responsividade da tabela */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Grid gap override */
        .form-row {
            gap: 1.5rem;
        }

        /* Flex Row for specific layouts where Grid is not ideal */
        .form-row-flex {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .col-grow-2 { flex: 2; min-width: 250px; }
        .col-grow-3 { flex: 3; min-width: 250px; }
        .col-fixed-150 { flex: 0 0 150px; }
        .col-auto { flex: 0 0 auto; }

        /* Input Icon Wrapper fix */
        .input-icon-wrapper {
            position: relative;
            width: 100%;
        }
    </style>
</head>

<body>
    <?php $basePath = '../../'; include '../../includes/header.php'; ?>

    <main>
        <div class="page-header">
            <div>
                <h2><i class="fas fa-cart-plus"></i> Nova Venda</h2>
                <p>Preencha os dados abaixo para registrar uma venda</p>
            </div>
        </div>

        <form method="POST" action="sales_process.php">
            <!-- Dados do Cliente -->
            <div class="table-container" style="margin-bottom: 2rem;">
                <div class="form-row-flex">
                    <div class="form-group col-grow-2">
                        <label>Cliente *</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <select name="client_id" class="form-control" required>
                                <option value="">Selecione um cliente</option>
                                <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Status</label>
                        <div class="input-icon">
                            <i class="fas fa-info-circle"></i>
                            <select name="status" class="form-control">
                                <option value="Finalizada">Finalizada</option>
                                <option value="Pendente">Pendente</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itens da Venda (Table Container) -->
            <div class="table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-boxes"></i> Itens da Venda</h3>
                </div>

                <div class="form-row-flex" style="align-items: flex-end;">
                    <div class="form-group col-grow-3">
                        <label>Produto</label>
                        <div class="input-icon">
                            <i class="fas fa-box-open"></i>
                            <select id="product" class="form-control">
                                <option value="">Selecione um produto</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>"
                                        data-price="<?= $p['price'] ?>" data-stock="<?= $p['stock'] ?>">
                                        <?= htmlspecialchars($p['name']) ?> (Estoque: <?= $p['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-fixed-150">
                        <label>Quantidade</label>
                        <div class="input-icon">
                            <i class="fas fa-sort-numeric-up"></i>
                            <input type="number" id="quantity" min="1" placeholder="1" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-auto">
                        <button type="button" class="btn btn-primary" onclick="addItem()">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="margin-top: 2rem;">
                    <table id="itemsTable">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th style="width: 100px;">Qtd</th>
                                <th>Valor Unit.</th>
                                <th>Total</th>
                                <th style="width: 80px; text-align: center;">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 3rem; border-top: 1px solid #eee; padding-top: 2rem;">
                    <div class="total-summary-card">
                        <span class="label">Total Geral</span>
                        <div class="amount"><span class="currency">R$</span><span id="saleTotal">0.00</span></div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="items" id="itemsInput">

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);">
                    <i class="fas fa-save"></i> Finalizar Venda
                </button>
            </div>
        </form>
    </main>

    <script>
        let items = [];

        function addItem() {
            const productSelect = document.getElementById('product');
            const quantityInput = document.getElementById('quantity');

            const productId = productSelect.value;
            const quantity = parseInt(quantityInput.value);

            if (!productId || isNaN(quantity) || quantity <= 0) {
                alert('Selecione um produto e quantidade válida');
                return;
            }

            const option = productSelect.selectedOptions[0];
            const stock = parseInt(option.dataset.stock);

            if (quantity > stock) {
                alert('Quantidade maior que o estoque disponível');
                return;
            }

            // Verifica se produto já existe na lista
            const existingItemIndex = items.findIndex(item => item.product_id === productId);
            if (existingItemIndex > -1) {
                if (items[existingItemIndex].quantity + quantity > stock) {
                    alert('Quantidade total maior que o estoque disponível');
                    return;
                }
                items[existingItemIndex].quantity += quantity;
            } else {
                items.push({
                    product_id: productId,
                    name: option.dataset.name,
                    quantity: quantity,
                    unit_price: parseFloat(option.dataset.price)
                });
            }

            quantityInput.value = '';
            productSelect.value = ''; // Reset select
            renderTable();
        }

        function removeItem(index) {
            if(confirm('Tem certeza que deseja remover este item?')) {
                items.splice(index, 1);
                renderTable();
            }
        }

        function renderTable() {
            const tbody = document.querySelector('#itemsTable tbody');
            tbody.innerHTML = '';

            let total = 0;

            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #999; padding: 2rem;">Nenhum item adicionado</td></tr>';
            }

            items.forEach((item, index) => {
                const itemTotal = item.quantity * item.unit_price;
                total += itemTotal;

                tbody.innerHTML += `
            <tr>
                <td><strong>${item.name}</strong></td>
                <td>${item.quantity}</td>
                <td>R$ ${item.unit_price.toFixed(2)}</td>
                <td style="color: var(--primary-color); font-weight: bold;">R$ ${itemTotal.toFixed(2)}</td>
                <td style="text-align: center;">
                    <button type="button" class="btn-remove" onclick="removeItem(${index})" title="Remover item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
            });

            document.getElementById('saleTotal').innerText = total.toFixed(2);
            document.getElementById('itemsInput').value = JSON.stringify(items);
        }
        
        // Render initial empty table
        renderTable();
    </script>
</body>

</html>