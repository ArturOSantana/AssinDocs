<?php
// Verificação de login
require_once "classes/Auth.php";
if (!Auth::verificarLogin()) {
    header("Location: login.php");
    exit;
}

// Dados do usuário (simulados; ajuste com Auth::usuarioId() se necessário)
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_email = $_SESSION['usuario_email'] ?? 'usuario@email.com';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AssinDocs - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <header class="main-header shadow-sm">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center logo-area" href="dashboard.php">
                    <i class="fas fa-file-signature fa-2x me-2"></i>
                    <span class="fw-bold">AssinDocs</span>
                </a>

                <!-- Botão Menu Mobile -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLogged" aria-controls="navbarLogged" aria-expanded="false" aria-label="Menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menu Principal Completo -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- Navegação Principal -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="dashboard.php" data-bs-toggle="tooltip" title="Veja seus documentos">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="upload.php" data-bs-toggle="tooltip" title="Envie um novo documento">
                            <i class="fas fa-upload me-1"></i> Novo Documento
                        </a>
                    </li>

                    <!-- Gerenciamento -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="historico.php" data-bs-toggle="tooltip" title="Histórico de assinaturas">
                            <i class="fas fa-history me-1"></i> Histórico
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="links.php" data-bs-toggle="tooltip" title="Compartilhar documentos por link">
                            <i class="fas fa-share-alt me-1"></i> Compartilhar
                        </a>
                    </li>

                    <!-- Administrativo -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="auditoria.php" data-bs-toggle="tooltip" title="Logs e auditoria do sistema">
                            <i class="fas fa-clipboard-list me-1"></i> Auditoria
                        </a>
                    </li>

                    <!-- Configurações -->
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="perfil.php" data-bs-toggle="tooltip" title="Gerencie seu perfil">
                            <i class="fas fa-user me-1"></i> Perfil
                        </a>
                    </li>
                </ul>

                <!-- Elementos à Direita -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- Notificações (Simuladas) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link menu-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" title="Notificações">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">3</span> <!-- Badge de notificações -->
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationsDropdown">
                            <li>
                                <h6 class="dropdown-header">Notificações Recentes</h6>
                            </li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-check-circle text-success me-2"></i> Documento assinado com sucesso</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Assinatura pendente</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-info-circle text-info me-2"></i> Novo projeto criado</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-center" href="#">Ver Todas</a></li>
                        </ul>
                    </li>

                    <!-- Perfil Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link menu-link d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg me-2"></i>
                            <span><?php echo htmlspecialchars($usuario_nome); ?></span>
                            <span class="badge-seguro ms-2"><i class="fas fa-shield-alt"></i> Verificado</span> <!-- Badge de credibilidade -->
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown">
                            <li>
                                <h6 class="dropdown-header"><?php echo htmlspecialchars($usuario_email); ?></h6>
                            </li>
                            <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-edit me-2"></i> Editar Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                        </ul>
                    </li>

                    <!-- Toggle Tema -->
                    <li class="nav-item">
                        <button id="theme-toggle" class="btn btn-sm btn-outline-secondary ms-3" data-bs-toggle="tooltip" title="Alternar Tema">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
            </div>
        </nav>
    </header>

    <script>
        // Inicializar Tooltips Bootstrap
        document.addEventListener('DOMContentLoaded', () => {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

            // Toggle Tema
            const themeToggle = document.getElementById('theme-toggle');
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                const icon = themeToggle.querySelector('i');
                icon.classList.toggle('fa-moon');
                icon.classList.toggle('fa-sun');
                // Salvar preferência (opcional: use localStorage)
                localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
            });

            // Carregar tema salvo
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
                themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
            }
        });
    </script>