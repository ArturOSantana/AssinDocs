<?php
// shared.php - Página para acesso via links compartilhados
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir classes necessárias
require_once 'classes/Compartilhamento.php';
require_once 'classes/Documento.php';
require_once 'classes/AssinaturaDigital.php';

$token = $_GET['token'] ?? '';
$acao = $_GET['acao'] ?? 'visualizacao';
$senha = $_POST['senha'] ?? '';

$compartilhamento = new Compartilhamento();
$documento_class = new Documento();
$assinatura_class = new AssinaturaDigital();

$msg = '';
$tipo_msg = '';
$dados_link = null;
$documento = null;
$link_info = null;
$permissoes = [];

// Validar token
if (empty($token)) {
    die("Token não fornecido");
}

// Processar validação do link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validar_senha'])) {
    $senha = $_POST['senha'];
    $dados_link = $compartilhamento->validarEAcessarLink($token, $senha, $acao);
} else {
    // Tentar validar sem senha primeiro
    $dados_link = $compartilhamento->validarEAcessarLink($token, null, $acao);
}

if ($dados_link && $dados_link['success']) {
    $documento = $dados_link['documento'];
    $link_info = $dados_link['link'];
    $permissoes = $dados_link['permissoes'];
} else {
    $msg = $dados_link['message'] ?? "Erro ao validar link";
    $tipo_msg = 'danger';
}

