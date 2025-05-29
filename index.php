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
<div class="background-overlay">
    <div class="main-container">
        <div class="left-image">
            <img src="img/cardgame.jpg" alt="Cardgame">
        </div>

        <div class="login-section">
            <div class="auth-container">
                <h1>IMPACT STORE</h1>
                <h3 id="authTitle">Faça seu Login</h3>
                
    <form id="authForm" method="POST">
        <!-- Campos de Login -->
        <div id="loginFields">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="loginEmail" name="email" placeholder="E-mail" class="form-control" required>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="loginSenha" name="senha" placeholder="Senha" class="form-control" required>
            </div>
        </div>

        <!-- Campos de Cadastro -->
        <div id="registerFields" style="display:none">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" id="cadastroNome" name="nome" placeholder="Nome Completo" class="form-control">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" id="cadastroEmail" name="email" placeholder="E-mail" class="form-control">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="cadastroSenha" name="senha" placeholder="Senha" class="form-control">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="confirmarSenha" name="confirmar_senha" placeholder="Confirmar Senha" class="form-control">
            </div>
        </div>

        <div id="authError" class="alert alert-danger" style="display: none;"></div>
        <button type="submit" id="authButton" class="btn btn-primary w-100">ENTRAR</button>
    </form>

                
                <p class="text-center mt-3">
                    <span id="authSwitchText">Não tem conta?</span> 
                    <a href="#" class="switch-auth" id="authSwitchLink">Cadastre-se</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let isLogin = true;
    document.getElementById('authSwitchLink').addEventListener('click', function (e) {
        e.preventDefault();
        isLogin = !isLogin;

        if (isLogin) {
            document.getElementById('authTitle').textContent = 'Faça seu Login';
            document.getElementById('loginFields').style.display = 'block';
            document.getElementById('registerFields').style.display = 'none';
            document.getElementById('authButton').textContent = 'ENTRAR';
            document.getElementById('authSwitchText').textContent = 'Não tem conta?';
            document.getElementById('authSwitchLink').textContent = 'Cadastre-se';
        } else {
            document.getElementById('authTitle').textContent = 'Crie sua Conta';
            document.getElementById('loginFields').style.display = 'none';
            document.getElementById('registerFields').style.display = 'block';
            document.getElementById('authButton').textContent = 'CADASTRAR';
            document.getElementById('authSwitchText').textContent = 'Já tem conta?';
            document.getElementById('authSwitchLink').textContent = 'Faça login';
        }
    });

    document.getElementById('authForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const errorElement = document.getElementById('authError');
        errorElement.style.display = 'none';

        document.querySelectorAll('#authForm input').forEach(input => {
            input.disabled = true;
        });

        if (isLogin) {
            document.querySelectorAll('#loginFields input').forEach(input => input.disabled = false);
        } else {
            document.querySelectorAll('#registerFields input').forEach(input => input.disabled = false);
        }

        const formData = new FormData(this);
        const endpoint = isLogin ? 'server/login.php' : 'server/cadastro.php';

        console.log('Enviando requisição para:', endpoint);
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Status da resposta:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.log('Texto da resposta:', text);
                    try {
                        return JSON.parse(text);
                    } catch {
                        throw new Error(text || 'Erro desconhecido');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            if (data.success) {
                if (isLogin) {
                    window.location.href = data.redirect || 'templates/vendas.php';
                } else {
                    alert('Cadastro realizado com sucesso! Faça login para continuar.');
                    isLogin = true;
                    document.getElementById('authTitle').textContent = 'Faça seu Login';
                    document.getElementById('loginFields').style.display = 'block';
                    document.getElementById('registerFields').style.display = 'none';
                    document.getElementById('authButton').textContent = 'ENTRAR';
                    document.getElementById('authSwitchText').textContent = 'Não tem conta?';
                    document.getElementById('authSwitchLink').textContent = 'Cadastre-se';
                    this.reset();
                }
            } else {
                errorElement.textContent = data.message || 'Ocorreu um erro. Tente novamente.';
                errorElement.style.display = 'block';
            }
        })
        .catch(error => {
            console.error("Erro:", error);
            errorElement.textContent = 'Erro na conexão: ' + error.message;
            errorElement.style.display = 'block';
        });
    });
</script>

</body>
</html>