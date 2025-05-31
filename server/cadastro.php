<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => '', 'debug' => []];

try {
    // Verificação dos campos
    $required = ['nome', 'email', 'senha', 'confirmar_senha'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $response['message'] = 'Todos os campos são obrigatórios';
            echo json_encode($response);
            exit();
        }
    }

    // Validações
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Email inválido';
        echo json_encode($response);
        exit();
    }

    if ($_POST['senha'] !== $_POST['confirmar_senha']) {
        $response['message'] = 'As senhas não coincidem';
        echo json_encode($response);
        exit();
    }

    // Preparação dos dados
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Conexão com o banco
    require_once __DIR__ . '/../config/database.php';
    $pdo = conectarBD();
    $response['debug']['db_connection'] = 'OK';

    // Verifica se email existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $response['message'] = 'Este email já está cadastrado';
        echo json_encode($response);
        exit();
    }

    // Insere novo usuário
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $success = $stmt->execute([$nome, $email, $senha_hash]);
    
    if ($success) {
        $response['success'] = true;
        $response['message'] = 'Cadastro realizado com sucesso!';
        $response['debug']['last_insert_id'] = $pdo->lastInsertId();
    } else {
        $response['message'] = 'Erro ao cadastrar usuário';
        $response['debug']['error_info'] = $stmt->errorInfo();
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro no sistema';
    $response['debug']['error'] = $e->getMessage();
    error_log('Erro no cadastro: ' . $e->getMessage());
}

echo json_encode($response);