<?php
// assinar.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Documento.php';
require_once 'classes/AssinaturaDigital.php';
require_once 'classes/Arquivo.php';

include 'includes/header_logado.php';

$documento_id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$msg = '';
$tipo_msg = '';

if (!$documento_id) {
    die("ID do documento não especificado");
}

try {
    $doc_class = new Documento();
    $assinatura_class = new AssinaturaDigital();
    
    // Buscar documento
    $documento = $doc_class->buscar($documento_id, $usuario_id);
    
    if (!$documento) {
        die("Documento não encontrado ou você não tem permissão para acessá-lo");
    }

    // Verificar status completo do documento
    $status_completo = $assinatura_class->verificarStatusCompleto($documento_id);
    
    // Buscar assinaturas existentes
    $assinaturas = $assinatura_class->listarAssinaturasDocumento($documento_id);

    // Verificar se usuário já assinou
    $usuario_ja_assinou = false;
    foreach ($assinaturas as $assinatura) {
        if ($assinatura['usuario_id'] == $usuario_id) {
            $usuario_ja_assinou = true;
            break;
        }
    }

    // Processar assinatura
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assinar'])) {
        
        if ($usuario_ja_assinou) {
            $msg = "Você já assinou este documento.";
            $tipo_msg = 'warning';
        } else {
            // Verificar integridade antes de assinar
            $integridade = $assinatura_class->verificarIntegridadeDocumento($documento_id);
            
            if (!$integridade['success'] || !$integridade['integridade']) {
                $msg = "ERRO DE SEGURANÇA: " . $integridade['message'];
                $tipo_msg = 'danger';
            } else {
                // Assinar documento
                $resultado = $assinatura_class->assinarDocumento($documento_id, $usuario_id);
                
                if ($resultado['success']) {
                    $msg = "SUCESSO! " . $resultado['message'] . " Assinatura: " . $resultado['assinatura_curta'];
                    $tipo_msg = 'success';
                    
                    // Atualizar dados
                    $status_completo = $assinatura_class->verificarStatusCompleto($documento_id);
                    $assinaturas = $assinatura_class->listarAssinaturasDocumento($documento_id);
                    $usuario_ja_assinou = true;
                    $documento = $doc_class->buscar($documento_id, $usuario_id);
                } else {
                    $msg = "❌ " . $resultado['message'];
                    $tipo_msg = 'danger';
                }
            }
        }
    }

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Cabeçalho -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-signature me-2"></i>Assinar Documento Digitalmente
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-<?php echo $tipo_msg; ?>">
                            <?php echo htmlspecialchars($msg); ?>
                        </div>
                    <?php endif; ?>

                    <h5><?php echo htmlspecialchars($documento['titulo']); ?></h5>
                    
                    <!-- Status de Integridade -->
                    <div class="alert alert-<?php echo $status_completo['integridade'] ? 'success' : 'danger'; ?>">
                        <i class="fas fa-<?php echo $status_completo['integridade'] ? 'shield-alt' : 'exclamation-triangle'; ?> me-2"></i>
                        <strong>Integridade do Documento:</strong> 
                        <?php echo $status_completo['integridade'] ? 'Segura e Preservada' : '❌ Comprometida'; ?>
                        <br>
                        <small class="opacity-75">
                            Hash SHA-256: <code><?php echo $documento['hash_documento']; ?></code>
                        </small>
                    </div>

                    <!-- Informações Técnicas -->
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-id-card me-2"></i>Assinante:</strong>
                            <?php echo htmlspecialchars($usuario_nome); ?> (ID: <?php echo $usuario_id; ?>)
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-hashtag me-2"></i>Documento ID:</strong>
                            <?php echo $documento_id; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visualizador do Documento -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>Pré-visualização do Documento
                    </h6>
                </div>
                <div class="card-body">
                    <iframe src="<?php echo $documento['arquivo_path']; ?>" 
                            width="100%" 
                            height="600px" 
                            style="border: 1px solid #dee2e6; border-radius: 0.375rem;">
                    </iframe>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Painel de Assinatura -->
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-<?php echo $usuario_ja_assinou ? 'success' : 'warning'; ?> text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fingerprint me-2"></i>
                        <?php echo $usuario_ja_assinou ? 'Documento Assinado' : 'Assinatura Digital'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($usuario_ja_assinou): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5>Documento Assinado</h5>
                            <p class="text-muted">Você já assinou este documento digitalmente.</p>
                            
                            <!-- Verificar assinatura -->
                            <?php 
                            $verificacao = $assinatura_class->verificarAssinatura($documento_id, $usuario_id);
                            ?>
                            <div class="alert alert-<?php echo $verificacao['valida'] ? 'success' : 'danger'; ?> mt-3">
                                <i class="fas fa-<?php echo $verificacao['valida'] ? 'check' : 'times'; ?> me-2"></i>
                                <?php echo $verificacao['message']; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label"><strong>Confirmar Assinatura Digital</strong></label>
                                <p class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Ao assinar, você concorda com os termos deste documento e 
                                    reconhece a validade jurídica desta assinatura digital baseada em criptografia RSA.
                                </p>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirmacao" required>
                                    <label class="form-check-label" for="confirmacao">
                                        Confirmo que li e concordo com o documento
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="assinar" class="btn btn-success btn-lg">
                                    <i class="fas fa-signature me-2"></i>Assinar Digitalmente
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assinaturas Existentes -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Assinaturas no Documento
                        <span class="badge bg-primary ms-2"><?php echo count($assinaturas); ?></span>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($assinaturas)): ?>
                        <p class="text-muted text-center">Nenhuma assinatura ainda</p>
                    <?php else: ?>
                        <?php foreach ($assinaturas as $assinatura): ?>
                            <div class="d-flex align-items-center mb-3 p-2 border rounded">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong><?php echo htmlspecialchars($assinatura['usuario_nome']); ?></strong>
                                    <?php if ($assinatura['usuario_id'] == $usuario_id): ?>
                                        <span class="badge bg-info ms-1">Você</span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted">
                                        <?php 
                                        $data = new DateTime($assinatura['timestamp']);
                                        echo $data->format('d/m/Y H:i');
                                        ?>
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <?php 
                                    $verificacao = $assinatura_class->verificarAssinatura($documento_id, $assinatura['usuario_id']);
                                    ?>
                                    <span class="badge bg-<?php echo $verificacao['valida'] ? 'success' : 'danger'; ?>">
                                        <i class="fas fa-<?php echo $verificacao['valida'] ? 'check' : 'times'; ?> me-1"></i>
                                        <?php echo $verificacao['valida'] ? 'Válida' : 'Inválida'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Geral -->
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status do Documento</h6>
                </div>
                <div class="card-body">
                    <?php
                    $status_config = [
                        'VALIDO' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Válido e Assinado'],
                        'NAO_ASSINADO' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Aguardando Assinaturas'],
                        'ASSINATURAS_INVALIDAS' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Assinaturas Inválidas'],
                        'CORROMPIDO' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Documento Corrompido'],
                        'INVALIDO' => ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Status Desconhecido']
                    ];
                    $status = $status_completo['status_geral'];
                    $config = $status_config[$status] ?? $status_config['INVALIDO'];
                    ?>
                    <div class="text-center">
                        <span class="badge bg-<?php echo $config['class']; ?> fs-6 p-2">
                            <i class="fas fa-<?php echo $config['icon']; ?> me-2"></i>
                            <?php echo $config['text']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validação do checkbox
    const form = document.querySelector('form');
    const checkbox = document.getElementById('confirmacao');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;

    if (form && checkbox && submitBtn) {
        form.addEventListener('submit', function(e) {
            if (!checkbox.checked) {
                e.preventDefault();
                alert('Por favor, confirme que leu e concorda com o documento antes de assinar.');
                checkbox.focus();
            }
        });
    }
});
</script>

<?php
include 'includes/footer.php';
?>