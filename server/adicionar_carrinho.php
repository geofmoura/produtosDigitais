<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}

// Verificar se foi enviado o ID do produto
if (!isset($_POST['produto_id']) || empty($_POST['produto_id'])) {
    header('Location: ../templates/vendas.php');
    exit();
}

$produto_id = (int)$_POST['produto_id'];
$usuario_id = $_SESSION['id'];

// Conectar ao banco de dados
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../db/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro de conexão: ' . $e->getMessage());
}

// Verificar se o produto existe
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    $_SESSION['mensagem'] = "Produto não encontrado!";
    header('Location: ../templates/vendas.php');
    exit();
}

// Inicializar o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Adicionar ao carrinho (simplificado)
$_SESSION['carrinho'][] = [
    'id' => $produto['id'],
    'nome' => $produto['nome'],
    'preco' => $produto['promocao'] && $produto['promocao'] != 'NULL' ? $produto['promocao'] : $produto['preco'],
    'quantidade' => 1
];

$_SESSION['mensagem'] = "Produto adicionado ao carrinho!";
header('Location: ../templates/vendas.php');
exit();
?>