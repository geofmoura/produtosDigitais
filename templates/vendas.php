<?php
session_start();

// Conectar ao banco de dados SQLite
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../db/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro de conexão: ' . $e->getMessage());
}

// Verifica se o usuário está logado
if (!isset($_SESSION['nome'])) {
    header('Location: ../index.php');
    exit();
}

// Busca apenas os jogos (tipo_produto = 'jogo')
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo_produto = 'jogo'");
$stmt->execute();
$jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca gift cards
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo_produto = 'gift_card'");
$stmt->execute();
$giftcards = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Store - Jogos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1b2838;
            color: #ffffff;
        }
        .game-header {
            background-color: #171a21;
            padding: 20px;
        }
        .game-carousel {
            background-color: #2a475e;
            border-radius: 5px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .carousel-item {
            padding: 20px;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0,0,0,0.5);
            border-radius: 50%;
            padding: 20px;
        }
        .game-price {
            font-size: 1.5rem;
            color: #66c0f4;
        }
        .game-price-promo {
            text-decoration: line-through;
            color: #8f98a0;
            font-size: 1rem;
        }
        .btn-buy {
            background-color: #5ba32b;
            border: none;
            padding: 10px 25px;
            font-weight: bold;
        }
        .btn-buy:hover {
            background-color: #6bc832;
        }
        .gift-card {
            background-color: #2a475e;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .gift-card:hover {
            transform: translateY(-5px);
        }
        .navbar {
            background-color: #171a21;
        }
        .navbar-brand {
            color: #fff;
            font-weight: bold;
        }
        .navbar-nav .nav-link {
            color: #c7d5e0;
        }
        .navbar-nav .nav-link:hover {
            color: #fff;
        }
        .logout-btn {
            color: #ff5722;
            border: 1px solid #ff5722;
        }
        .logout-btn:hover {
            background-color: #ff5722;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">Impact Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Jogos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#giftcards-section">Gift Cards</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrinho.php">Carrinho</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="me-3">Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</span>
                    <a href="logout.php" class="btn btn-outline-light logout-btn">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <!-- Carrossel de Jogos -->
        <section class="game-carousel">
            <div id="gameCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($jogos as $index => $jogo): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Imagem do jogo (substituir depois) -->
                                    <img src="../assets/jogos/<?php echo strtolower(str_replace(' ', '-', $jogo['nome'])); ?>.jpg" 
                                         class="d-block w-100 rounded" 
                                         alt="<?php echo htmlspecialchars($jogo['nome']); ?>">
                                </div>
                                <div class="col-md-4 p-4">
                                    <h2><?php echo htmlspecialchars($jogo['nome']); ?></h2>
                                    <p><?php echo htmlspecialchars($jogo['descricao']); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <?php if ($jogo['promocao'] && $jogo['promocao'] != 'NULL'): ?>
                                                <span class="game-price-promo">R$ <?php echo number_format($jogo['preco'], 2, ',', '.'); ?></span>
                                                <span class="game-price">R$ <?php echo number_format($jogo['promocao'], 2, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="game-price">R$ <?php echo number_format($jogo['preco'], 2, ',', '.'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <form action="../server/adicionar_carrinho.php" method="POST">
                                            <input type="hidden" name="produto_id" value="<?php echo $jogo['id']; ?>">
                                            <button type="submit" class="btn btn-buy">Comprar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Controles do Carrossel -->
                <button class="carousel-control-prev" type="button" data-bs-target="#gameCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#gameCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
            </div>
        </section>

        <!-- Seção de Gift Cards -->
        <section id="giftcards-section" class="mt-5">
            <h2 class="mb-4">Gift Cards</h2>
            <div class="row" id="giftcards-container">
                <?php foreach ($giftcards as $giftcard): ?>
                    <div class="col-md-3 mb-4">
                        <div class="gift-card">
                            <img src="../assets/giftcards/<?php echo strtolower(str_replace(' ', '-', $giftcard['nome'])); ?>.jpg" 
                                class="img-fluid" 
                                alt="<?php echo htmlspecialchars($giftcard['nome']); ?>">
                            <div class="p-3">
                                <h5><?php echo htmlspecialchars($giftcard['nome']); ?></h5>
                                <p class="game-price">R$ <?php echo number_format($giftcard['preco'], 2, ',', '.'); ?></p>
                                <form action="../server/adicionar_carrinho.php" method="POST">
                                    <input type="hidden" name="produto_id" value="<?php echo $giftcard['id']; ?>">
                                    <button type="submit" class="btn btn-buy w-100">Comprar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>© <?php echo date('Y'); ?> Impact Store. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>