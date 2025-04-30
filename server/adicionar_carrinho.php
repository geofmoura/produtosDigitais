<?php
session_start();
header('Content-Type: application/json');

if(session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 dia
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

error_log('Sessão no adicionar_carrinho: ' . print_r($_SESSION, true));

if (!isset($_POST['produto_id']) || !is_numeric($_POST['produto_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Produto inválido']);
    exit();
}

try {
    $dbPath = __DIR__ . '/../db/database.sqlite';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco de dados']);
    exit();
}

$produto_id = (int)$_POST['produto_id'];
$stmt = $pdo->prepare("SELECT id, nome, preco, promocao, tipo FROM produtos WHERE id = ?");
$stmt->execute([$produto_id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo json_encode(['status' => 'error', 'message' => 'Produto não encontrado']);
    exit();
}

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$encontrado = false;
foreach ($_SESSION['carrinho'] as &$item) {
    if ($item['id'] === $produto['id']) {
        $item['quantidade']++;
        $encontrado = true;
        break;
    }
}

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

echo json_encode([
    'status' => 'success',
    'message' => 'Produto adicionado ao carrinho!',
    'productName' => $produto['nome']
]);
exit();
?>