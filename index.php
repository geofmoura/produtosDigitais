<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            background:rgb(0, 0, 0);
            background-size: cover;
        }
        .modal-content {
            background-color: rgb(255, 255, 255);
        }
        .switch-modal {
            color: #0d6efd;
            cursor: pointer;
            text-decoration: underline;
        }
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>

    <!-- Modal de Login  -->
    <div class="modal fade" id="myModalLogin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Faça seu Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formLogin" method="POST" onsubmit="fazerLogin(event)">
                        <input type="hidden" name="form_action" value="login">
                        <input type="email" name="email" placeholder="E-mail" class="form-control mb-3" required>
                        <input type="password" name="senha" placeholder="Senha" class="form-control mb-3" required>
                        <div id="loginError" class="error-message"></div>
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>
                    <p class="text-center mt-3">
                        Não tem conta? 
                        <span class="switch-modal" onclick="switchToCadastro()">Clique aqui para cadastrar</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro  -->
    <div class="modal fade" id="myModalCadastro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Faça seu Cadastro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCadastro" method="POST" onsubmit="fazerCadastro(event)">
                        <input type="hidden" name="form_action" value="cadastro">
                        <input type="text" name="nome" placeholder="Nome" class="form-control mb-3" required>
                        <input type="email" name="email" placeholder="E-mail" class="form-control mb-3" required>
                        <input type="password" name="senha" placeholder="Senha" class="form-control mb-3" required>
                        <div id="cadastroError" class="error-message"></div>
                        <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                    </form>
                    <p class="text-center mt-3">
                        Já tem conta? 
                        <span class="switch-modal" onclick="switchToLogin()">Clique aqui para entrar</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Abre o modal de login com delay APENAS na primeira vez (ao carregar a página)
        setTimeout(() => {
            const loginModal = new bootstrap.Modal(document.getElementById('myModalLogin'), {
                backdrop: 'static' // impede fechar clicando fora
            });
            loginModal.show();
        }, 500);

        // Função para alternar para Cadastro (instantâneo)
        function switchToCadastro() {
            bootstrap.Modal.getInstance(document.getElementById('myModalLogin')).hide();
            new bootstrap.Modal(document.getElementById('myModalCadastro')).show();
        }

        // Função para alternar para Login (instantâneo)
        function switchToLogin() {
            bootstrap.Modal.getInstance(document.getElementById('myModalCadastro')).hide();
            new bootstrap.Modal(document.getElementById('myModalLogin')).show();
        }

        // Função para login (envia os dados via AJAX)
        function fazerLogin(event) {
            event.preventDefault();
            
            const form = document.getElementById('formLogin');
            const formData = new FormData(form);
            const errorElement = document.getElementById('loginError');
            
            fetch('server/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redireciona se o login for bem-sucedido
                    window.location.href = 'templates/vendas.php';
                } else {
                    // Exibe mensagem de erro
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cadastro bem-sucedido, alterna para login
                    alert('Cadastro realizado com sucesso! Faça login para continuar.');
                    switchToLogin();
                } else {
                    // Exibe mensagem de erro
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