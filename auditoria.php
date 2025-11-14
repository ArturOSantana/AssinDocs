<?php
// auditoria.php - Painel de Auditoria
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Auditoria.php';
require_once 'classes/Documento.php';

include 'includes/header_logado.php';

$auditoria = new Auditoria();
$documento_class = new Documento();

// Filtros
$filtros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['filtrar'])) {
    $filtros = array_filter([
        'usuario_id' => $_POST['usuario_id'] ?? $_GET['usuario_id'] ?? null,
        'tipo' => $_POST['tipo'] ?? $_GET['tipo'] ?? null,
        'documento_id' => $_POST['documento_id'] ?? $_GET['documento_id'] ?? null,
        'data_inicio' => $_POST['data_inicio'] ?? $_GET['data_inicio'] ?? null,
        'data_fim' => $_POST['data_fim'] ?? $_GET['data_fim'] ?? null,
        'acao' => $_POST['acao'] ?? $_GET['acao'] ?? null
    ]);
}

// Buscar logs
$logs = $auditoria->buscarLogs($filtros, 50);
$estatisticas = $auditoria->obterEstatisticas($filtros);

// Buscar documentos para filtro
$documentos = $documento_class->listarPorUsuario($_SESSION['usuario_id']);

// Exportar CSV
if (isset($_POST['exportar_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=auditoria_' . date('Y-m-d') . '.csv');
    $auditoria->exportarCSV($filtros);
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Painel de Auditoria
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Monitoramento completo de todas as atividades do sistema. 
                        Registro de logs para compliance e segurança jurídica.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <?php if (!empty($estatisticas['estatisticas_gerais'])): ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white text-center">
                <div class="card-body">
                    <h3><?php echo $estatisticas['estatisticas_gerais']['total_logs']; ?></h3>
                    <p class="mb-0">Total de Logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white text-center">
                <div class="card-body">
                    <h3><?php echo $estatisticas['estatisticas_gerais']['usuarios_ativos']; ?></h3>
                    <p class="mb-0">Usuários Ativos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white text-center">
                <div class="card-body">
                    <h3><?php echo $estatisticas['estatisticas_gerais']['documentos_afetados']; ?></h3>
                    <p class="mb-0">Documentos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white text-center">
                <div class="card-body">
                    <h3><?php echo $estatisticas['estatisticas_gerais']['ips_unicos']; ?></h3>
                    <p class="mb-0">IPs Únicos</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" id="filtroForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Ação</label>
                        <select name="tipo" class="form-select">
                            <option value="">Todos os tipos</option>
                            <option value="login" <?php echo ($filtros['tipo'] ?? '') === 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="upload" <?php echo ($filtros['tipo'] ?? '') === 'upload' ? 'selected' : ''; ?>>Upload</option>
                            <option value="assinatura" <?php echo ($filtros['tipo'] ?? '') === 'assinatura' ? 'selected' : ''; ?>>Assinatura</option>
                            <option value="download" <?php echo ($filtros['tipo'] ?? '') === 'download' ? 'selected' : ''; ?>>Download</option>
                            <option value="compartilhamento" <?php echo ($filtros['tipo'] ?? '') === 'compartilhamento' ? 'selected' : ''; ?>>Compartilhamento</option>
                            <option value="seguranca" <?php echo ($filtros['tipo'] ?? '') === 'seguranca' ? 'selected' : ''; ?>>Segurança</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Documento</label>
                        <select name="documento_id" class="form-select">
                            <option value="">Todos os documentos</option>
                            <?php foreach ($documentos as $doc): ?>
                                <option value="<?php echo $doc['id']; ?>" 
                                    <?php echo ($filtros['documento_id'] ?? '') == $doc['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($doc['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" 
                               value="<?php echo $filtros['data_inicio'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" 
                               value="<?php echo $filtros['data_fim'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ação</label>
                        <input type="text" name="acao" class="form-control" 
                               placeholder="Buscar ação..." value="<?php echo $filtros['acao'] ?? ''; ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" name="filtrar" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <a href="auditoria.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Limpar
                        </a>
                        <button type="submit" name="exportar_csv" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Exportar CSV
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Logs -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Logs de Auditoria
                <span class="badge bg-primary ms-2"><?php echo count($logs); ?></span>
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                    <h5>Nenhum log encontrado</h5>
                    <p>Nenhuma atividade registrada com os filtros aplicados</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Tipo</th>
                                <th>Documento</th>
                                <th>IP</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($log['criado_em'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($log['usuario_nome']): ?>
                                        <strong><?php echo htmlspecialchars($log['usuario_nome']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $log['usuario_email']; ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Sistema</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['acao']); ?></strong>
                                    <?php if ($log['descricao']): ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($log['descricao']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $tipo_config = [
                                        'login' => ['icon' => 'sign-in-alt', 'class' => 'primary'],
                                        'upload' => ['icon' => 'upload', 'class' => 'success'],
                                        'assinatura' => ['icon' => 'signature', 'class' => 'warning'],
                                        'download' => ['icon' => 'download', 'class' => 'info'],
                                        'compartilhamento' => ['icon' => 'share-alt', 'class' => 'secondary'],
                                        'seguranca' => ['icon' => 'shield-alt', 'class' => 'danger'],
                                        'sistema' => ['icon' => 'cog', 'class' => 'dark']
                                    ];
                                    $config = $tipo_config[$log['tipo']] ?? $tipo_config['sistema'];
                                    ?>
                                    <span class="badge bg-<?php echo $config['class']; ?>">
                                        <i class="fas fa-<?php echo $config['icon']; ?> me-1"></i>
                                        <?php echo ucfirst($log['tipo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['documento_titulo']): ?>
                                        <strong><?php echo htmlspecialchars($log['documento_titulo']); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><code><?php echo $log['ip_address']; ?></code></small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="mostrarDetalhes(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
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

<!-- Modal para Detalhes -->
<div class="modal fade" id="modalDetalhes">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conteudoDetalhes">
                <!-- Conteúdo carregado via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function mostrarDetalhes(log) {
    const conteudo = `
        <div class="row">
            <div class="col-md-6">
                <strong>Informações Básicas:</strong>
                <ul class="list-unstyled mt-2">
                    <li><strong>Data/Hora:</strong> ${new Date(log.criado_em).toLocaleString()}</li>
                    <li><strong>Ação:</strong> ${log.acao}</li>
                    <li><strong>Tipo:</strong> ${log.tipo}</li>
                    <li><strong>Usuário:</strong> ${log.usuario_nome || 'Sistema'} ${log.usuario_email ? '(' + log.usuario_email + ')' : ''}</li>
                </ul>
            </div>
            <div class="col-md-6">
                <strong>Detalhes Técnicos:</strong>
                <ul class="list-unstyled mt-2">
                    <li><strong>IP:</strong> <code>${log.ip_address}</code></li>
                    <li><strong>Documento:</strong> ${log.documento_titulo || 'N/A'}</li>
                    <li><strong>User Agent:</strong> <small>${log.user_agent || 'N/A'}</small></li>
                </ul>
            </div>
        </div>
        ${log.descricao ? `
        <div class="row mt-3">
            <div class="col-12">
                <strong>Descrição:</strong>
                <p class="mt-2">${log.descricao}</p>
            </div>
        </div>
        ` : ''}
        ${log.metadata ? `
        <div class="row mt-3">
            <div class="col-12">
                <strong>Metadados:</strong>
                <pre class="mt-2 p-2 bg-light rounded"><code>${JSON.stringify(JSON.parse(log.metadata), null, 2)}</code></pre>
            </div>
        </div>
        ` : ''}
    `;
    
    document.getElementById('conteudoDetalhes').innerHTML = conteudo;
    new bootstrap.Modal(document.getElementById('modalDetalhes')).show();
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php
include 'includes/footer.php';
?>