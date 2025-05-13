<?php
// Arquivo config/database.php - modificado para usar sua estrutura

function conectarBD() {
    // Caminho absoluto para o banco de dados
    $dbPath = __DIR__ . '/../db/database.sqlite';
    
    // Verificar se o diretório db existe
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    try {
        // Conectar ao banco
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar tabelas se não existirem
        criarTabelas($pdo);
        
        return $pdo;
    } catch (PDOException $e) {
        die('Erro de conexão: ' . $e->getMessage());
    }
}

function criarTabelas($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS produtos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            preco REAL NOT NULL,
            descricao TEXT,
            promocao REAL,
            tipo TEXT NOT NULL,
            imagem TEXT
        );

        CREATE TABLE IF NOT EXISTS pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            total REAL NOT NULL,
            data_pedido TEXT NOT NULL,
            email_envio TEXT NOT NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        );

        CREATE TABLE IF NOT EXISTS itens_pedido (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            produto_id INTEGER NOT NULL,
            quantidade INTEGER NOT NULL DEFAULT 1,
            preco_unitario REAL NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
            FOREIGN KEY (produto_id) REFERENCES produtos(id)
        );

        CREATE TABLE IF NOT EXISTS codigos_ativos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            produto_id INTEGER NOT NULL,
            codigo TEXT NOT NULL,
            status TEXT DEFAULT 'ativo',
            data_geracao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_utilizacao DATETIME,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
            FOREIGN KEY (produto_id) REFERENCES produtos(id)
        );

        CREATE TABLE IF NOT EXISTS pagamentos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            metodo TEXT NOT NULL,
            status TEXT DEFAULT 'pendente',
            parcelas INTEGER DEFAULT 1,
            valor_total REAL NOT NULL,
            valor_parcela REAL NOT NULL,
            data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
            referencia_externa TEXT,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        );

        CREATE TABLE IF NOT EXISTS historico_pagamentos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pagamento_id INTEGER NOT NULL,
            status_anterior TEXT NOT NULL,
            status_novo TEXT NOT NULL,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            descricao TEXT,
            FOREIGN KEY (pagamento_id) REFERENCES pagamentos(id)
        );
    ");
}