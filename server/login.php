<?php
session_start();
header('Content-Type: application/json');

// Conectar ao banco de dados
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../db/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
    exit();
}

// Receber dados do formulário
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios']);
    exit();
}

// Verificar se o usuário existe
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
    exit();
}

// Login bem-sucedido
$_SESSION['id'] = $usuario['id'];
$_SESSION['nome'] = $usuario['nome'];
$_SESSION['email'] = $usuario['email'];

header('Location: ../templates/vendas.php');
exit();
?>