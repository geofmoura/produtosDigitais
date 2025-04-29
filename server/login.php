<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);


try {
    $dbPath = __DIR__ . '/../db/database.sqlite'; 
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit();
}


$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha email e senha']);
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    exit();
}

if ($senha !== $usuario['senha']) {
    echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
    exit();
}

$_SESSION['usuario'] = [
    'id' => $usuario['id'],
    'nome' => $usuario['nome'],
    'email' => $usuario['email']
];

echo json_encode([
    'success' => true,
    'message' => 'Login OK!',
    'redirect' => '/templates/vendas.php' 
]);
?>