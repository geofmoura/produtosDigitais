# Impact Store

Loja online de produtos digitais desenvolvida com HTML, CSS, JavaScript, PHP e SQLite3.

## O que faz

- Login e cadastro de usuários
- Catálogo de produtos com busca
- Carrinho de compras
- Checkout com simulação de pagamento
- Download de produtos após compra

## Tecnologias

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Banco**: SQLite3

## Como usar

1. Coloque os arquivos no servidor (XAMPP, WAMP, etc.)
2. Acesse pelo navegador
3. Cadastre-se ou faça login
4. Navegue pelos produtos
5. Adicione ao carrinho
6. Finalize a compra

## Estrutura dos arquivos

```
IMPACTSTORE
├── config
│   └── database.php
├── db
│   └── database.sqlite
├── fonts
├── img
├── videos
├── server
│   ├── adicionar_carrinho.php
│   ├── cadastro.php
│   ├── login.php
│   ├── logout.php
│   ├── protection.php
├── templates
│   ├── carrinho.php
│   ├── checkout.php
│   ├── script.js
│   ├── style.css
│   └── vendas.php
├── index.php
└── README.md

```

## Funcionalidades principais

- **Login/Cadastro**: Dados salvos no banco SQLite
- **Produtos**: Busca e visualização
- **Carrinho**: Adicionar/remover produtos
- **Pagamento**: Simulação com dados de cartão
- **Download**: Acesso aos arquivos após compra

## Para testar

Use qualquer número de cartão para simular o pagamento.

---

Projeto de e-commerce para produtos digitais com todas as funcionalidades básicas de uma loja online.
