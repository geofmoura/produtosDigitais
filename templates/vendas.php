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
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo = 'jogo'");
$stmt->execute();
$jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca gift cards
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo = 'gift_card'");
$stmt->execute();
$giftcards = $stmt->fetchAll(PDO::FETCH_ASSOC);

function gerarNomeImagem($nomeProduto) {
    // Remove espaços e caracteres especiais
    $nome = preg_replace('/[^a-z0-9]/i', '', $nomeProduto);
    $nome = strtolower($nome);
    
    // Mapeamento manual se necessário (adicione os seus casos específicos)
    $mapeamento = [
        'Resident Evil 4' => 'residentevil.jpg',
        'Valorant' => 'valorant.jpg',
        'Minecraft' => 'minecraft.jpg',
        'League of Legends' => 'lol.jpg',
        'The Last of Us' => 'thelastofus.jpg'
        
    ];
    
    return $mapeamento[$nome] ?? $nome . '.jpg';
}
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
        .toast {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
    .toast.show {   
    opacity: 1;
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
                                    <!-- Imagem do jogo -->
                                    <img src="../img<?php echo gerarNomeImagem($jogo['nome']); ?>" 
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
                        <img src="../img<?php echo gerarNomeImagem($giftcard['nome']); ?>" 
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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar event listeners a todos os forms de compra
    const buyForms = document.querySelectorAll('form[action*="adicionar_carrinho"]');
    
    buyForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Mostrar toast/notificação
                    showToast(result.message, result.productName);
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao adicionar ao carrinho');
            }
        });
    });
    
    // Função para mostrar notificação
    function showToast(message, productName) {
        // Criar elemento de toast se não existir
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = 'toast show';
        toast.style.backgroundColor = '#5ba32b';
        toast.style.color = 'white';
        toast.style.padding = '15px';
        toast.style.borderRadius = '5px';
        toast.style.marginBottom = '10px';
        toast.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.justifyContent = 'space-between';
        toast.style.minWidth = '300px';
        
        toast.innerHTML = `
            <div>
                <strong>${productName}</strong>
                <div>${message}</div>
            </div>
            <a href="carrinho.php" class="btn btn-sm btn-light">Ver Carrinho</a>
        `;
        
        toastContainer.appendChild(toast);
        
        // Remover toast após 5 segundos
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
});
</script>
</body>
</html>