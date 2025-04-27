<?php
session_start();
require_once __DIR__ . '/../config/database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover'])) {
    $produtoId = $_POST['produto_id'];
    if (isset($_SESSION['carrinho'][$produtoId])) {
        unset($_SESSION['carrinho'][$produtoId]);
    }
}

$carrinho = $_SESSION['carrinho'] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carrinho</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Seu Carrinho</h1>
    <?php if (empty($carrinho)): ?>
        <p>Carrinho vazio!</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Produto</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($carrinho as $id => $item): 
                $subtotal = $item['preco'] * $item['quantidade'];
                $total += $subtotal;
            ?>
                <tr>
                    <td><?= $item['nome'] ?></td>
                    <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                    <td><?= $item['quantidade'] ?></td>
                    <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="produto_id" value="<?= $id ?>">
                            <button type="submit" name="remover">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td colspan="2">R$ <?= number_format($total, 2, ',', '.') ?></td>
            </tr>
        </table>
    <?php endif; ?>
    <a href="../index.php">Continuar Comprando</a>
</body>
</html>