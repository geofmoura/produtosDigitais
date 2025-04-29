<?php
session_start();
header('Content-Type: application/json');

// Debug (mostra erros)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1️⃣ Conectar ao banco de dados SQLite
try {
    $dbPath = __DIR__ . '/../db/database.sqlite'; // Ajuste o caminho se necessário
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit();
}

// 2️⃣ Receber dados do POST (verifique se estão chegando)
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha email e senha']);
    exit();
}

// 3️⃣ Buscar usuário no banco
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    exit();
}

// 4️⃣ Verificar senha (TEXTO PURO - só para teste!)
if ($senha !== $usuario['senha']) {
    echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
    exit();
}

// 5️⃣ Criar sessão (verifique se sessions estão ativas no PHP)
$_SESSION['usuario'] = [
    'id' => $usuario['id'],
    'nome' => $usuario['nome'],
    'email' => $usuario['email']
];

// 6️⃣ Resposta de sucesso
echo json_encode([
    'success' => true,
    'message' => 'Login OK!',
    'redirect' => '/templates/vendas.php' // Ajuste o redirecionamento
]);
?>