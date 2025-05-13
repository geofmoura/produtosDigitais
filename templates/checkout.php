<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
}

if (isset($_SESSION['usuario']['nome'])) {
    $nome_usuario = $_SESSION['usuario']['nome'];
} elseif (isset($_SESSION['usuario_nome'])) {
    $nome_usuario = $_SESSION['usuario_nome'];
} elseif (is_array($_SESSION['usuario'])) {
    $nome_usuario = $_SESSION['usuario']['nome'] ?? 'Visitante';
} else {
    $nome_usuario = $_SESSION['usuario'] ?? 'Visitante';
}

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

$carrinho = $_SESSION['carrinho'] ?? [];
$exibirCarrinhoVazio = empty($carrinho);
$total = 0;

foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$exibirCarrinhoVazio) {
    $transactionStarted = false;
    try {
        if (!isset($_SESSION['usuario_id'])) {
            throw new Exception("Usuário não identificado. Faça login novamente.");
        }
        
        if (empty($_POST['email'])) {
            throw new Exception("Email para envio é obrigatório.");
        }

        $pdo->beginTransaction();
        $transactionStarted = true;

        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, data_pedido, email_envio) VALUES (?, ?, datetime('now'), ?)");
        $stmt->execute([
            $_SESSION['usuario_id'],
            $total,
            $_POST['email']
        ]);
        $pedido_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        
        foreach ($carrinho as $item) {
            $stmt->execute([$pedido_id, $item['id'], $item['quantidade'], $item['preco']]);
            
            if ($item['tipo'] === 'jogo' || $item['tipo'] === 'giftcard') {
                $codigo = generateRandomCode(16);
                $pdo->prepare("INSERT INTO codigos_ativos (pedido_id, produto_id, codigo, status) VALUES (?, ?, ?, 'ativo')")
                    ->execute([$pedido_id, $item['id'], $codigo]);
            }
        }

        $pdo->commit();
        $transactionStarted = false;

        unset($_SESSION['carrinho']);
        $_SESSION['sucesso_pedido'] = "Pedido #$pedido_id realizado com sucesso! Os produtos serão enviados para seu email.";
        header('Location: ../templates/vendas.php');
        exit();
        
    } catch (Exception $e) {
        if ($transactionStarted) {
            try {
                $pdo->rollBack();
            } catch (PDOException $rollbackException) {
                $e = new Exception($e->getMessage() . " - Falha no rollback: " . $rollbackException->getMessage(), 0, $e);
            }
        }
        
        $erro = "Erro ao finalizar pedido: " . $e->getMessage();
    }
}

function generateRandomCode($length = 16) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Impact Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="checkout-page vendas-page d-flex flex-column">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Impact Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="d-flex ms-auto align-items-center user-section">
                    <span class="me-3 user-greeting">Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</span>
                    <a href="../server/logout.php" class="btn btn-outline-light logout-btn">Sair</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container content-wrapper py-4">
        <div class="checkout-container">
            <h1 class="mb-4"><i class="bi bi-credit-card"></i> Finalizar Compra</h1>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            
            <?php if ($exibirCarrinhoVazio): ?>
                <div class="alert alert-warning text-center py-4">
                    <i class="bi bi-cart-x" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">Seu carrinho está vazio</p>
                    <a href="../templates/vendas.php" class="btn btn-primary mt-3">Voltar às compras</a>
                </div>
            <?php else: ?>
                <div class="resumo-pedido">
                    <h3 class="mb-3"><i class="bi bi-cart-check"></i> Resumo do Pedido</h3>
                    <table class="table table-dark table-borderless">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrinho as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nome']) ?></td>
                                    <td class="text-end"><?= $item['quantidade'] ?></td>
                                    <td class="text-end">R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="border-top">
                                <th colspan="2" class="text-end">Total:</th>
                                <th class="text-end">R$ <?= number_format($total, 2, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="form-checkout">
                    <h3 class="mb-3"><i class="bi bi-envelope"></i> Informações para Envio</h3>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email para recebimento</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?>">
                            <div class="form-text">Os códigos e links de download serão enviados para este email</div>
                        </div>
                        
                        <h3 class="mt-4 mb-3"><i class="bi bi-credit-card"></i> Pagamento</h3>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nome_cartao" class="form-label">Nome no Cartão</label>
                                <input type="text" class="form-control" id="nome_cartao" required>
                            </div>
                            <div class="col-md-6">
                                <label for="numero_cartao" class="form-label">Número do Cartão</label>
                                <input type="text" class="form-control" id="numero_cartao" required>
                            </div>
                            <div class="col-md-4">
                                <label for="validade" class="form-label">Validade</label>
                                <input type="text" class="form-control" id="validade" placeholder="MM/AA" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" required>
                            </div>
                            <div class="col-md-4">
                                <label for="parcelas" class="form-label">Parcelas</label>
                                <select class="form-select" id="parcelas">
                                    <option value="1">1x R$ <?= number_format($total, 2, ',', '.') ?></option>
                                    <?php if ($total > 100): ?>
                                        <option value="2">2x R$ <?= number_format($total/2, 2, ',', '.') ?></option>
                                        <option value="3">3x R$ <?= number_format($total/3, 2, ',', '.') ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i> Códigos referente aos cartões presentes serão enviados por email 
                            imediatamente após a confirmação do pagamento.
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="carrinho.php" class="btn btn-outline-light"><i class="bi bi-arrow-left"></i> Voltar ao Carrinho</a>
                            <button type="submit" class="btn btn-success">Confirmar Pedido <i class="bi bi-check-circle"></i></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="site-footer">
        <div class="container-fluid text-center">
            <p class="footer-text mb-0">© <?php echo date('Y'); ?> Impact Store. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('numero_cartao').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').replace(/(\d{4})(?=\d)/g, '$1 ');
        });
        
        document.getElementById('validade').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').replace(/(\d{2})(?=\d)/g, '$1/');
        });
        
        document.getElementById('cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 4);
        });
    </script>
</body>
</html>