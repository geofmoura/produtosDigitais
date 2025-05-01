<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

$nome_usuario = $_SESSION['usuario'] ?? 'Visitante';
$rootDir = dirname(__DIR__);
$dbPath = $rootDir . '/db/database.sqlite';

if (!file_exists($dbPath)) {
    die("Erro: Arquivo do banco de dados não encontrado em: " . $dbPath);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover'])) {
    $produtoId = (int)$_POST['produto_id'];
    
    if (isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $key => $item) {
            if ($item['id'] == $produtoId) {
                unset($_SESSION['carrinho'][$key]);
                $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
                $_SESSION['sucesso'] = "O produto \"".htmlspecialchars($item['nome'])."\" foi removido do carrinho";
                break;
            }
        }
    }
}

$carrinho = $_SESSION['carrinho'] ?? [];
$exibirCarrinhoVazio = empty($carrinho);
$total = 0;

$sucesso = $_SESSION['sucesso'] ?? null;
$exibirCarrinhoVazio = empty($carrinho);
unset($_SESSION['sucesso']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
    <link rel="stylesheet" href="./style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
        }
        
        .content-wrapper {
            flex: 1;
            padding-bottom: 20px;
        }
        
        .site-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            background-color: #1a1a1a;
        }
        
        .d-none {
            display: none !important;
        }

        .shadow-table {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            overflow: hidden; /* mantém o border-radius visível */
        }
        
        /* Estilos para a tabela responsiva */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 768px) {
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
            }
            
            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: 1px solid #dee2e6;
            }
            
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: calc(50% - 1rem);
                padding-right: 1rem;
                text-align: left;
                font-weight: bold;
                white-space: nowrap;
            }
            
            .table td:nth-of-type(1):before { content: "Produto"; }
            .table td:nth-of-type(2):before { content: "Preço Unitário"; }
            .table td:nth-of-type(3):before { content: "Quantidade"; }
            .table td:nth-of-type(4):before { content: "Subtotal"; }
            .table td:nth-of-type(5):before { content: "Ação"; }
            
            .table td:last-child {
                border-bottom: 0;
            }
            
            .table tfoot tr {
                display: table-row;
            }
            
            .table tfoot td {
                display: table-cell;
                text-align: right;
                padding-left: 0.75rem;
            }
            
            .table tfoot td::before {
                display: none;
            }
        }
    </style>
