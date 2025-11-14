<?php
// historico.php - VERSÃO CORRIGIDA
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header_logado.php';
require_once 'classes/Documento.php';

try {
    $doc = new Documento();
    $usuario_id = $_SESSION['usuario_id'];
    
    // Buscar documentos do usuário
    $documentos = $doc->listarPorUsuario($usuario_id);
    
    // DEBUG
    error_log("DEBUG historico.php: " . count($documentos) . " documentos encontrados");
    
} catch (Exception $e) {
    die("Erro ao carregar histórico: " . $e->getMessage());
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary">
                            <i class="fas fa-history me-2"></i>Histórico de Documentos
                        </h4>
                        <div>
                            <span class="badge bg-primary fs-6">
                                <?php echo count($documentos); ?> documento(s)
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($documentos)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-file-alt fa-4x mb-3"></i>
                            <h4>Nenhum documento encontrado</h4>
                            <p class="mb-4">Você ainda não possui documentos no sistema.</p>
                            <a href="upload.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Enviar Primeiro Documento
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Tabela de Documentos -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Documento</th>
                                        <th width="15%">Status</th>
                                        <th width="20%">Data de Envio</th>
                                        <th width="20%">Hash</th>
                                        <th width="15%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $documento): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf text-danger me-3 fa-lg"></i>
                                                <div>
                                                    <strong class="d-block"><?php echo htmlspecialchars($documento['titulo']); ?></strong>
                                                    <small class="text-muted">
                                                        <?php 
                                                        if (isset($documento['arquivo_path']) && file_exists($documento['arquivo_path'])) {
                                                            $tamanho = filesize($documento['arquivo_path']);
                                                            echo round($tamanho / 1024, 1) . ' KB';
                                                        } else {
                                                            echo 'Arquivo PDF';
                                                        }
                                                        ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $status_config = [
                                                'assinado' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Assinado'],
                                                'pendente' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pendente'],
                                                'rejeitado' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Rejeitado']
                                            ];
                                            $status = $documento['status'];
                                            $config = $status_config[$status] ?? $status_config['pendente'];
                                            ?>
                                            <span class="badge bg-<?php echo $config['class']; ?>">
                                                <i class="fas fa-<?php echo $config['icon']; ?> me-1"></i>
                                                <?php echo $config['text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php 
                                                // USANDO criado_em em vez de data_envio
                                                $data_criacao = new DateTime($documento['criado_em']);
                                                echo $data_criacao->format('d/m/Y H:i');
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <code>
                                                    <?php 
                                                    if (!empty($documento['hash_documento'])) {
                                                        echo substr($documento['hash_documento'], 0, 16) . '...';
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </code>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?php echo $documento['arquivo_path']; ?>" 
                                                   target="_blank"
                                                   class="btn btn-outline-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Visualizar documento">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo $documento['arquivo_path']; ?>" 
                                                   class="btn btn-outline-success" 
                                                   download
                                                   data-bs-toggle="tooltip" 
                                                   title="Baixar documento">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <?php if ($documento['status'] === 'pendente'): ?>
                                                <a href="assinar.php?id=<?php echo $documento['id']; ?>" 
                                                   class="btn btn-outline-warning" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Assinar documento">
                                                    <i class="fas fa-signature"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Estatísticas -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="text-primary"><?php echo count($documentos); ?></h5>
                                        <p class="mb-0">Total</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="text-success">
                                            <?php 
                                            $assinados = array_filter($documentos, fn($d) => $d['status'] === 'assinado');
                                            echo count($assinados);
                                            ?>
                                        </h5>
                                        <p class="mb-0">Assinados</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="text-warning">
                                            <?php 
                                            $pendentes = array_filter($documentos, fn($d) => $d['status'] === 'pendente');
                                            echo count($pendentes);
                                            ?>
                                        </h5>
                                        <p class="mb-0">Pendentes</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="text-danger">
                                            <?php 
                                            $rejeitados = array_filter($documentos, fn($d) => $d['status'] === 'rejeitado');
                                            echo count($rejeitados);
                                            ?>
                                        </h5>
                                        <p class="mb-0">Rejeitados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php
include 'includes/footer.php';
?>