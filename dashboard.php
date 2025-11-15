<?php
// dashboard.php - VERSÃO CORRIGIDA
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificação de sessão
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Inclusões
require_once 'classes/Documento.php';
require_once 'classes/Projeto.php';
require_once 'classes/Usuario.php';

include 'includes/header_logado.php';

try {
    // Instanciar classes
    $doc = new Documento();
    $projeto = new Projeto();
    $usuario = new Usuario();

    // Dados do usuário
    $usuario_id = $_SESSION['usuario_id'];
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

    // Buscar documentos do usuário
    $documentos = $doc->listarPorUsuario($usuario_id);

    // Estatísticas
    $total_documentos = count($documentos);
    $assinados = 0;
    $pendentes = 0;
    $rejeitados = 0;

    foreach ($documentos as $documento) {
        switch ($documento['status']) {
            case 'assinado':
                $assinados++;
                break;
            case 'pendente':
                $pendentes++;
                break;
            case 'rejeitado':
                $rejeitados++;
                break;
        }
    }

    // Documentos recentes (últimos 5)
    $documentos_recentes = array_slice($documentos, 0, 5);

    // Buscar projetos do usuário
    $projetos_usuario = $projeto->listarProjetos($usuario_id);
    $total_projetos = count($projetos_usuario);

    // Dados para gráfico
    $chart_data = [
        'labels' => ['Assinados', 'Pendentes', 'Rejeitados'],
        'data' => [$assinados, $pendentes, $rejeitados],
        'colors' => ['#28a745', '#ffc107', '#dc3545']
    ];

} catch (Exception $e) {
    die("Erro ao carregar dados do dashboard: " . $e->getMessage());
}
?>

<div class="container-fluid py-4">
    <!-- Cabeçalho e Boas-vindas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-lg">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-2">
                                <i class="fas fa-hand-wave me-2"></i>Olá, <?php echo htmlspecialchars($usuario_nome); ?>!
                            </h2>
                            <p class="card-text mb-0 opacity-75">
                                Bem-vindo ao seu painel de controle. Aqui você pode gerenciar todos os seus documentos.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="bg-white bg-opacity-25 rounded-pill px-3 py-2 d-inline-block">
                                <small class="text-white">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d/m/Y'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Importantes -->
    <?php if ($pendentes > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Atenção!</strong> Você tem <strong><?php echo $pendentes; ?> documento(s)</strong> aguardando assinatura.
                <a href="upload.php" class="alert-link ms-2">Enviar novo documento</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Métricas Principais -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total de Documentos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $total_documentos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Assinados
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $assinados; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Pendentes
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $pendentes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-4 border-start-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Projetos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $total_projetos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-folder fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico e Ações Rápidas -->
    <div class="row mb-4">
        <!-- Gráfico de Status -->
        <?php if ($total_documentos > 0): ?>
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Distribuição de Documentos
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="100%" height="60"></canvas>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-pie fa-4x mb-3"></i>
                        <h5>Nenhum dado para exibir</h5>
                        <p>Envie seu primeiro documento para ver estatísticas</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ações Rápidas -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Ações Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="upload.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload me-2"></i>Novo Documento
                        </a>
                        <a href="historico.php" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i>Ver Histórico
                        </a>
                        <a href="projetos.php" class="btn btn-outline-primary">
                            <i class="fas fa-folder me-2"></i>Meus Projetos
                        </a>
                        <a href="perfil.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>Meu Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documentos Recentes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-file me-2"></i>Documentos Recentes
                    </h6>
                    <a href="historico.php" class="btn btn-sm btn-outline-primary">
                        Ver Todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($documentos_recentes)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-file fa-4x mb-3"></i>
                            <h5>Nenhum documento encontrado</h5>
                            <p class="mb-4">Comece enviando seu primeiro documento para assinatura</p>
                            <a href="upload.php" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Enviar Primeiro Documento
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Documento</th>
                                        <th width="20%">Status</th>
                                        <th width="20%">Data de Criação</th>
                                        <th width="20%" class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos_recentes as $doc_item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf text-danger me-3 fa-lg"></i>
                                                <div>
                                                    <strong class="d-block"><?php echo htmlspecialchars($doc_item['titulo']); ?></strong>
                                                    <small class="text-muted">
                                                        <?php 
                                                        if (isset($doc_item['arquivo_path']) && file_exists($doc_item['arquivo_path'])) {
                                                            $tamanho = filesize($doc_item['arquivo_path']);
                                                            echo round($tamanho / 1024, 1) . ' KB';
                                                        } else {
                                                            echo 'PDF';
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
                                            $status = $doc_item['status'];
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
                                                // CORREÇÃO: Usar criado_em em vez de data_envio
                                               $data_criacao = new DateTime($documento['data_envio'] ?? $documento['criado_em']);
                                                ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?php echo $doc_item['arquivo_path']; ?>" 
                                                   target="_blank"
                                                   class="btn btn-outline-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Visualizar documento">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo $doc_item['arquivo_path']; ?>" 
                                                   class="btn btn-outline-success" 
                                                   download
                                                   data-bs-toggle="tooltip" 
                                                   title="Baixar documento">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <?php if ($doc_item['status'] === 'pendente'): ?>
                                                <a href="assinar.php?id=<?php echo $doc_item['id']; ?>" 
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para Gráficos -->
<?php if ($total_documentos > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Pizza
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($chart_data['labels']); ?>,
            datasets: [{
                data: <?php echo json_encode($chart_data['data']); ?>,
                backgroundColor: <?php echo json_encode($chart_data['colors']); ?>,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<?php endif; ?>


<?php
include 'includes/footer.php';
?>