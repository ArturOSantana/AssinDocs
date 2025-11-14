<?php
// links.php - Gerenciamento de Links de Compartilhamento
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Documento.php';
require_once 'classes/Compartilhamento.php';

include 'includes/header_logado.php';

$usuario_id = $_SESSION['usuario_id'];
$compartilhamento = new Compartilhamento();
$documento_class = new Documento();

$msg = '';
$tipo_msg = '';

// Ações via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['gerar_link'])) {
        $resultado = $compartilhamento->gerarLink(
            $_POST['documento_id'],
            $usuario_id,
            $_POST['tipo'],
            $_POST['expira_horas'],
            $_POST['max_usos'] ?: null,
            $_POST['senha'] ?: null
        );

        if ($resultado['success']) {
            $msg = " Link gerado com sucesso! URL: " . $resultado['url_completa'];
            $tipo_msg = 'success';
        } else {
            $msg = "Erro: " . $resultado['message'];
            $tipo_msg = 'danger';
        }
    }
    elseif (isset($_POST['toggle_link'])) {
        $resultado = $compartilhamento->toggleLink($_POST['link_id'], $usuario_id);
        if ($resultado['success']) {
            $msg = " Link " . ($resultado['novo_status'] ? 'ativado' : 'desativado') . " com sucesso";
            $tipo_msg = 'success';
        } else {
            $msg = " Erro ao alterar status do link";
            $tipo_msg = 'danger';
        }
    }
    elseif (isset($_POST['excluir_link'])) {
        $resultado = $compartilhamento->excluirLink($_POST['link_id'], $usuario_id);
        if ($resultado['success']) {
            $msg = "Link excluído com sucesso";
            $tipo_msg = 'success';
        } else {
            $msg = " Erro ao excluir";
            $tipo_msg = 'danger';
        }
    }
}

// Buscar documentos do usuário para o formulário
$documentos = $documento_class->listarPorUsuario($usuario_id);

// Buscar links do usuário
$links = $compartilhamento->listarLinksUsuario($usuario_id);

