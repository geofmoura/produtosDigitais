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

    require_once __DIR__ . '/../config/database.php';
    $pdo = conectarBD();

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
