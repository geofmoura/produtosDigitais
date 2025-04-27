<?php
session_start(); 
require_once __DIR__ . '/config/database.php';

$pdo->exec("INSERT OR IGNORE INTO produtos (nome, preco) VALUES 
    ('Notebook', 2500.00),
    ('Mouse', 50.00),
    ('Teclado', 120.00)");

$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Loja</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .produto { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Produtos Disponíveis</h1>
    <?php foreach ($produtos as $produto): ?>
        <div class="produto">
            <h3><?= $produto['nome'] ?></h3>
            <p>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>
            <form action="server/adicionar_carrinho.php" method="POST">
                <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                <button type="submit">Adicionar ao Carrinho</button>
            </form>
        </div>
    <?php endforeach; ?>
    <a href="templates/carrinho.php">Ver Carrinho (<?= count($_SESSION['carrinho'] ?? []) ?>)</a>
</body>
</html>