?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-share-alt me-2"></i>Compartilhamento por Link Seguro
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-<?php echo $tipo_msg; ?>">
                            <?php echo htmlspecialchars($msg); ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted">
                        Gere links seguros para compartilhar seus documentos com outras pessoas. 
                        Controle o acesso com senha, limite de usos e data de expiração.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulário para Gerar Novo Link -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Gerar Novo Link
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Selecionar Documento</label>
                            <select name="documento_id" class="form-select" required>
                                <option value="">Selecione um documento...</option>
                                <?php foreach ($documentos as $doc): ?>
                                    <option value="<?php echo $doc['id']; ?>">
                                        <?php echo htmlspecialchars($doc['titulo']); ?> 
                                        (<?php echo $doc['status']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Acesso</label>
                            <select name="tipo" class="form-select" required>
                                <option value="visualizacao">Apenas Visualização</option>
                                <option value="download">Visualização e Download</option>
                                <option value="assinatura">Visualização, Download e Assinatura</option>
                            </select>
                            <div class="form-text">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Assinatura:</strong> Permite que outras pessoas assinem o documento
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expira em (horas)</label>
                                <input type="number" name="expira_horas" class="form-control" 
                                       value="48" min="1" max="720" required>
                                <div class="form-text">Máximo: 30 dias (720 horas)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Máximo de Usos (opcional)</label>
                                <input type="number" name="max_usos" class="form-control" 
                                       min="1" placeholder="Ilimitado">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Senha (opcional)</label>
                            <input type="password" name="senha" class="form-control" 
                                   placeholder="Proteger link com senha">
                        </div>

                        <button type="submit" name="gerar_link" class="btn btn-success w-100">
                            <i class="fas fa-link me-2"></i>Gerar Link Seguro
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista de Links Existentes -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Meus Links de Compartilhamento
                        <span class="badge bg-light text-primary ms-2"><?php echo count($links); ?></span>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($links)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-share-alt fa-3x mb-3"></i>
                            <h5>Nenhum link gerado</h5>
                            <p>Use o formulário ao lado para gerar seu primeiro link de compartilhamento</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Documento</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Usos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($links as $link): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($link['documento_titulo']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                Expira: <?php echo $link['expira_em_formatado']; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                            $tipo_config = [
                                                'visualizacao' => ['icon' => 'eye', 'class' => 'info', 'text' => 'Visualização'],
                                                'download' => ['icon' => 'download', 'class' => 'warning', 'text' => 'Download'],
                                                'assinatura' => ['icon' => 'signature', 'class' => 'success', 'text' => 'Assinatura']
                                            ];
                                            $config = $tipo_config[$link['tipo']];
                                            ?>
                                            <span class="badge bg-<?php echo $config['class']; ?>">
                                                <i class="fas fa-<?php echo $config['icon']; ?> me-1"></i>
                                                <?php echo $config['text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_config = [
                                                'ativo' => ['icon' => 'check', 'class' => 'success', 'text' => 'Ativo'],
                                                'desativado' => ['icon' => 'pause', 'class' => 'secondary', 'text' => 'Desativado'],
                                                'expirado' => ['icon' => 'clock', 'class' => 'warning', 'text' => 'Expirado'],
                                                'limite_atingido' => ['icon' => 'times', 'class' => 'danger', 'text' => 'Limite']
                                            ];
                                            $status = $link['status'];
                                            $status_conf = $status_config[$status];
                                            ?>
                                            <span class="badge bg-<?php echo $status_conf['class']; ?>">
                                                <i class="fas fa-<?php echo $status_conf['icon']; ?> me-1"></i>
                                                <?php echo $status_conf['text']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $link['usos']; ?>
                                            <?php if ($link['max_usos']): ?>
                                                /<?php echo $link['max_usos']; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <!-- Copiar Link -->
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="copiarLink('<?php echo $link['url_completa']; ?>')"
                                                        data-bs-toggle="tooltip" title="Copiar link">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                
                                                <!-- Ativar/Desativar -->
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                                    <button type="submit" name="toggle_link" 
                                                            class="btn btn-<?php echo $link['ativo'] ? 'warning' : 'success'; ?>"
                                                            data-bs-toggle="tooltip" 
                                                            title="<?php echo $link['ativo'] ? 'Desativar' : 'Ativar'; ?>">
                                                        <i class="fas fa-<?php echo $link['ativo'] ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                                
                                                <!-- Estatísticas -->
                                                <button type="button" class="btn btn-outline-info"
                                                        onclick="verEstatisticas(<?php echo $link['id']; ?>)"
                                                        data-bs-toggle="tooltip" title="Estatísticas">
                                                    <i class="fas fa-chart-bar"></i>
                                                </button>
                                                
                                                <!-- Excluir -->
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Tem certeza que deseja excluir este link?')">
                                                    <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                                    <button type="submit" name="excluir_link" 
                                                            class="btn btn-outline-danger"
                                                            data-bs-toggle="tooltip" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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

<!-- Modal para Estatísticas -->
<div class="modal fade" id="modalEstatisticas">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Estatísticas do Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conteudoEstatisticas">
                Carregando...
            </div>
        </div>
    </div>
</div>

<script>
function copiarLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        // Mostrar feedback
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(function(err) {
        alert('Erro ao copiar: ' + err);
    });
}

function verEstatisticas(linkId) {
    // Em uma implementação real, faria uma requisição AJAX
    // Por enquanto, mostra informações básicas
    const conteudo = `
        <div class="text-center">
            <i class="fas fa-chart-bar fa-3x text-primary mb-3"></i>
            <h6>Estatísticas do Link</h6>
            <p>Funcionalidade em desenvolvimento</p>
            <p>Em breve: gráficos de acesso, IPs, horários, etc.</p>
        </div>
    `;
    document.getElementById('conteudoEstatisticas').innerHTML = conteudo;
    new bootstrap.Modal(document.getElementById('modalEstatisticas')).show();
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