<?php
session_start();
error_log('Login script iniciado');
error_log('POST: ' . print_r($_POST, true));
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $dbPath = __DIR__ . '/../db/database.sqlite'; 
    error_log('Tentando conectar ao banco: ' . $dbPath);
    
    if (!file_exists($dbPath)) {
        error_log('Arquivo do banco de dados não encontrado: ' . $dbPath);
        echo json_encode(['success' => false, 'message' => 'Banco de dados não encontrado']);
        exit();
    }
    
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log('Conexão com banco de dados estabelecida');
} catch (PDOException $e) {
    error_log('Erro de conexão com banco: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit();
}

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Preencha email e senha']);
    exit();
}

try {
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

    // Armazenar informações de usuário na sessão
    $_SESSION['usuario'] = $usuario['nome'];
    $_SESSION['id'] = $usuario['id'];
    $_SESSION['email'] = $usuario['email'];
    
    error_log('Sessão criada: ' . print_r($_SESSION, true));

    echo json_encode([
        'success' => true,
        'message' => 'Login OK!',
        'redirect' => '../templates/vendas.php' 
    ]);
} catch (Exception $e) {
    error_log('Erro ao processar login: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao processar login: ' . $e->getMessage()]);
}
?>