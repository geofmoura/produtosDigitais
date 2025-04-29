<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, faça login primeiro']);
    exit();
}

// Validar dados do formulário
if (!isset($_POST['produto_id']) || !is_numeric($_POST['produto_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Produto inválido']);
    exit();
}

// Conexão com o banco de dados
try {
    $dbPath = __DIR__ . '/../db/database.sqlite';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco de dados']);
    exit();
}

// Buscar produto no banco
$produto_id = (int)$_POST['produto_id'];
$stmt = $pdo->prepare("SELECT id, nome, preco, promocao, tipo FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo json_encode(['status' => 'error', 'message' => 'Produto não encontrado']);
    exit();
}

// Inicializar carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Verificar se o produto já está no carrinho
$encontrado = false;
foreach ($_SESSION['carrinho'] as &$item) {
    if ($item['id'] === $produto['id']) {
        $item['quantidade']++;
        $encontrado = true;
        break;
    }
}

// Adicionar novo item se não existir
if (!$encontrado) {
    $_SESSION['carrinho'][] = [
        'id' => $produto['id'],
        'nome' => $produto['nome'],
        'preco' => !empty($produto['promocao']) ? $produto['promocao'] : $produto['preco'],
        'quantidade' => 1,
        'imagem' => ($produto['tipo'] === 'jogo' ? 'jogos/' : 'giftcards/') . 
                   strtolower(str_replace(' ', '-', $produto['nome'])) . '.jpg'
    ];
}

// Retornar resposta JSON
echo json_encode([
    'status' => 'success',
    'message' => 'Produto adicionado ao carrinho!',
    'productName' => $produto['nome']
]);
exit();
?>