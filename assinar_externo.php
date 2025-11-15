<?php
// assinar_externo.php - VERSÃO COM ASSINATURA VISUAL
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/AssinaturaExterna.php';
require_once 'classes/Documento.php';
require_once 'classes/AssinaturaDigital.php';
require_once 'classes/GeradorAssinatura.php'; // NOVA CLASSE

$token = $_GET['token'] ?? null;
$msg = '';
$tipo_msg = '';

if (!$token) {
    die("Token de acesso não fornecido. Verifique o link recebido.");
}

try {
    $assinatura_externa = new AssinaturaExterna();
    $doc_class = new Documento();
    $assinatura_digital = new AssinaturaDigital();
    $gerador_assinatura = new GeradorAssinatura(); // NOVO
    
    // Buscar informações do signatário
    $signatario = $assinatura_externa->buscarSignatarioPorToken($token);
    
    if (!$signatario) {
        die("Token inválido ou expirado. Entre em contato com o remetente.");
    }

    if ($signatario['assinado']) {
        $msg = "Você já assinou este documento.";
        $tipo_msg = 'info';
    }

    $documento = $doc_class->buscarParaCertificado($signatario['documento_id']);

    // Processar assinatura
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$signatario['assinado']) {
        $dados_assinante = [
            'nome_completo' => htmlspecialchars(trim($_POST['nome_completo'])),
            'cpf' => $_POST['cpf'],
            'email' => filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL),
            'tipo_documento' => $_POST['tipo_documento'],
            'numero_documento' => $_POST['numero_documento'],
            'data_emissao' => $_POST['data_emissao']
        ];

        $resultado = $assinatura_externa->processarAssinatura(
            $token, 
            $dados_assinante, 
            $_FILES['documento_identificacao']
        );

        if ($resultado['success']) {
            // GERAR CERTIFICADO DE ASSINATURA VISUAL
            $assinaturas = $assinatura_digital->listarAssinaturasDocumento($signatario['documento_id']);
            
            // Encontrar a assinatura específica deste usuário
            $minha_assinatura = null;
            foreach ($assinaturas as $assinatura) {
                if ($assinatura['email'] === $signatario['email']) {
                    $minha_assinatura = $assinatura;
                    break;
                }
            }
            
            if ($minha_assinatura) {
                // Gerar PDF com assinatura visual
                $pdf = $gerador_assinatura->gerarCertificadoAssinatura($documento, $minha_assinatura);
                
                // Salvar o certificado
                $nome_certificado = 'certificado_assinatura_' . $resultado['assinatura_id'] . '.pdf';
                $caminho_certificado = 'uploads/certificados/' . $nome_certificado;
                
                // Criar pasta se não existir
                if (!is_dir('uploads/certificados/')) {
                    mkdir('uploads/certificados/', 0755, true);
                }
                
                $pdf->Output('F', $caminho_certificado);
                
                // Atualizar mensagem com link para download do certificado
                $msg = $resultado['message'] . " ";
                $msg .= "<a href='{$resultado['certificado_url']}' target='_blank' class='btn btn-success btn-sm'>Ver Certificado Online</a> ";
                $msg .= "<a href='{$caminho_certificado}' download class='btn btn-primary btn-sm'>Baixar Certificado PDF</a>";
            } else {
                $msg = $resultado['message'] . " <a href='{$resultado['certificado_url']}' target='_blank'>Ver Certificado</a>";
            }
            
            $tipo_msg = 'success';
            $signatario['assinado'] = 1;
        } else {
            $msg = $resultado['message'];
            $tipo_msg = 'danger';
        }
    }

} catch (Exception $e) {
    die("Erro no sistema: " . $e->getMessage());
}

include "includes/header.php";
?>