// Processar ações específicas
if ($dados_link && $dados_link['success']) {
    if (isset($_POST['assinar_documento'])) {
        // Em produção, aqui você coletaria os dados do signatário externo
        $msg = "Funcionalidade de assinatura externa em desenvolvimento";
        $tipo_msg = 'info';
    }
    
    if (isset($_POST['download_documento'])) {
        if (in_array('download', array_map('strtolower', $permissoes)) || in_array('Baixar documento', $permissoes)) {
            if (file_exists($documento['arquivo_path'])) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($documento['arquivo_path']) . '"');
                header('Content-Length: ' . filesize($documento['arquivo_path']));
                readfile($documento['arquivo_path']);
                exit;
            } else {
                $msg = "Arquivo não encontrado no servidor";
                $tipo_msg = 'danger';
            }
        } else {
            $msg = "Download não permitido para este link";
            $tipo_msg = 'warning';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Compartilhado - AssinDocs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .shared-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        .document-preview {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .permission-badge {
            font-size: 0.8rem;
        }
        .security-status {
            border-left: 4px solid #28a745;
            padding-left: 15px;
        }
        .security-status.invalid {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="shared-container p-4 p-md-5">
                    <!-- Cabeçalho -->
                    <div class="text-center mb-4">
                        <h1 class="text-primary mb-2">
                            <i class="fas fa-file-contract me-2"></i>AssinDocs
                        </h1>
                        <p class="text-muted">Documento compartilhado com você</p>
                    </div>

                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-<?php echo $tipo_msg; ?>">
                            <?php echo htmlspecialchars($msg); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de Senha (se necessário) -->
                    <?php if (!$dados_link || !$dados_link['success']): ?>
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-lock me-2"></i>Acesso Protegido
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Este link está protegido por senha. Digite a senha fornecida pelo remetente:</p>
                                <form method="POST" class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <input type="password" name="senha" class="form-control" 
                                               placeholder="Digite a senha" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="validar_senha" class="btn btn-primary w-100">
                                            <i class="fas fa-unlock me-2"></i>Acessar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Informações do Documento -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file-pdf me-2"></i>
                                        <?php echo htmlspecialchars($documento['titulo']); ?>
                                    </h5>
                                    <span class="badge bg-light text-primary">
                                        <?php echo strtoupper($link_info['tipo']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-info-circle me-2"></i>Informações:</strong>
                                        <ul class="list-unstyled mt-2">
                                            <li><small>Status: <span class="badge bg-<?php echo $documento['status'] === 'assinado' ? 'success' : 'warning'; ?>"><?php echo ucfirst($documento['status']); ?></span></small></li>
                                            <li><small>Criado: <?php echo date('d/m/Y H:i', strtotime($documento['criado_em'])); ?></small></li>
                                            <li><small>Hash: <code><?php echo substr($documento['hash_documento'], 0, 20); ?>...</code></small></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <strong><i class="fas fa-check-circle me-2"></i>Permissões:</strong>
                                        <div class="mt-2">
                                            <?php foreach ($permissoes as $permissao): ?>
                                                <span class="badge bg-success permission-badge me-1 mb-1">
                                                    <i class="fas fa-check me-1"></i><?php echo $permissao; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ações Disponíveis -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap gap-2">
                                            <!-- Visualização -->
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="document.getElementById('pdf-preview').scrollIntoView({behavior: 'smooth'})">
                                                <i class="fas fa-eye me-2"></i>Visualizar
                                            </button>
                                            
                                            <!-- Download -->
                                            <?php if (in_array('Baixar documento', $permissoes) || $link_info['tipo'] === 'download' || $link_info['tipo'] === 'assinatura'): ?>
                                            <form method="POST" class="d-inline">
                                                <button type="submit" name="download_documento" 
                                                        class="btn btn-outline-success">
                                                    <i class="fas fa-download me-2"></i>Baixar
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <!-- Assinatura -->
                                            <?php if (in_array('Assinar documento', $permissoes) || $link_info['tipo'] === 'assinatura'): ?>
                                            <button type="button" class="btn btn-outline-warning" 
                                                    data-bs-toggle="modal" data-bs-target="#modalAssinatura">
                                                <i class="fas fa-signature me-2"></i>Assinar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verificação de Integridade -->
                        <?php
                        $status_completo = $assinatura_class->verificarStatusCompleto($documento['id']);
                        $assinaturas = $assinatura_class->listarAssinaturasDocumento($documento['id']);
                        ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-<?php echo $status_completo['integridade'] ? 'success' : 'danger'; ?> text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Verificação de Segurança
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <i class="fas fa-<?php echo $status_completo['integridade'] ? 'check' : 'times'; ?>-circle fa-2x text-<?php echo $status_completo['integridade'] ? 'success' : 'danger'; ?> mb-2"></i>
                                            <h6>Integridade</h6>
                                            <small class="text-muted">
                                                <?php echo $status_completo['integridade'] ? 'Documento íntegro' : 'Documento alterado'; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <i class="fas fa-signature fa-2x text-primary mb-2"></i>
                                            <h6>Assinaturas</h6>
                                            <small class="text-muted">
                                                <?php echo count($assinaturas); ?> assinatura(s)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <?php
                                            $status_icon = $status_completo['status_geral'] === 'VALIDO' ? 'check' : 
                                                          ($status_completo['status_geral'] === 'NAO_ASSINADO' ? 'clock' : 'exclamation');
                                            $status_color = $status_completo['status_geral'] === 'VALIDO' ? 'success' : 
                                                          ($status_completo['status_geral'] === 'NAO_ASSINADO' ? 'warning' : 'danger');
                                            ?>
                                            <i class="fas fa-<?php echo $status_icon; ?>-circle fa-2x text-<?php echo $status_color; ?> mb-2"></i>
                                            <h6>Status Geral</h6>
                                            <small class="text-muted">
                                                <?php 
                                                $status_text = [
                                                    'VALIDO' => 'Válido',
                                                    'NAO_ASSINADO' => 'Não assinado',
                                                    'ASSINATURAS_INVALIDAS' => 'Assinaturas inválidas',
                                                    'CORROMPIDO' => 'Documento corrompido'
                                                ];
                                                echo $status_text[$status_completo['status_geral']] ?? $status_completo['status_geral'];
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visualizador do Documento -->
                        <div class="card shadow-sm mb-4" id="pdf-preview">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-pdf me-2"></i>Visualização do Documento
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if (file_exists($documento['arquivo_path'])): ?>
                                    <iframe src="<?php echo $documento['arquivo_path']; ?>" 
                                            width="100%" 
                                            height="600px"
                                            style="border: none; border-radius: 0 0 0.375rem 0.375rem;">
                                    </iframe>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                        <h5>Arquivo não encontrado</h5>
                                        <p>O documento físico não está disponível no servidor.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Assinaturas Existentes -->
                        <?php if (!empty($assinaturas)): ?>
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i>Assinaturas no Documento
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($assinaturas as $assinatura): ?>
                                    <div class="d-flex align-items-center mb-3 p-2 border rounded">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <strong><?php echo htmlspecialchars($assinatura['usuario_nome']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo $assinatura['email']; ?> • 
                                                <?php echo date('d/m/Y H:i', strtotime($assinatura['timestamp'])); ?>
                                            </small>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <?php 
                                            $verificacao = $assinatura_class->verificarAssinatura($documento['id'], $assinatura['usuario_id']);
                                            ?>
                                            <span class="badge bg-<?php echo $verificacao['valida'] ? 'success' : 'danger'; ?>">
                                                <i class="fas fa-<?php echo $verificacao['valida'] ? 'check' : 'times'; ?> me-1"></i>
                                                <?php echo $verificacao['valida'] ? 'Válida' : 'Inválida'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Rodapé -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small">
                            <i class="fas fa-shield-alt me-1"></i>
                            Documento compartilhado via AssinDocs - Plataforma segura de assinatura digital
                            <br>
                            <?php if (isset($link_info)): ?>
                                Link válido até: <?php echo date('d/m/Y H:i', strtotime($link_info['expira_em'])); ?>
                                • Usos: <?php echo $link_info['usos']; ?><?php echo $link_info['max_usos'] ? '/'.$link_info['max_usos'] : ''; ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Assinatura Externa -->
    <div class="modal fade" id="modalAssinatura">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assinar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Funcionalidade em desenvolvimento</strong><br>
                        Em breve você poderá assinar este documento digitalmente mesmo sem ter uma conta no AssinDocs.
                    </div>
                    <p>Para assinar este documento:</p>
                    <ol>
                        <li>Preencha seus dados pessoais</li>
                        <li>Será gerada uma chave digital única para você</li>
                        <li>Sua assinatura será vinculada ao hash do documento</li>
                        <li>Receba um comprovante de assinatura</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" disabled>
                        <i class="fas fa-signature me-2"></i>Em Breve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>