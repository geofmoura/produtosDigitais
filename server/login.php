<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

try {
    if (empty($_POST['email']) || empty($_POST['senha'])) {
        $response['message'] = 'Preencha todos os campos';
        echo json_encode($response);
        exit();
    }

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $dbPath = __DIR__ . '/../../db/database.sqlite';
    
    if (!file_exists($dbPath)) {
        $response['message'] = 'Banco de dados não encontrado';
        echo json_encode($response);
        exit();
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $response['message'] = 'Usuário não encontrado';
        echo json_encode($response);
        exit();
    }

    if ($senha === $usuario['senha']) {
        unset($usuario['senha']);
        $_SESSION['usuario'] = $usuario;
        $response['success'] = true;
        $response['redirect'] = '../templates/vendas.php';
    } else {
        $response['message'] = 'Senha incorreta';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erro no sistema: ' . $e->getMessage();
}

echo json_encode($response);
?>