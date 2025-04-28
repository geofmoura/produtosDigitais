<?php
session_start();
header('Content-Type: application/json');

// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log para depuração
$log = ['status' => 'iniciando', 'dados' => []];

// Conectar ao banco de dados
try {
    $dbPath = __DIR__ . '/../db/database.sqlite';
    $log['database_path'] = $dbPath;
    
    if (!file_exists($dbPath)) {
        $log['erro'] = 'Arquivo do banco de dados não encontrado';
        echo json_encode(['success' => false, 'message' => 'Arquivo de banco de dados não encontrado', 'log' => $log]);
        exit();
    }
    
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $log['database_connection'] = 'conectado';
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
    if (!$stmt->fetch()) {
        // Criar tabela se não existir
        $pdo->exec("CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            senha TEXT NOT NULL,
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        $log['table_created'] = true;
    } else {
        $log['table_exists'] = true;
    }
    
} catch (PDOException $e) {
    $log['erro_db'] = $e->getMessage();
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados: ' . $e->getMessage(), 'log' => $log]);
    exit();
}

// Receber dados do formulário
$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$log['dados'] = ['nome' => $nome, 'email' => $email, 'senha_length' => strlen($senha)];

if (empty($nome) || empty($email) || empty($senha)) {
    $log['validation'] = 'campos vazios';
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios', 'log' => $log]);
    exit();
}

// Verificar se o email já está em uso
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $emailExiste = (bool)$stmt->fetchColumn();
    $log['email_exists'] = $emailExiste;

    if ($emailExiste) {
        echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado', 'log' => $log]);
        exit();
    }
} catch (PDOException $e) {
    $log['erro_verificar_email'] = $e->getMessage();
    echo json_encode(['success' => false, 'message' => 'Erro ao verificar email: ' . $e->getMessage(), 'log' => $log]);
    exit();
}

try {
    // Inserir novo usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $result = $stmt->execute([$nome, $email, $senha]);
    $log['insert_result'] = $result;
    $log['user_id'] = $pdo->lastInsertId();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso!', 'log' => $log]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao inserir usuário', 'log' => $log]);
    }
} catch (PDOException $e) {
    $log['erro_inserir'] = $e->getMessage();
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage(), 'log' => $log]);
}
?>