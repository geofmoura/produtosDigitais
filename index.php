<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header('Location: templates/vendas.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Store</title>
    <link rel="stylesheet" href="templates/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="home-page">
    <div class="splash-container">
        <div class="row g-0 h-100">

            <div class="col-md-8 game-collage">
    <div class="game-image">
        <img src="img/game1.jpg" alt="Jogo" class="img-fluid">
    </div>
</div>

            <div class="col-md-4 login-section">
                <div class="store-title">
                    <h1>IMPACT STORE</h1>
                </div>
                
                <div class="auth-container" id="loginContainer">
                    <h3>Faça seu Login</h3>
                    <form id="formLogin" method="POST" onsubmit="fazerLogin(event)">
                        <input type="hidden" name="form_action" value="login">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" placeholder="E-mail" class="form-control" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="senha" placeholder="Senha" class="form-control" required>
                        </div>
                        <div id="loginError" class="error-message"></div>
                        <button type="submit" class="btn btn-primary w-100">ENTRAR</button>
                    </form>
                    <p class="text-center mt-3">
                        Não tem conta? 
                        <span class="switch-auth" onclick="toggleAuth('cadastro')">Cadastre-se</span>
                    </p>
                </div>

                <div class="auth-container" id="cadastroContainer" style="display: none;">
                    <h3>Faça seu Cadastro</h3>
                    <form id="formCadastro" method="POST" onsubmit="fazerCadastro(event)">
                        <input type="hidden" name="form_action" value="cadastro">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="nome" placeholder="Nome" class="form-control" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" placeholder="E-mail" class="form-control" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="senha" placeholder="Senha" class="form-control" required>
                        </div>
                        <div id="cadastroError" class="error-message"></div>
                        <button type="submit" class="btn btn-primary w-100">CADASTRAR</button>
                    </form>
                    <p class="text-center mt-3">
                        Já tem conta? 
                        <span class="switch-auth" onclick="toggleAuth('login')">Faça login</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleAuth(type) {
            if (type === 'login') {
                document.getElementById('loginContainer').style.display = 'block';
                document.getElementById('cadastroContainer').style.display = 'none';
            } else {
                document.getElementById('loginContainer').style.display = 'none';
                document.getElementById('cadastroContainer').style.display = 'block';
            }
        }

        function fazerLogin(event) {
            event.preventDefault();
            
            const form = document.getElementById('formLogin');
            const formData = new FormData(form);
            const errorElement = document.getElementById('loginError');
            
            console.log("Enviando dados de login...");
            
            fetch('server/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Resposta de rede não ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta recebida:', data);
                if (data.success) {
                    console.log('Login bem-sucedido, redirecionando para:', data.redirect);
                    window.location.href = data.redirect;
                } else {
                    errorElement.textContent = data.message || 'Erro ao fazer login. Verifique suas credenciais.';
                    errorElement.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                errorElement.textContent = 'Ocorreu um erro ao processar o login.';
                errorElement.style.display = 'block';
            });
        }

        // Função para cadastro
        function fazerCadastro(event) {
            event.preventDefault();
            
            const form = document.getElementById('formCadastro');
            const formData = new FormData(form);
            const errorElement = document.getElementById('cadastroError');
            
            fetch('server/cadastro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Resposta de rede não ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Cadastro realizado com sucesso! Faça login para continuar.');
                    toggleAuth('login');
                } else {
                    errorElement.textContent = data.message || 'Erro ao fazer cadastro.';
                    errorElement.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                errorElement.textContent = 'Ocorreu um erro ao processar o cadastro.';
                errorElement.style.display = 'block';
            });
        }
    </script>
</body>
</html>