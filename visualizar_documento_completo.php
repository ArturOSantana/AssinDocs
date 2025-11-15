<?php
// visualizar_documento_completo.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/Documento.php';
require_once 'classes/AssinaturaDigital.php';
require_once 'classes/GeradorAssinatura.php';

$documento_id = $_GET['id'] ?? null;
$download = $_GET['download'] ?? false;

if (!$documento_id) {
    die("ID do documento não especificado.");
}

try {
    $doc_class = new Documento();
    $assinatura_digital = new AssinaturaDigital();
    $gerador_assinatura = new GeradorAssinatura();
    
    // Buscar documento (em produção, adicione verificação de permissão)
    $documento = $doc_class->buscarParaCertificado($documento_id);
    $assinaturas = $assinatura_digital->listarAssinaturasDocumento($documento_id);
    
    if (!$documento) {
        die("Documento não encontrado.");
    }
    
    // Se for download, gerar PDF completo com assinaturas
    if ($download) {
        $pdf = $gerador_assinatura->gerarPaginaAssinatura($documento, $assinaturas);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="documento_com_assinaturas_' . $documento_id . '.pdf"');
        $pdf->Output('D');
        exit;
    }
    
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

include "includes/header.php";
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>
                        <?php echo htmlspecialchars($documento['titulo']); ?>
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Visualizador do Documento Original -->
                    <div class="mb-4">
                        <h5>Documento Original</h5>
                        <iframe src="<?php echo $documento['arquivo_path']; ?>" 
                                width="100%" height="500px" style="border: 1px solid #ddd;"></iframe>
                    </div>
                    
                    <!-- Página de Assinaturas Gerada -->
                    <div class="mb-4">
                        <h5>Termo de Assinaturas Digitais</h5>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Esta página contém o registro oficial de todas as assinaturas digitais 
                            com carimbo de data/hora e hash criptográfico.
                        </div>
                        
                        <!-- Pré-visualização das assinaturas -->
                        <div class="assinaturas-preview">
                            <?php foreach ($assinaturas as $index => $assinatura): ?>
                            <div class="assinatura-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6>
                                            <i class="fas fa-user-circle me-2"></i>
                                            <?php echo htmlspecialchars($assinatura['usuario_nome']); ?>
                                        </h6>
                                        <p class="mb-1"><small>Email: <?php echo $assinatura['email']; ?></small></p>
                                        <p class="mb-1"><small>Data: <?php echo date('d/m/Y H:i', strtotime($assinatura['timestamp'])); ?></small></p>
                                        <p class="mb-0">
                                            <small class="text-muted">
                                                Hash: <code><?php echo substr($assinatura['assinatura'], 0, 20); ?>...</code>
                                            </small>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?php if ($assinatura['valida']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Válida
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Inválida
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($assinaturas)): ?>
                                <p class="text-muted text-center py-4">
                                    <i class="fas fa-signature fa-2x mb-3"></i><br>
                                    Nenhuma assinatura registrada ainda.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Painel de Ações -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Download
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo $documento['arquivo_path']; ?>" 
                           download 
                           class="btn btn-outline-primary">
                            <i class="fas fa-file-pdf me-2"></i>Documento Original
                        </a>
                        
                        <a href="visualizar_documento_completo.php?id=<?php echo $documento_id; ?>&download=1" 
                           class="btn btn-primary">
                            <i class="fas fa-file-contract me-2"></i>Documento + Assinaturas (PDF)
                        </a>
                        
                        <a href="certificado_assinatura_externa.php?doc_id=<?php echo $documento_id; ?>" 
                           target="_blank" 
                           class="btn btn-outline-success">
                            <i class="fas fa-certificate me-2"></i>Certificado Completo
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Informações do Documento -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informações
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Hash do Documento:</strong></p>
                    <code class="d-block p-2 bg-light small"><?php echo $documento['hash_documento']; ?></code>
                    
                    <p class="mt-3"><strong>Status:</strong></p>
                    <?php
                    $status_completo = $assinatura_digital->verificarStatusCompleto($documento_id);
                    $status_cor = $status_completo['status_geral'] === 'VALIDO' ? 'success' : 'warning';
                    ?>
                    <span class="badge bg-<?php echo $status_cor; ?>">
                        <?php echo $status_completo['status_geral']; ?>
                    </span>
                    
                    <p class="mt-3"><strong>Assinaturas:</strong></p>
                    <p><?php echo count($assinaturas); ?> registro(s)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>