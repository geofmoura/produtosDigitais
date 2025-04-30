<?php
session_start();


if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover'])) {
    $produtoId = (int)$_POST['produto_id'];
    
    if (isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $key => $item) {
            if ($item['id'] == $produtoId) {
                unset($_SESSION['carrinho'][$key]);
                $_SESSION['carrinho'] = array_values($_SESSION['carrinho']); // Reindexar array
                $_SESSION['sucesso'] = "Produto removido do carrinho";
                break;
            }
        }
    }
}

$carrinho = $_SESSION['carrinho'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
    <link rel="stylesheet" href="templates/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .table { background-color: white; }
        .btn-continuar { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Seu Carrinho</h1>
        
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success"><?= $_SESSION['sucesso'] ?></div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>
        
        <?php if (empty($carrinho)): ?>
            <div class="alert alert-info">Seu carrinho está vazio!</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço Unitário</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                        <button type="submit" name="remover" class="btn btn-danger btn-sm">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td colspan="2"><strong>R$ <?= number_format($total, 2, ',', '.') ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="../templates/vendas.php" class="btn btn-primary">Continuar Comprando</a>
            <?php if (!empty($carrinho)): ?>
                <a href="checkout.php" class="btn btn-success">Finalizar Compra</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>