<style>
.documento-info {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin-bottom: 20px;
}
.requerido::after {
    content: " *";
    color: red;
}
.hash-box {
    background: #e9ecef;
    padding: 10px;
    border-radius: 5px;
    font-family: monospace;
    font-size: 0.9em;
    word-break: break-all;
}
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-signature me-2"></i>Assinar Documento Digitalmente
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-<?php echo $tipo_msg; ?>">
                            <?php echo $msg; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Informações do Documento -->
                    <div class="documento-info">
                        <h5><?php echo htmlspecialchars($documento['titulo']); ?></h5>
                        <p class="mb-1"><strong>Hash do Documento:</strong></p>
                        <div class="hash-box">
                            <?php echo $documento['hash_documento']; ?>
                        </div>
                        <small class="text-muted">Este hash garante a integridade do documento.</small>
                    </div>

                    <?php if (!$signatario['assinado']): ?>
                    <!-- Formulário de Assinatura -->
                    <form method="POST" enctype="multipart/form-data" id="formAssinatura">
                        <h5 class="mb-3">Seus Dados para Assinatura</h5>

                        <!-- Dados Pessoais -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label requerido">Nome Completo</label>
                                <input type="text" name="nome_completo" class="form-control" 
                                       value="<?php echo htmlspecialchars($signatario['nome']); ?>" 
                                       required readonly>
                                <small class="text-muted">Nome conforme convite</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label requerido">CPF</label>
                                <input type="text" name="cpf" class="form-control cpf-mask" 
                                       value="<?php echo $signatario['cpf']; ?>" 
                                       required readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label requerido">E-mail</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($signatario['email']); ?>" 
                                       required readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label requerido">Tipo de Documento</label>
                                <select name="tipo_documento" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <option value="RG">RG</option>
                                    <option value="CNH">CNH</option>
                                    <option value="PASSAPORTE">Passaporte</option>
                                    <option value="OUTRO">Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label requerido">Número do Documento</label>
                                <input type="text" name="numero_documento" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data de Emissão</label>
                                <input type="date" name="data_emissao" class="form-control">
                            </div>
                        </div>

                        <!-- Documento de Identificação -->
                        <div class="mb-4">
                            <label class="form-label requerido">Documento de Identificação</label>
                            <input type="file" name="documento_identificacao" class="form-control" 
                                   accept=".jpg,.jpeg,.png,.pdf" required>
                            <small class="text-muted">
                                Envie uma foto ou scan do seu documento (RG, CNH ou Passaporte). 
                                Formatos: JPG, PNG, PDF. Tamanho máximo: 2MB.
                            </small>
                        </div>

                        <!-- Termos e Condições -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="termos" required>
                                <label class="form-check-label" for="termos">
                                    Declaro, sob as penas da lei, que as informações fornecidas são verdadeiras 
                                    e que concordo com os termos deste documento. Reconheço a validade jurídica 
                                    desta assinatura digital.
                                </label>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-fingerprint me-2"></i>Assinar Documento Digitalmente
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>Documento Assinado</h4>
                            <p>Você já assinou este documento digitalmente.</p>
                            <a href="<?php echo $assinatura_externa->gerarLinkCertificado($signatario['id']); ?>" 
                               class="btn btn-primary" target="_blank">
                                <i class="fas fa-certificate me-2"></i>Ver Certificado de Assinatura
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informações de Segurança -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-shield-alt me-2"></i>Segurança e Validade Jurídica</h6>
                    <ul class="small text-muted">
                        <li>Assinatura registrada com hash SHA-256 para garantia de integridade</li>
                        <li>Registro de IP, data/hora e user agent para rastreabilidade</li>
                        <li>Documento de identificação armazenado criptografado</li>
                        <li>Conformidade com MP 2.200-2/2001 (ICP-Brasil)</li>
                        <li>Carimbo temporal para validade jurídica</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CPF
    const cpfInput = document.querySelector('.cpf-mask');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2')
                            .replace(/(\d{3})(\d)/, '$1.$2')
                            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Validação do formulário
    const form = document.getElementById('formAssinatura');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = form.querySelector('input[type="file"]');
            const termos = form.querySelector('#termos');
            
            if (fileInput.files[0] && fileInput.files[0].size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('Arquivo muito grande! O tamanho máximo é 2MB.');
                return;
            }
            
            if (!termos.checked) {
                e.preventDefault();
                alert('Você deve aceitar os termos e condições para assinar.');
                termos.focus();
                return;
            }
        });
    }
});
</script>

<?php include "includes/footer.php"; ?>