</head>
<body class="carrinho d-flex flex-column">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Impact Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="d-flex ms-auto align-items-center user-section">
                    <span class="me-3 user-greeting">Olá, <?php echo $nome_usuario; ?>!</span>
                    <a href="../server/logout.php" class="btn btn-outline-light logout-btn">Sair</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="table-responsive shadow p-3 mb-5 bg-dark rounded"> <!-- bg-dark aqui -->
    <table class="table table-dark table-striped table-hover"> <!-- table-dark e table-hover -->
        <thead class="table-secondary"> <!-- cabeçalho em tom mais claro -->
            <tr>
                <th>Produto</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$exibirCarrinhoVazio): ?>
                <?php foreach ($carrinho as $item): 
                    $subtotal = $item['preco'] * $item['quantidade'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome']) ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="produto_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="remover" class="btn btn-outline-danger btn-sm">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-light"> <!-- text-light -->
                        <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Seu carrinho está vazio</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <?php if (!$exibirCarrinhoVazio): ?>
                <tr class="table-active"> <!-- destaque para o total -->
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td colspan="2"><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td colspan="5" class="text-end bg-secondary bg-opacity-10"> <!-- fundo sutil -->
                    <div class="d-flex justify-content-between py-2">
                        <a href="../templates/vendas.php" class="btn btn-outline-primary">Continuar Comprando</a>
                        <?php if (!$exibirCarrinhoVazio): ?>
                            <a href="checkout.php" class="btn btn-outline-success">Finalizar Compra</a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled>Finalizar Compra</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

    <footer class="bg-dark text-white py-4 mt-auto site-footer">
        <div class="container text-center">
            <p class="footer-text mb-0">© <?php echo date('Y'); ?> Impact Store. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Configurações comuns
        const animationStyles = `
            @keyframes slideIn {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes slideInUp {
                from { transform: translateY(100%); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes slideOut {
                to { transform: translateY(20px); opacity: 0; }
            }
            @keyframes slideOutDown {
                to { transform: translateY(100%); opacity: 0; }
            }
        `;
        
        // Adiciona os estilos de animação
        const style = document.createElement('style');
        style.textContent = animationStyles;
        document.head.appendChild(style);

        // Cria containers de toast se não existirem
        function initContainers() {
            const positions = ['top', 'bottom'];
            positions.forEach(position => {
                const id = `toast-container-${position}`;
                if (!document.getElementById(id)) {
                    const container = document.createElement('div');
                    container.id = id;
                    container.style.position = 'fixed';
                    container.style[position] = '20px';
                    container.style.right = '20px';
                    container.style.zIndex = '9999';
                    container.style.display = 'flex';
                    container.style.flexDirection = 'column';
                    container.style.gap = '10px';
                    document.body.appendChild(container);
                }
            });
        }

        // Função para criar toasts
        function showToast(message, options = {}) {
            const defaults = {
                position: 'top',
                bgColor: '#dc3545',
                textColor: 'white',
                autoClose: true,
                closeButton: true,
                animationIn: 'slideIn',
                animationOut: 'slideOut'
            };
            
            const config = {...defaults, ...options};
            
            const toast = document.createElement('div');
            toast.className = 'toast show';
            toast.style.backgroundColor = config.bgColor;
            toast.style.color = config.textColor;
            toast.style.borderRadius = '5px';
            toast.style.padding = '15px 40px 15px 15px';
            toast.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            toast.style.minWidth = '300px';
            toast.style.position = 'relative';
            toast.style.animation = `${config.animationIn} 0.3s ease-out`;
            
            const toastContent = document.createElement('div');
            toastContent.innerHTML = message;
            toast.appendChild(toastContent);

            if (config.closeButton) {
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '&times;';
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '5px';
                closeBtn.style.right = '10px';
                closeBtn.style.background = 'transparent';
                closeBtn.style.border = 'none';
                closeBtn.style.color = config.textColor;
                closeBtn.style.fontSize = '20px';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.padding = '0';
                closeBtn.style.lineHeight = '1';
                closeBtn.addEventListener('click', () => {
                    closeToast(toast, config.animationOut);
                });
                toast.appendChild(closeBtn);
            }

            const container = document.getElementById(`toast-container-${config.position}`);
            container.appendChild(toast);

            if (config.autoClose) {
                setTimeout(() => {
                    closeToast(toast, config.animationOut);
                }, 5000);
            }

            return toast;
        }

        function closeToast(toast, animation) {
            toast.style.animation = `${animation} 0.3s ease-out forwards`;
            setTimeout(() => toast.remove(), 300);
        }

        // Inicializa os containers
        initContainers();

        // 1. Tratamento para remoção de produtos
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            const message = successAlert.textContent.trim();
            const productNameMatch = message.match(/"(.*?)"/);
            const productName = productNameMatch ? productNameMatch[1] : 'Produto';
            
            showToast(
                `<strong>${productName}</strong> - ${message}`,
                { bgColor: '#dc3545' } // Vermelho para remoção
            );
            
            setTimeout(() => successAlert.remove(), 100);
        }

        // 2. Tratamento para carrinho vazio
        const emptyCartAlert = document.querySelector('.alert-info');
        if (emptyCartAlert && emptyCartAlert.textContent.includes('vazio')) {
            showToast(
                '<strong>Carrinho vazio</strong> - Seu carrinho de compras está vazio.',
                {
                    position: 'bottom',
                    bgColor: '#ffc107', // Amarelo para alerta
                    textColor: '#000',
                    animationIn: 'slideInUp',
                    animationOut: 'slideOutDown'
                }
            );
            
            setTimeout(() => emptyCartAlert.remove(), 100);
        }

        // Adapta a tabela para mobile
        function adaptTableForMobile() {
            const isMobile = window.innerWidth <= 768;
            const tds = document.querySelectorAll('.table td');
            
            tds.forEach(td => {
                if (isMobile) {
                    const headerText = td.closest('table')
                                      .querySelector('th:nth-child(' + (td.cellIndex + 1) + ')')
                                      ?.textContent || '';
                    td.setAttribute('data-label', headerText);
                } else {
                    td.removeAttribute('data-label');
                }
            });
        }
        
        // Executa na carga e em redimensionamentos
        adaptTableForMobile();
        window.addEventListener('resize', adaptTableForMobile);
    });
    </script>
</body>
</html>