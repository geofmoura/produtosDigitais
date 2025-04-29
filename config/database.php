<?php

$dbPath = realpath(__DIR__ . '/db/database.sqlite');

if(!file_exists($dbPath)) {
    die("Arquivo do banco de dados não encontrado m: " . $dbPath);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
} catch (PDOException $e) {
    die('Erro de conexão: ' . $e->getMessage());
}

$pdo = new PDO('sqlite:' . $dbPath);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        senha TEXT NOT NULL
    )
");