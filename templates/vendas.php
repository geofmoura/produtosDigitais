<?php
session_start();
error_log('Vendas.php - SESSION: ' . print_r($_SESSION, true));

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['usuario'])) {
    error_log('Usuário não logado, redirecionando para index.php');
    header('Location: ../index.php'); 
    exit();
}

if (is_array($_SESSION['usuario'])) {
    $nome_usuario = isset($_SESSION['usuario']['nome']) 
        ? htmlspecialchars($_SESSION['usuario']['nome']) 
        : (isset($_SESSION['usuario']['username']) 
            ? htmlspecialchars($_SESSION['usuario']['username']) 
            : 'Usuário');
} else {
    $nome_usuario = htmlspecialchars($_SESSION['usuario']);
}

// Conexão com o banco de dados SQLite
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../db/database.sqlite'); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro de conexão: ' . $e->getMessage());
}

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE tipo = 'jogo'");
$stmt->execute();
$jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        'The Witcher 3: Wild Hunt' => 'thewitcher.jpg',
        'Roblox' => 'roblox.jpg',
        'Katana Zero' => 'katanazero.jpg',
        'God of War'=> 'godofwar.jpg',
        'Counter Strike 2' => 'counterstrike2.jpg',
        'Brawlhalla' => 'brawlhalla.jpg',
        'The Elder Scrolls V: Skyrim' => 'skyrim.jpg',
        'Free Fire' => 'freefire.jpg',
        'Baldur\'s Gate 3' => 'baldursgate.jpg',
        'Mad Max' => 'madmax.jpg',
        'Panicore' => 'panicore.jpg',
        'Shadow of Mordor' => 'shadow.jpg',
        'Castlevania: Lords of Shadow' => 'castlevania.jpg',
        'Dying Light' => 'dyinglight.jpg',
        'Dead Rising 3' => 'deadrising.jpg',
        'Call of Duty: Warzone' => 'warzone.jpg',
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="preload" href="../img/background.jpg" as="image">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="style.css"> 
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
                    <span class="me-3 user-greeting">Olá, <?php echo $nome_usuario; ?>!</span>
                    <a href="../server/logout.php" class="btn btn-outline-light logout-btn">Sair</a>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="container my-5 main-content">
        <!-- Barra de Pesquisa -->
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Pesquisar jogos ou gift cards..." autocomplete="off">
            <div id="searchResults" class="search-results"></div>
        </div>

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
                                            
                                            <?php if ($jogo['preco'] == 0.00 || ($jogo['promocao'] && $jogo['promocao'] == 0.00)): ?>
                                                <button onclick="simulateDownload('<?php echo htmlspecialchars($jogo['nome']); ?>')" class="btn btn-download w-100">
                                                    BAIXAR
                                                </button>
                                            <?php else: ?>
                                                <form action="../server/adicionar_carrinho.php" method="POST" class="purchase-form w-100">
                                                    <input type="hidden" name="produto_id" value="<?php echo $jogo['id']; ?>">
                                                    <button type="submit" class="btn btn-buy w-100">COMPRAR</button>
                                                </form>
                                            <?php endif; ?>
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
                
        <!-- Seção de Gift Cards -->
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

    <footer class="site-footer">
        <div class="container-fluid text-center">
            <p class="footer-text mb-0">© <?php echo date('Y'); ?> Impact Store. Todos os direitos reservados.</p>
        </div>
    </footer>

    <div id="downloadCard" class="download-card">
        <div class="download-header">
            <h6>Instalando Jogo</h6>
            <span id="downloadClose" class="download-close">&times;</span>
        </div>
        <div class="download-body">
            <p id="downloadGameName">Nome do Jogo</p>
            <div class="download-progress">
                <div id="downloadProgressBar" class="progress-bar"></div>
            </div>
            <div class="download-info">
                <span id="downloadPercentage">0%</span>
                <span id="downloadSpeed">0 MB/s</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buyForms = document.querySelectorAll('form[action*="adicionar_carrinho"]');
        
        buyForms.forEach(form => {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        showToast(result.message, result.productName);
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao adicionar o produto ao carrinho');
                }
            });
        });

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
            toast.style.maxWidth = '400px';
            toast.style.transition = 'all 0.3s ease';

            const toastContent = document.createElement('div');
            toastContent.style.flexGrow = '1';
            toastContent.innerHTML = `
                <strong>${productName}</strong> - ${message}
            `;
            toast.appendChild(toastContent);

            const cartButton = document.createElement('button');
            cartButton.innerHTML = '<i class="bi bi-cart"></i> Ver Carrinho';
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
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        function formatPrice(price) {
            return 'R$ ' + parseFloat(price).toFixed(2).replace('.', ',');
        }

        const allProducts = [
            <?php 
            foreach ($jogos as $jogo): 
                echo "{
                    id: {$jogo['id']},
                    nome: '" . addslashes($jogo['nome']) . "',
                    tipo: 'jogo',
                    preco: {$jogo['preco']},
                    promocao: " . ($jogo['promocao'] ? $jogo['promocao'] : 'null') . ",
                    descricao: '" . addslashes($jogo['descricao']) . "'
                },";
            endforeach; 
            foreach ($giftcards as $giftcard): 
                echo "{
                    id: {$giftcard['id']},
                    nome: '" . addslashes($giftcard['nome']) . "',
                    tipo: 'gift_card',
                    preco: {$giftcard['preco']},
                    promocao: " . ($giftcard['promocao'] ? $giftcard['promocao'] : 'null') . ",
                    descricao: '" . addslashes($giftcard['descricao']) . "'
                },";
            endforeach; 
            ?>
        ];
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }
            
            const filteredProducts = allProducts.filter(product => 
                product.nome.toLowerCase().includes(searchTerm)
            ).slice(0, 8);
            
            displaySearchResults(filteredProducts);
        });
        
        function displaySearchResults(products) {
            searchResults.innerHTML = '';
            
            if (products.length === 0) {
                searchResults.innerHTML = '<div class="no-results">Nenhum produto encontrado</div>';
                searchResults.style.display = 'block';
                return;
            }
            
            products.forEach(product => {
                const priceToShow = product.promocao && product.promocao !== null ? 
                    product.promocao : product.preco;
                
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <div>
                        <strong>${product.nome}</strong>
                        <span class="type">${product.tipo === 'jogo' ? 'Jogo' : 'Gift Card'}</span>
                    </div>
                    <div class="price">${formatPrice(priceToShow)}</div>
                `;
                
                resultItem.addEventListener('click', () => {
                    if (product.tipo === 'jogo') {
                        const carousel = bootstrap.Carousel.getOrCreateInstance('#gameCarousel');
                        const carouselItems = document.querySelectorAll('#gameCarousel .carousel-item');
                        let foundIndex = -1;
                        
                        carouselItems.forEach((item, index) => {
                            const itemName = item.querySelector('.game-title').textContent;
                            if (itemName === product.nome) {
                                foundIndex = index;
                            }
                        });
                        
                        if (foundIndex >= 0) {
                            carousel.to(foundIndex);
                            
                            carouselItems[foundIndex].classList.add('highlight-search-result');
                            setTimeout(() => {
                                carouselItems[foundIndex].classList.remove('highlight-search-result');
                            }, 8000);
                            
                            const carouselElement = document.querySelector('.game-carousel');
                            const yOffset = -100;
                            const y = carouselElement.getBoundingClientRect().top + window.pageYOffset + yOffset;
                            window.scrollTo({top: y, behavior: 'smooth'});
                        }
                    } else {
                        document.getElementById('giftcards-section').scrollIntoView({
                            behavior: 'smooth'
                        });

                        const giftCardElements = document.querySelectorAll('.giftcard-item');
                        giftCardElements.forEach(element => {
                            const cardName = element.querySelector('.game-title').textContent;
                            if (cardName === product.nome) {
                                element.style.boxShadow = '0 0 0 3px rgba(13, 110, 253, 0.5)';
                                setTimeout(() => {
                                    element.style.boxShadow = '';
                                }, 2000);
                            }
                        });
                    }
                    
                    searchResults.style.display = 'none';
                    searchInput.value = product.nome;
                });
                
                searchResults.appendChild(resultItem);
            });
            
            searchResults.style.display = 'block';
        }
        
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && searchResults.style.display === 'block') {
                const firstResult = searchResults.querySelector('.search-result-item');
                if (firstResult) {
                    firstResult.click();
                }
            }
        });
    });

    let downloadInProgress = false;

    function simulateDownload(gameName) {
        if (downloadInProgress) {
            alert('Já existe um download em andamento!');
            return;
        }
        
        downloadInProgress = true;
        
        const downloadCard = document.getElementById('downloadCard');
        const downloadGameName = document.getElementById('downloadGameName');
        const downloadProgressBar = document.getElementById('downloadProgressBar');
        const downloadPercentage = document.getElementById('downloadPercentage');
        const downloadSpeed = document.getElementById('downloadSpeed');
        const downloadClose = document.getElementById('downloadClose');
        const downloadHeader = downloadCard.querySelector('.download-header h6');

        downloadProgressBar.style.width = '0%';
        downloadPercentage.textContent = '0%';
        downloadHeader.textContent = 'Instalando Jogo';

        downloadGameName.textContent = gameName;
        downloadCard.style.display = 'block';

        let progress = 0;
        const duration = 8000; 
        const interval = 100; 
        const steps = duration / interval;
        const increment = 100 / steps;

        const baseSpeed = 8 + Math.random() * 15;
        
        const downloadInterval = setInterval(() => {
            progress += increment + (Math.random() * 0.5 - 0.25);
            if (progress >= 100) {
                progress = 100;
                clearInterval(downloadInterval);

                downloadProgressBar.style.width = '100%';
                downloadPercentage.textContent = '100%';
                downloadSpeed.textContent = '0 MB/s';

                setTimeout(() => {
                    downloadHeader.textContent = "Download Concluído";
                    downloadSpeed.textContent = 'Finalizado';

                    setTimeout(() => {
                        downloadCard.style.display = 'none';
                        downloadInProgress = false;
                    }, 3000);
                }, 1000);
                return;
            }

            downloadProgressBar.style.width = `${progress}%`;
            downloadPercentage.textContent = `${Math.round(progress)}%`;

            const speedVariation = 0.7 + Math.random() * 0.6;
            const currentSpeed = baseSpeed * speedVariation * (1 - progress/150);
            downloadSpeed.textContent = `${Math.max(0.1, currentSpeed).toFixed(1)} MB/s`;
        }, interval);

        downloadClose.onclick = function() {
            clearInterval(downloadInterval);
            downloadCard.style.display = 'none';
            downloadInProgress = false;
        };
    }
    </script>
</body>
</html>