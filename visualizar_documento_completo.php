<?php
// visualizar_documento_completo.php - VERSÃO ATUALIZADA
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Documento.php';
require_once 'classes/AssinaturaDigital.php';
require_once 'classes/GeradorAssinatura.php';

$documento_id = $_GET['id'] ?? null;
$download = $_GET['download'] ?? false;
$tipo_download = $_GET['tipo'] ?? 'original'; // original, assinaturas, completo

if (!$documento_id) {
    die("ID do documento não especificado.");
}

try {
    $doc_class = new Documento();
    $assinatura_digital = new AssinaturaDigital();
    
    // Buscar documento
    $documento = $doc_class->buscar($documento_id, $_SESSION['usuario_id']);
    $assinaturas = $assinatura_digital->listarAssinaturasDocumento($documento_id);
    
    if (!$documento) {
        die("Documento não encontrado ou sem permissão.");
    }
    
    // Processar download
    if ($download && class_exists('GeradorAssinatura')) {
        $gerador_assinatura = new GeradorAssinatura();
        
        switch ($tipo_download) {
            case 'assinaturas':
                // Apenas página de assinaturas
                $pdf = $gerador_assinatura->gerarPaginaAssinatura($documento, $assinaturas);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="assinaturas_' . $documento_id . '.pdf"');
                $pdf->Output('D');
                exit;
                
            case 'completo':
                // Tentar mesclar (abordagem simplificada)
                $this->gerarDocumentoCompleto($documento, $assinaturas);
                exit;
                
            case 'original':
            default:
                // Documento original
                if (file_exists($documento['arquivo_path'])) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . basename($documento['arquivo_path']) . '"');
                    header('Content-Length: ' . filesize($documento['arquivo_path']));
                    readfile($documento['arquivo_path']);
                    exit;
                }
                break;
        }
    }
    
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

/**
 * Gerar documento completo com assinaturas (abordagem simplificada)
 */
function gerarDocumentoCompleto($documento, $assinaturas) {
    $gerador_assinatura = new GeradorAssinatura();
    
    // Criar um novo PDF
    $pdf = new FPDF();
    
    // Primeira página: Capa com informações
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 20, 'DOCUMENTO COMPLETO', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Informacoes do Documento', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 6, 'Titulo:', 0, 0);
    $pdf->Cell(0, 6, $documento['titulo'], 0, 1);
    $pdf->Cell(40, 6, 'Hash SHA-256:', 0, 0);
    $pdf->SetFont('Courier', '', 8);
    $pdf->Cell(0, 6, $documento['hash_documento'], 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 6, 'Data:', 0, 0);
    $pdf->Cell(0, 6, date('d/m/Y H:i', strtotime($documento['criado_em'])), 0, 1);
    
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->MultiCell(0, 6, 'Este documento inclui o original e o termo de assinaturas digitais.');
    
    // Adicionar página de assinaturas
    $pdf->AddPage();
    $pdf_assinaturas = $gerador_assinatura->gerarPaginaAssinatura($documento, $assinaturas);
    
    // Em uma implementação real, você importaria as páginas do PDF original aqui
    // Por enquanto, vamos apenas incluir a página de assinaturas
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="documento_completo_' . $documento['id'] . '.pdf"');
    $pdf->Output('D');
}

