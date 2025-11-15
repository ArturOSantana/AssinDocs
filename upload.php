<?php
// upload.php - VERSÃO CORRIGIDA
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificação de sessão
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// INCLUSÕES SIMPLIFICADAS
require_once 'classes/Arquivo.php';
require_once 'classes/Documento.php';
require_once 'classes/Projeto.php';

// DEBUG: Verificar e carregar AssinaturaExterna
if (file_exists('classes/AssinaturaExterna.php')) {
    require_once 'classes/AssinaturaExterna.php';
} else {
    error_log("AVISO: AssinaturaExterna.php não encontrado. Usando sistema antigo.");
}

include 'includes/header_logado.php';

$msg = '';
$tipo_msg = '';
$usuario_id = $_SESSION['usuario_id'];

try {
    $projeto = new Projeto();
    $projetos = $projeto->listarProjetos($usuario_id);
} catch (Exception $e) {
    die("Erro ao carregar projetos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titulo = htmlspecialchars(trim($_POST['titulo']));
        $projeto_id = (int)$_POST['projeto_id'];
        $signatarios = $_POST['signatarios'] ?? [];

        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = Arquivo::uploadPDF($_FILES['arquivo']);

            if ($upload['success']) {
                $doc = new Documento();

                // CORREÇÃO: Passar o hash como parâmetro
                $id = $doc->criarDocumento(
                    $usuario_id,
                    $projeto_id,
                    $titulo,
                    $upload['path'],
                    $upload['hash'] // ← PARÂMETRO ADICIONADO
                );

                if ($id) {
                    // Adicionar signatários - VERSÃO LIMPA
                    $signatarios_adicionados = 0;

                    foreach ($signatarios as $email) {
                        $email_limpo = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

                        if (!empty($email_limpo) && filter_var($email_limpo, FILTER_VALIDATE_EMAIL)) {

                            if (class_exists('AssinaturaExterna')) {
                                $assinatura_externa = new AssinaturaExterna();
                                $resultado = $assinatura_externa->adicionarSignatario(
                                    $id,
                                    'Signatário',
                                    $email_limpo,
                                    ''
                                );

                                if ($resultado['success']) {
                                    $signatarios_adicionados++;
                                    // Opcional: enviar email aqui
                                } else {
                                    // Fallback para sistema antigo
                                    $doc->adicionarSignatario($id, 'Signatário', $email_limpo);
                                    $signatarios_adicionados++;
                                }
                            } else {
                                // Sistema antigo
                                $doc->adicionarSignatario($id, 'Signatário', $email_limpo);
                                $signatarios_adicionados++;
                            }
                        }
                    }

                    $msg = 'Documento enviado com sucesso! Hash: ' . substr($upload['hash'], 0, 16) . '...';
                    $msg .= ' Signatários: ' . $signatarios_adicionados;
                    $tipo_msg = 'success';
                } else {
                    $msg = 'Erro ao salvar documento no banco de dados.';
                    $tipo_msg = 'danger';
                }
            } else {
                $msg = $upload['message'];
                $tipo_msg = 'danger';
            }
        } else {
            $msg = 'Nenhum arquivo selecionado.';
            $tipo_msg = 'danger';
        }
    } catch (Exception $e) {
        $msg = 'Erro interno: ' . $e->getMessage();
        $tipo_msg = 'danger';
    }
}
?>

<div class="container mt-5">
    <h2>Enviar Novo Documento</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $tipo_msg; ?>">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título do Documento</label>
            <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex.: Contrato de Prestação de Serviços" required>
        </div>

        <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo PDF (máx. 5MB)</label>
            <input type="file" id="arquivo" name="arquivo" accept=".pdf" class="form-control" required onchange="previewPDF(this)">
            <div class="form-text">Apenas arquivos PDF são permitidos. O sistema gerará um hash SHA-256 para verificação de integridade.</div>
            <div id="pdf-preview" class="mt-3" style="display:none;">
                <iframe id="pdf-iframe" width="100%" height="400px" style="border: 1px solid #ddd; border-radius: 5px;"></iframe>
            </div>
        </div>

        <div class="mb-3">
            <label for="projeto_id" class="form-label">Projeto (opcional)</label>
            <select id="projeto_id" name="projeto_id" class="form-control">
                <option value="">Selecione um projeto</option>
                <?php foreach ($projetos as $p): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Signatários (e-mails)</label>
            <div id="signatarios-container">
                <input type="email" name="signatarios[]" class="form-control mb-2" placeholder="E-mail do primeiro signatário" required>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addSignatario()">
                <i class="fas fa-plus"></i> Adicionar Signatário
            </button>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> Enviar Documento
        </button>
        <a href="dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
    </form>
</div>


<?php
include 'includes/footer.php';
?>