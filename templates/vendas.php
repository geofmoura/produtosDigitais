<?php
session_start();
error_log('Vendas.php - SESSION: ' . print_r($_SESSION, true));

if (!isset($_SESSION['usuario'])) {
    error_log('Usuário não logado, redirecionando para index.php');
    header('Location: ../index.php'); 
    exit();
}

// Conectar ao banco de dados SQLite
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../db/database.sqlite'); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro de conexão: ' . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo = 'jogo'");
$stmt->execute();
$jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca gift cards
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo = 'gift_card'");
$stmt->execute();
$giftcards = $stmt->fetchAll(PDO::FETCH_ASSOC);

function gerarNomeImagem($nomeProduto) {
    $mapeamento = [
        'Resident Evil 4' => 'residentevil4.jpg',
        'The Last of Us Part 1' => 'thelastofus.jpg',
        'League of Legends' => 'lol.jpg',
        'Minecraft' => 'minecraft.jpg',
        'Valorant' => 'valorant.jpg',
        'The Whitcher 3: Wild Hunt' => 'thewitcher.jpg', 
        'Roblox' => 'roblox.jpg',
        'Katana Zero' => 'katanazero.jpg',
        'God of War'=> 'godofwar.jpg',
        'Counter Strike 2' => 'godofwar.jpg',
        'Gift Card PSN R$100' => 'psncard.jpg',
        'Gift Card IMVU R$25' => 'imvucard.jpg',
        'Gift Card Xbox R$50' => 'xboxcard.jpg',
        'Gift Card Google Play R$50' => 'playcard.jpg',
        'Gift Card Netflix R$30' => 'netflixcard.jpg'
    ];
    
    return $mapeamento[$nomeProduto] ?? strtolower(preg_replace('/[^a-z0-9]/i', '', $nomeProduto)) . '.jpg';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Store - Jogos</title>
    <link rel="stylesheet" href="style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="vendas-page">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                <div class="d-flex align-items-center user-section">
                    <span class="me-3 user-greeting">Olá, <?php echo htmlspecialchars($_SESSION['nome'] ?? 'Usuário'); ?>!</span>
                    <a href="../server/logout.php" class="btn btn-outline-light logout-btn">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container my-5 main-content">
        <!-- Carrossel de Jogos -->
        <section class="game-carousel">
    <div class="carousel-wrapper">
        <div id="gameCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($jogos as $index => $jogo): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row game-item">
                            <div class="col-md-8 game-image-container">
                                <img src="../img/<?php echo gerarNomeImagem($jogo['nome']); ?>" 
                                     onerror="this.src='../img/default.jpg'; this.alt='Imagem não disponível'"
                                     class="d-block w-100 rounded game-image" 
                                     alt="<?php echo htmlspecialchars($jogo['nome']); ?>">
                            </div>
                            <div class="col-md-4 p-4 game-info">
                                <h2 class="game-title mb-3"><?php echo htmlspecialchars($jogo['nome']); ?></h2>
                                <p class="game-description mb-4"><?php echo htmlspecialchars($jogo['descricao']); ?></p>

                                <div class="game-purchase">
                                    <div class="price-container mb-3">
                                        <?php if ($jogo['promocao'] && $jogo['promocao'] != 'NULL'): ?>
                                            <span class="game-price-promo">R$ <?php echo number_format($jogo['preco'], 2, ',', '.'); ?></span>
                                            <span class="game-price">R$ <?php echo number_format($jogo['promocao'], 2, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="game-price">R$ <?php echo number_format($jogo['preco'], 2, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form action="../server/adicionar_carrinho.php" method="POST" class="purchase-form w-100">
                                        <input type="hidden" name="produto_id" value="<?php echo $jogo['id']; ?>">
                                        <button type="submit" class="btn btn-buy w-100">COMPRAR</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Controles do Carrossel -->
            <button class="carousel-control-prev carousel-button" type="button" data-bs-target="#gameCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next carousel-button" type="button" data-bs-target="#gameCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
            </button>
        </div>
    </div>
</section>
            
        <!-- Seção de Gift Cards - Estilo igual ao carrossel de jogos -->
<section id="giftcards-section" class="mt-5 giftcards-section">
    <h2 class="mb-4 section-title">Gift Cards</h2>
    <div class="row">
        <?php foreach ($giftcards as $giftcard): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="game-item giftcard-item">
                    <div class="game-image-container">
                        <img src="../img/<?php echo gerarNomeImagem($giftcard['nome']); ?>" 
                             onerror="this.src='../img/default.jpg'; this.alt='Imagem não disponível'"
                             class="img-fluid rounded game-image" 
                             alt="<?php echo htmlspecialchars($giftcard['nome']); ?>">
                    </div>
                    <div class="game-info p-3">
                        <h5 class="game-title"><?php echo htmlspecialchars($giftcard['nome']); ?></h5>
                        
                        <div class="game-purchase">
                            <div class="price-container mb-2">
                                <?php if ($giftcard['promocao'] && $giftcard['promocao'] != 'NULL'): ?>
                                    <span class="game-price-promo">R$ <?php echo number_format($giftcard['preco'], 2, ',', '.'); ?></span>
                                    <span class="game-price">R$ <?php echo number_format($giftcard['promocao'], 2, ',', '.'); ?></span>
                                <?php else: ?>
                                    <span class="game-price">R$ <?php echo number_format($giftcard['preco'], 2, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <form action="../server/adicionar_carrinho.php" method="POST" class="purchase-form w-100">
                                <input type="hidden" name="produto_id" value="<?php echo $giftcard['id']; ?>">
                                <button type="submit" class="btn btn-buy w-100">COMPRAR</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
    </main>

    <footer class="bg-dark text-white py-4 mt-5 site-footer">
        <div class="container text-center">
            <p class="footer-text">© <?php echo date('Y'); ?> Impact Store. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buyForms = document.querySelectorAll('form[action*="adicionar_carrinho"]');

        buyForms.forEach(form => {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                console.log('Response:', result); 
                if (result.status === 'success') {
                    // Mostrar toast/notificação
                    showToast(result.message, result.productName);
                } else {
                    alert(result.message);
                }
            });
        });

        // Função para mostrar notificação
            function showToast(message, productName) {
            let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = 'toast show';
        toast.style.backgroundColor = '#28a745';
        toast.style.color = 'white';
        toast.style.borderRadius = '5px';
        toast.style.padding = '15px';
        toast.style.marginBottom = '10px';
        toast.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.justifyContent = 'space-between';
        toast.style.minWidth = '350px'; 

        const toastContent = document.createElement('div');
        toastContent.style.flexGrow = '1';
        toastContent.innerHTML = `<strong>${productName}</strong> ${message}`;
        toast.appendChild(toastContent);

        // Botão "Ir para Carrinho"
        const cartButton = document.createElement('button');
        cartButton.innerHTML = '<i class="bi bi-cart"></i> Ir para Carrinho';
        cartButton.style.marginLeft = '15px';
        cartButton.style.padding = '5px 10px';
        cartButton.style.border = 'none';
        cartButton.style.borderRadius = '4px';
        cartButton.style.backgroundColor = '#ffffff';
        cartButton.style.color = '#28a745';
        cartButton.style.cursor = 'pointer';
        cartButton.style.fontWeight = 'bold';
        cartButton.addEventListener('click', () => {
            window.location.href = 'carrinho.php';
        });
        toast.appendChild(cartButton);

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
    });
</script>
</body>
</html>