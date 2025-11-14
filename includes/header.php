<?php
// Header para usuários deslogados
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AssinDocs - Assinaturas Digitais Seguras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <a class="logo-area" href="index.php">
                <img src="../img/logo.png" alt="Imagem">
               <!-- <i class="fas fa-file-signature fa-2x me-2"></i>  -->
                <span>AssinDocs</span>
            </a>
            <nav class="menu-deslogado">
                <a class="menu-link" href="index.php"><i class="fas fa-home"></i> Início</a>
                <button class="btn btn-primary px-4" data-bs-toggle="tooltip" title="Crie sua conta gratuitamente"><a href="cadastro.php" class="text-white text-decoration-none">Cadastrar</a></button>
                <button class="btn btn-outline-primary px-4" data-bs-toggle="tooltip" title="Acesse sua conta"><a href="login.php" class="text-decoration-none">Entrar</a></button>
                <!-- Toggle Tema (inovação) -->
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary ms-2"><i class="fas fa-moon"></i></button>
            </nav>
        </div>
    </div>
</header>
<script>
    // Toggle Tema (escuro/claro)
    document.getElementById('theme-toggle').addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const icon = document.querySelector('#theme-toggle i');
        icon.classList.toggle('fa-moon');
        icon.classList.toggle('fa-sun');
    });
</script>