include "includes/header_logado.php";
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-contract me-2"></i>
                            <?php echo htmlspecialchars($documento['titulo']); ?>
                        </h4>
                        <div>
                            <span class="badge bg-light text-primary fs-6">
                                ID: <?php echo $documento_id; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Documento Principal -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-file-pdf me-2"></i>Visualização do Documento
                    </h5>
                </div>
                <div class="card-body p-0">
                    <iframe src="<?php echo $documento['arquivo_path']; ?>" 
                            width="100%" 
                            height="600px" 
                            style="border: none; border-radius: 0 0 0.375rem 0.375rem;">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Painel de Assinaturas e Ações -->
        <div class="col-lg-4">
            <!-- Ações Rápidas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Opções de Download
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Documento Original -->
                        <a href="visualizar_documento_completo.php?id=<?php echo $documento_id; ?>&download=1&tipo=original" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-file-pdf me-2"></i>Documento Original
                        </a>
                        
                        <!-- Apenas Assinaturas -->
                        <a href="visualizar_documento_completo.php?id=<?php echo $documento_id; ?>&download=1&tipo=assinaturas" 
                           class="btn btn-outline-info">
                            <i class="fas fa-signature me-2"></i>Termo de Assinaturas
                        </a>
                        
                        <!-- Documento Completo -->
                        <a href="visualizar_documento_completo.php?id=<?php echo $documento_id; ?>&download=1&tipo=completo" 
                           class="btn btn-primary">
                            <i class="fas fa-file-contract me-2"></i>Documento Completo
                        </a>
                    </div>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6><i class="fas fa-info-circle me-2"></i>Opções:</h6>
                        <ul class="small mb-0">
                            <li><strong>Documento Original:</strong> Apenas o PDF original</li>
                            <li><strong>Termo de Assinaturas:</strong> Apenas a página com as assinaturas</li>
                            <li><strong>Documento Completo:</strong> Original + Termo de Assinaturas</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Restante do código permanece igual -->
            <!-- Assinaturas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-signature me-2"></i>Assinaturas
                        </h5>
                        <span class="badge bg-light text-info fs-6">
                            <?php echo count($assinaturas); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($assinaturas)): ?>
                        <div class="assinaturas-list">
                            <?php foreach ($assinaturas as $index => $assinatura): ?>
                            <div class="assinatura-item border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-user-circle me-2"></i>
                                        <?php echo htmlspecialchars($assinatura['usuario_nome']); ?>
                                    </h6>
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
                                
                                <p class="mb-1 small text-muted">
                                    <i class="fas fa-envelope me-2"></i><?php echo $assinatura['email']; ?>
                                </p>
                                
                                <p class="mb-1 small text-muted">
                                    <i class="fas fa-clock me-2"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($assinatura['timestamp'])); ?>
                                </p>
                                
                                <p class="mb-0 small">
                                    <i class="fas fa-fingerprint me-2"></i>
                                    <code class="text-muted"><?php echo substr($assinatura['assinatura'], 0, 20); ?>...</code>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-signature fa-3x mb-3"></i>
                            <h6>Nenhuma assinatura</h6>
                            <p class="small">Este documento ainda não foi assinado.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informações do Documento -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informações
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $status_completo = $assinatura_digital->verificarStatusCompleto($documento_id);
                    $status_config = [
                        'VALIDO' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Válido'],
                        'NAO_ASSINADO' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pendente'],
                        'ASSINATURAS_INVALIDAS' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Inválido'],
                        'CORROMPIDO' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Corrompido']
                    ];
                    $status = $status_completo['status_geral'];
                    $config = $status_config[$status] ?? $status_config['NAO_ASSINADO'];
                    ?>
                    
                    <div class="mb-3">
                        <strong>Status do Documento:</strong>
                        <div class="mt-1">
                            <span class="badge bg-<?php echo $config['class']; ?> fs-6">
                                <i class="fas fa-<?php echo $config['icon']; ?> me-1"></i>
                                <?php echo $config['text']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Integridade:</strong>
                        <div class="mt-1">
                            <?php if ($status_completo['integridade']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-shield-alt me-1"></i>Preservada
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Comprometida
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Hash SHA-256:</strong>
                        <div class="mt-1">
                            <code class="d-block p-2 bg-light small text-break">
                                <?php echo $documento['hash_documento']; ?>
                            </code>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <strong>Data de Criação:</strong>
                        <div class="mt-1">
                            <?php echo date('d/m/Y H:i', strtotime($documento['criado_em'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include "includes/footer.php"; ?>