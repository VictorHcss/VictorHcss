/**
 * ERP - Dashboard - Main Script
 * Gerencia autenticação, dados e lógica das páginas.
 */

window.App = {
    // Configurações e Estado
    config: {
        keys: {
            users: 'erp_users',
            clients: 'erp_clients',
            products: 'erp_products',
            sales: 'erp_sales',
            loggedUser: 'erp_logged_user'
        }
    },

    // Inicialização
    init: function () {
        App.UI.injectResources();
        App.Data.init();
        App.Auth.check();
        App.UI.setupGlobalEvents();
        App.Router.route();
    },

    // Gerenciamento de Dados (LocalStorage)
    Data: {
        init: () => {
            if (!localStorage.getItem(App.config.keys.users)) {
                const users = [
                    { id: 1, name: "Admin", email: "admin@erp.com", password: "123", role: "admin" }
                ];
                localStorage.setItem(App.config.keys.users, JSON.stringify(users));
            }

            if (!localStorage.getItem(App.config.keys.clients)) {
                const clients = [
                    { id: 1, name: "Empresa ABC", email: "contato@abc.com", phone: "1199999999" },
                    { id: 2, name: "João da Silva", email: "joao@gmail.com", phone: "1188888888" },
                    { id: 3, name: "Maria Oliveira", email: "maria@outlook.com", phone: "1177777777" }
                ];
                localStorage.setItem(App.config.keys.clients, JSON.stringify(clients));
            }

            if (!localStorage.getItem(App.config.keys.products)) {
                const products = [
                    { id: 1, name: "Notebook Dell", price: 3500.00, stock: 10, category: "Eletrônicos" },
                    { id: 2, name: "Mouse Logitech", price: 150.00, stock: 50, category: "Acessórios" },
                    { id: 3, name: "Teclado Mecânico", price: 400.00, stock: 30, category: "Acessórios" },
                    { id: 4, name: "Monitor 24pol", price: 1200.00, stock: 5, category: "Eletrônicos" }
                ];
                localStorage.setItem(App.config.keys.products, JSON.stringify(products));
            }

            if (!localStorage.getItem(App.config.keys.sales)) {
                const sales = [
                    { id: 1, clientId: 1, clientName: "Empresa ABC", total: 3650.00, date: "2023-10-01", status: "Finalizada" },
                    { id: 2, clientId: 2, clientName: "João da Silva", total: 150.00, date: "2023-10-05", status: "Finalizada" },
                    { id: 3, clientId: 3, clientName: "Maria Oliveira", total: 400.00, date: "2023-10-10", status: "Pendente" }
                ];
                localStorage.setItem(App.config.keys.sales, JSON.stringify(sales));
            }
        },

        get: (key) => {
            return JSON.parse(localStorage.getItem(key) || '[]');
        },

        set: (key, data) => {
            localStorage.setItem(key, JSON.stringify(data));
        },

        add: (key, item) => {
            const data = App.Data.get(key);
            item.id = Date.now();
            data.push(item);
            App.Data.set(key, data);
            return item;
        },

        update: (key, updatedItem) => {
            let data = App.Data.get(key);
            data = data.map(item => item.id === updatedItem.id ? updatedItem : item);
            App.Data.set(key, data);
        },

        delete: (key, id) => {
            let data = App.Data.get(key);
            data = data.filter(item => item.id != id);
            App.Data.set(key, data);
        }
    },

    // Autenticação
    Auth: {
        check: () => {
            const path = window.location.pathname;
            const isLoginPage = path.includes('login.php');
            const isRegisterPage = path.includes('register.php');
            const user = JSON.parse(localStorage.getItem(App.config.keys.loggedUser));

            if (!user && !isLoginPage && !isRegisterPage) {
                window.location.href = 'login.php';
            } else if (user && (isLoginPage || isRegisterPage)) {
                window.location.href = 'index.php';
            }

            if (user && !isLoginPage && !isRegisterPage) {
                const userNameEl = document.getElementById('userName');
                if (userNameEl) userNameEl.textContent = user.name;
            }
        },

        login: (email, password) => {
            const users = App.Data.get(App.config.keys.users);
            const user = users.find(u => u.email === email && u.password === password);
            if (user) {
                localStorage.setItem(App.config.keys.loggedUser, JSON.stringify(user));
                window.location.href = 'index.php';
                return true;
            }
            return false;
        },

        register: (name, email, password) => {
            const users = App.Data.get(App.config.keys.users);
            if (users.some(u => u.email === email)) {
                return { success: false, message: 'Email já cadastrado.' };
            }

            const newUser = {
                id: Date.now(),
                name: name,
                email: email,
                password: password,
                role: 'user' // Default role
            };

            users.push(newUser);
            App.Data.set(App.config.keys.users, users);

            // Auto login after register
            localStorage.setItem(App.config.keys.loggedUser, JSON.stringify(newUser));
            window.location.href = 'index.php';

            return { success: true };
        },

        logout: () => {
            localStorage.removeItem(App.config.keys.loggedUser);
            window.location.href = 'login.php';
        }
    },

    // Roteamento
    Router: {
        route: () => {
            const path = window.location.pathname;
            if (path.includes('login.php')) {
                App.Controllers.login();
            } else if (path.includes('register.php')) {
                App.Controllers.register();
            } else if (path.includes('index.php') || path.endsWith('/')) {
                App.Controllers.dashboard();
            } else if (path.includes('clients.php')) {
                App.Controllers.clients();
            } else if (path.includes('products.php')) {
                App.Controllers.products();
            } else if (path.includes('sales.php')) {
                App.Controllers.sales();
            } else if (path.includes('stock.php')) {
                App.Controllers.stock();
            } else if (path.includes('users.php')) {
                App.Controllers.users();
            }
        }
    },

    // Controladores
    Controllers: {
        login: () => {
            const form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const success = App.Auth.login(email, password);
                    if (!success) {
                        document.getElementById('errorMsg').textContent = "Email ou senha inválidos.";
                        App.UI.showToast('Erro ao logar', 'error');
                    }
                });
            }
        },

        register: () => {
            const form = document.getElementById('registerForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const name = document.getElementById('name').value;
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;

                    if (password !== confirmPassword) {
                        App.UI.showToast('As senhas não coincidem.', 'error');
                        return;
                    }

                    if (password.length < 3) {
                        App.UI.showToast('Senha muito curta.', 'error');
                        return;
                    }

                    const result = App.Auth.register(name, email, password);
                    if (!result.success) {
                        App.UI.showToast(result.message, 'error');
                    } else {
                        App.UI.showToast('Cadastro realizado com sucesso!');
                    }
                });
            }
        },

        dashboard: () => {
            const clients = App.Data.get(App.config.keys.clients);
            const products = App.Data.get(App.config.keys.products);
            const sales = App.Data.get(App.config.keys.sales);

            const totalClients = clients.length;
            const totalProducts = products.length;
            const totalSales = sales.length;
            const totalStock = products.reduce((acc, p) => acc + Number(p.stock), 0);
            const elClients = document.getElementById('totalClients');
            if (elClients) elClients.textContent = totalClients;
            const elProducts = document.getElementById('totalProducts');
            if (elProducts) elProducts.textContent = totalProducts;
            const elSales = document.getElementById('totalSales');
            if (elSales) elSales.textContent = totalSales;
            const elStock = document.getElementById('totalStock');
            if (elStock) elStock.textContent = totalStock;

            if (typeof Chart !== 'undefined') {
                App.UI.renderDashboardCharts(products, sales);
            }

            const tbody = document.querySelector("#lastSalesTable tbody");
            if (tbody) {
                tbody.innerHTML = '';
                sales.slice(-5).reverse().forEach(sale => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${sale.id}</td>
                            <td>${sale.clientName}</td>
                            <td>R$ ${Number(sale.total).toFixed(2)}</td>
                            <td><span class="badge ${sale.status === 'Finalizada' ? 'badge-success' : 'badge-warning'}">${sale.status}</span></td>
                        </tr>
                    `;
                });
            }
        },

        clients: () => {
            App.UI.setupSearch(App.config.keys.clients, ['name', 'email']);
            App.UI.renderTable(App.config.keys.clients, [
                { key: 'id', label: 'ID' },
                { key: 'name', label: 'Nome' },
                { key: 'email', label: 'Email' },
                { key: 'phone', label: 'Telefone' }
            ]);

            const btnAdd = document.getElementById('btnAdd');
            if (btnAdd) {
                btnAdd.onclick = () => {
                    App.UI.showModal('Novo Cliente', [
                        { name: 'name', label: 'Nome', type: 'text' },
                        { name: 'email', label: 'Email', type: 'email' },
                        { name: 'phone', label: 'Telefone', type: 'text' }
                    ], (data) => {
                        App.Data.add(App.config.keys.clients, data);
                        App.Controllers.clients();
                        App.UI.showToast('Cliente adicionado com sucesso!');
                    });
                };
            }
        },

        products: () => {
            App.UI.setupSearch(App.config.keys.products, ['name', 'category']);
            App.UI.renderTable(App.config.keys.products, [
                { key: 'id', label: 'ID' },
                { key: 'name', label: 'Nome' },
                { key: 'price', label: 'Preço', format: (v) => `R$ ${Number(v).toFixed(2)}` },
                { key: 'stock', label: 'Estoque' },
                { key: 'category', label: 'Categoria' }
            ]);

            const btnAdd = document.getElementById('btnAdd');
            if (btnAdd) {
                btnAdd.onclick = () => {
                    App.UI.showModal('Novo Produto', [
                        { name: 'name', label: 'Nome', type: 'text' },
                        { name: 'price', label: 'Preço', type: 'number' },
                        { name: 'stock', label: 'Estoque Inicial', type: 'number' },
                        { name: 'category', label: 'Categoria', type: 'text' }
                    ], (data) => {
                        App.Data.add(App.config.keys.products, data);
                        App.Controllers.products();
                        App.UI.showToast('Produto adicionado com sucesso!');
                    });
                };
            }
        },

        sales: () => {
            App.UI.setupSearch(App.config.keys.sales, ['clientName', 'status']);
            App.UI.renderTable(App.config.keys.sales, [
                { key: 'id', label: 'ID' },
                { key: 'clientName', label: 'Cliente' },
                { key: 'total', label: 'Total', format: (v) => `R$ ${Number(v).toFixed(2)}` },
                { key: 'date', label: 'Data' },
                { key: 'status', label: 'Status' }
            ]);

            const btnAdd = document.getElementById('btnAdd');
            if (btnAdd) {
                // Habilitar botão de nova venda
                btnAdd.style.display = 'inline-flex';
                btnAdd.onclick = () => {
                    // Carregar clientes e produtos para os selects
                    const clients = App.Data.get(App.config.keys.clients);
                    const products = App.Data.get(App.config.keys.products);

                    const clientOptions = clients.map(c => ({ value: c.id, label: c.name }));
                    const productOptions = products.map(p => ({ value: p.id, label: `${p.name} (R$ ${p.price})` }));

                    if (clients.length === 0 || products.length === 0) {
                        App.UI.showToast('Cadastre clientes e produtos antes de vender.', 'error');
                        return;
                    }

                    App.UI.showModal('Nova Venda', [
                        { name: 'clientId', label: 'Cliente', type: 'select', options: clientOptions },
                        { name: 'productId', label: 'Produto', type: 'select', options: productOptions },
                        { name: 'quantity', label: 'Quantidade', type: 'number', value: 1 }
                    ], (data) => {
                        const product = products.find(p => p.id == data.productId);
                        const client = clients.find(c => c.id == data.clientId);
                        const qty = Number(data.quantity);

                        if (product.stock < qty) {
                            App.UI.showToast(`Estoque insuficiente! Disponível: ${product.stock}`, 'error');
                            return; // Não fecha o modal (idealmente), mas aqui vai fechar e falhar. MVP.
                        }

                        // Baixar estoque
                        product.stock -= qty;
                        App.Data.update(App.config.keys.products, product);

                        // Criar venda
                        const total = product.price * qty;
                        const sale = {
                            clientId: client.id,
                            clientName: client.name,
                            total: total,
                            date: new Date().toISOString().split('T')[0],
                            status: 'Finalizada',
                            items: [{ productId: product.id, productName: product.name, quantity: qty, price: product.price }]
                        };

                        App.Data.add(App.config.keys.sales, sale);
                        App.Controllers.sales();
                        App.UI.showToast('Venda realizada com sucesso!');
                    });
                };
            }
        },

        stock: () => {
            App.UI.setupSearch(App.config.keys.products, ['name']);
            App.UI.renderTable(App.config.keys.products, [
                { key: 'id', label: 'ID' },
                { key: 'name', label: 'Produto' },
                { key: 'stock', label: 'Quantidade', format: (v) => v < 10 ? `<span style="color:red;font-weight:bold">${v}</span>` : v }
            ]);
            const btnAdd = document.getElementById('btnAdd');
            if (btnAdd) btnAdd.style.display = 'none';
        },

        users: () => {
            App.UI.setupSearch(App.config.keys.users, ['name', 'email']);
            App.UI.renderTable(App.config.keys.users, [
                { key: 'id', label: 'ID' },
                { key: 'name', label: 'Nome' },
                { key: 'email', label: 'Email' },
                { key: 'role', label: 'Função' }
            ]);

            const btnAdd = document.getElementById('btnAdd');
            if (btnAdd) {
                btnAdd.onclick = () => {
                    App.UI.showModal('Novo Usuário', [
                        { name: 'name', label: 'Nome', type: 'text' },
                        { name: 'email', label: 'Email', type: 'email' },
                        { name: 'password', label: 'Senha', type: 'password' },
                        {
                            name: 'role', label: 'Função', type: 'select', options: [
                                { value: 'admin', label: 'Admin' },
                                { value: 'user', label: 'Usuário' }
                            ]
                        }
                    ], (data) => {
                        App.Data.add(App.config.keys.users, data);
                        App.Controllers.users();
                        App.UI.showToast('Usuário adicionado com sucesso!');
                    });
                };
            }
        }
    },

    // UI Helpers
    UI: {
        injectResources: () => {
            // FontAwesome
            if (!document.querySelector('link[href*="font-awesome"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
                document.head.appendChild(link);
            }

            // Modal Container
            if (!document.querySelector('.modal-overlay')) {
                const modalphp = `
                    <div class="modal-overlay" iUI:d="modalOverlay">
                        <div class="modal">
                            <div class="modal-header">
                                <h3 id="modalTitle">Título</h3>
                                <button class="modal-close" id="modalClose">&times;</button>
                            </div>
                            <div class="modal-body" id="modalBody">
                                <!-- Form Fields -->
                            </div>
                            <div class="modal-footer">
                                <button id="modalCancel" style="background-color:#95a5a6">Cancelar</button>
                                <button id="modalConfirm">Salvar</button>
                            </div>
                        </div>
                    </div>
                    <div class="toast-container" id="toastContainer"></div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                
                // Eventos do Modal
                document.getElementById('modalClose').onclick = App.UI.closeModal;
                document.getElementById('modalCancel').onclick = App.UI.closeModal;
                document.getElementById('modalOverlay').onclick = (e) => {
                    if (e.target.id === 'modalOverlay') App.UI.closeModal();
                };
            }

            // Search Bar Injection (se não existir e tiver tabela)
            const main = document.querySelector('main');
            const table = document.querySelector('table');
            if (main && table && !document.querySelector('.search-bar')) {
                const searchphp = `
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Pesquisar...">
                    </div>
                `;
                table.parentElement.insertBefore(document.createRange().createContextualFragment(searchphp), table);
            }
        },

        setupGlobalEvents: () => {
            const nav = document.querySelector('nav');
            if (nav && !nav.querySelector('.logout-btn')) {
                const logoutLink = document.createElement('a');
                logoutLink.href = "#";
                logoutLink.innerHTML = '<i class="fas fa-sign-out-alt"></i> Sair';
                logoutLink.className = "logout-btn";
                logoutLink.onclick = (e) => {
                    e.preventDefault();
                    App.Auth.logout();
                };
                nav.appendChild(logoutLink);
            }

            // Highlight menu ativo
            const links = document.querySelectorAll('nav a');
            links.forEach(link => {
                if (link.href === window.location.href) {
                    link.classList.add('active');
                }
            });
        },

        setupSearch: (key, fields) => {
            const input = document.getElementById('searchInput');
            if (!input) return;

            input.onkeyup = (e) => {
                const term = e.target.value.toLowerCase();
                const data = App.Data.get(key);
                const filtered = data.filter(item => {
                    return fields.some(field => String(item[field]).toLowerCase().includes(term));
                });

                // Hacky: para chamar renderTable novamente. 
                // Idealmente eu teria estado local no controller, mas para MVP serve.
                // Preciso saber as colunas originais.
                // armazenar a ultima config de tabela em App.UI.lastTableConfig
                if (App.UI.lastTableConfig) {
                    App.UI._renderTableData(filtered, App.UI.lastTableConfig.key, App.UI.lastTableConfig.columns);
                }
            };
        },

        renderTable: (key, columns) => {
            App.UI.lastTableConfig = { key, columns }; // Salva config para busca
            const data = App.Data.get(key);
            App.UI._renderTableData(data, key, columns);
        },

        _renderTableData: (data, key, columns) => {
            const tbody = document.querySelector('table tbody');
            const thead = document.querySelector('table thead tr');

            if (!tbody || !thead) return;

            // Atualiza cabeçalho se necessário (opcional, mas é bom para organizar)
            thead.innerHTML = '';
            columns.forEach(col => {
                thead.innerHTML += `<th>${col.label || col}</th>`;
            });
            thead.innerHTML += '<th>Ações</th>';

            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${columns.length + 1}" style="text-align:center">Nenhum registro encontrado.</td></tr>`;
                return;
            }

            data.forEach(item => {
                let row = '<tr>';
                columns.forEach(col => {
                    let val = item[col.key || col];
                    if (col.format) val = col.format(val);
                    row += `<td>${val}</td>`;
                });
                row += `<td>
                    <button class="btn-delete" onclick="App.UI.deleteItem('${key}', ${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>`;
                row += '</tr>';
                tbody.innerHTML += row;
            });
        },

        deleteItem: (key, id) => {
            if (confirm('Tem certeza que deseja excluir este item?')) {
                App.Data.delete(key, id);
                location.reload();
            }
        },

        showModal: (title, fields, onConfirm) => {
            const overlay = document.getElementById('modalOverlay');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            const btnConfirm = document.getElementById('modalConfirm');

            modalTitle.textContent = title;
            modalBody.innerHTML = '';

            fields.forEach(field => {
                let inputphp = '';
                if (field.type === 'select') {
                    inputphp = `<select id="field_${field.name}" class="modal-input">`;
                    field.options.forEach(opt => {
                        inputphp += `<option value="${opt.value}">${opt.label}</option>`;
                    });
                    inputphp += `</select>`;
                } else {
                    inputphp = `<input type="${field.type}" id="field_${field.name}" class="modal-input" value="${field.value || ''}">`;
                }

                const group = `
                    <div class="form-group">
                        <label>${field.label}</label>
                        ${inputphp}
                    </div>
                `;
                modalBody.innerHTML += group;
            });

            overlay.classList.add('active');

            // Remove listener anterior para não duplicar
            const newBtn = btnConfirm.cloneNode(true);
            btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);

            newBtn.onclick = () => {
                const data = {};
                let isValid = true;
                fields.forEach(field => {
                    const el = document.getElementById(`field_${field.name}`);
                    if (!el.value) isValid = false;
                    data[field.name] = el.value;
                });

                if (!isValid) {
                    App.UI.showToast('Preencha todos os campos', 'error');
                    return;
                }

                onConfirm(data);
                App.UI.closeModal();
            };
        },

        closeModal: () => {
            document.getElementById('modalOverlay').classList.remove('active');
        },

        showToast: (message, type = 'success') => {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            let icon = 'check-circle';
            if (type === 'error') icon = 'exclamation-circle';
            if (type === 'info') icon = 'info-circle';

            toast.innerHTML = `<i class="fas fa-${icon}"></i> <span>${message}</span>`;

            container.appendChild(toast);

            // Trigger reflow
            void toast.offsetWidth;
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        },

        renderDashboardCharts: (products, sales) => {
            if (document.getElementById('salesChart')) {
                const canvasSales = document.getElementById('salesChart');
                new Chart(canvasSales.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: products.map(p => p.name),
                        datasets: [{
                            label: 'Estoque Atual',
                            data: products.map(p => p.stock),
                            backgroundColor: '#3498db',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } }
                });
            }

            if (document.getElementById('monthlySalesChart')) {
                const canvasMonthly = document.getElementById('monthlySalesChart');
                new Chart(canvasMonthly.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai'],
                        datasets: [{
                            label: 'Vendas',
                            data: [1200, 1900, 300, 500, 200],
                            borderColor: '#2ecc71',
                            backgroundColor: 'rgba(46, 204, 113, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } }
                });
            }

            if (document.getElementById('vendasVendedorChart')) {
                const canvasRanking = document.getElementById('vendasVendedorChart');
                const labels = window.rankingNomes || [];
                const valores = window.rankingValores || [];

                new Chart(canvasRanking.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total (R$)',
                            data: valores,
                            backgroundColor: '#4361ee',
                            borderRadius: 8
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } }
                    }
                });
            }

            if (document.getElementById('metaGaugeChart')) {
                const canvasMeta = document.getElementById('metaGaugeChart');
                const perc = window.percentualMeta || 0;

                new Chart(canvasMeta.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Atingido', 'Restante'],
                        datasets: [{
                            data: [perc, 100 - perc],
                            backgroundColor: ['#2ecc71', '#e9ecef'],
                            circumference: 180,
                            rotation: 270,
                            cutout: '80%'
                        }]
                    },
                    options: {
                        responsive: true,
                        aspectRatio: 2,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }
    }
};

// Removido reassignment para evitar duplicação de identificador
window.goToModule = (url) => window.location.href = url;

window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const main = document.querySelector('main');

    if (sidebar) {
        sidebar.classList.toggle('collapsed');
        if (main) {
            main.classList.toggle('expanded');
        }
    }
};

function viewSale(id) {
    const modal = document.getElementById("saleModal");
    const modalBody = document.getElementById("modalBody");

    if (!modal || !modalBody) return;

    modal.style.display = "block";
    modalBody.innerHTML = "<div style='text-align:center; padding:2rem;'><i class='fas fa-spinner fa-spin'></i> Carregando detalhes...</div>";

    fetch('sale_view.php?id=' + id + '&modal=1')
        .then(response => {
            if (!response.ok) throw new Error('Erro na requisição');
            return response.text();
        })
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            modalBody.innerHTML = "<p style='color:red; text-align:center'>Erro ao carregar dados.</p>";
        });
}

document.addEventListener('DOMContentLoaded', () => {
    window.App.init();
    const modal = document.getElementById("saleModal");
    if (modal) {
        const closeBtn = modal.querySelector(".close-modal");
        if (closeBtn) {
            closeBtn.onclick = () => modal.style.display = "none";
        }
        window.onclick = (event) => {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    }
});
