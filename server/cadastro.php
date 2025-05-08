<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

try {
    $required = ['nome', 'email', 'senha', 'confirmar_senha'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $response['message'] = 'Todos os campos são obrigatórios';
            echo json_encode($response);
            exit();
        }
    }

    if ($_POST['senha'] !== $_POST['confirmar_senha']) {
        $response['message'] = 'As senhas não coincidem';
        echo json_encode($response);
        exit();
    }

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $dbPath = __DIR__ . '/../../db/database.sqlite';
    $dbDir = dirname($dbPath);
    
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        senha TEXT NOT NULL,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $response['message'] = 'Este email já está cadastrado';
        echo json_encode($response);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    if ($stmt->execute([$nome, $email, $senha])) {
        $response['success'] = true;
        $response['message'] = 'Cadastro realizado com sucesso!';
    } else {
        $response['message'] = 'Erro ao cadastrar usuário';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erro no sistema: ' . $e->getMessage();
}

echo json_encode($response);
?>