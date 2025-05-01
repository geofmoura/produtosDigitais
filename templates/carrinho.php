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
            min-height: 100%;
            height: auto;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .content-wrapper {
            flex: 1;
            padding-bottom: 20px;
        }
        
        .site-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,0.1);
            background-color: #1a1a1a;
            margin-top: auto;
        }
        
        .d-none {
            display: none !important;
        }

        .shadow-table {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            overflow: hidden; 
        }
        
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
    
    <div class="table-responsive shadow p-3 mb-5 bg-dark rounded"> 
    <table class="table table-dark table-striped table-hover">
        <thead class="table-secondary">
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
                    <td colspan="5" class="text-center py-4 text-light"> 
                        <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Seu carrinho está vazio</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <?php if (!$exibirCarrinhoVazio): ?>
                <tr class="table-active"> 
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td colspan="2"><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td colspan="5" class="text-end bg-secondary bg-opacity-10"> 
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

        adaptTableForMobile();
        window.addEventListener('resize', adaptTableForMobile);
    });
</script>
</body>
</html>