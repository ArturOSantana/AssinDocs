<?php
// certificado_assinatura.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/Documento.php';
require_once 'classes/AssinaturaDigital.php';
require_once 'classes/Usuario.php';

$documento_id = $_GET['id'] ?? null;

if (!$documento_id) {
    die("ID do documento não especificado");
}

try {
    $doc_class = new Documento();
    $assinatura_class = new AssinaturaDigital();
    $usuario_class = new Usuario();
    
    // Buscar documento (sem restrição de usuário para visualização pública)
    $documento = $doc_class->buscarParaCertificado($documento_id);
    
    if (!$documento) {
        die("Documento não encontrado");
    }

    // Buscar assinaturas
    $assinaturas = $assinatura_class->listarAssinaturasDocumento($documento_id);
    $status_completo = $assinatura_class->verificarStatusCompleto($documento_id);

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Assinatura - <?php echo htmlspecialchars($documento['titulo']); ?></title>
    <style>
        .certificado {
            border: 2px solid #000;
            padding: 30px;
            margin: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(white, #f9f9f9);
        }
        .selo-assinatura {
            border: 1px solid #28a745;
            padding: 15px;
            margin: 10px 0;
            background: #f8fff9;
            border-radius: 5px;
        }
        .assinatura-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .hash-code {
            font-family: monospace;
            background: #f4f4f4;
            padding: 5px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="certificado">
        <h2 style="text-align: center; color: #1e40af;">CERTIFICADO DE ASSINATURA DIGITAL</h2>
        
        <div class="selo-assinatura">
            <h3>📄 Documento: <?php echo htmlspecialchars($documento['titulo']); ?></h3>
            <p><strong>ID:</strong> <?php echo $documento_id; ?></p>
            <p><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($documento['criado_em'])); ?></p>
            <p><strong>Status:</strong> 
                <span style="color: <?php echo $status_completo['status_geral'] === 'VALIDO' ? 'green' : 'red'; ?>">
                    <?php echo $status_completo['status_geral']; ?>
                </span>
            </p>
        </div>

        <h3>Hash de Integridade</h3>
        <div class="hash-code">
            <?php echo $documento['hash_documento']; ?>
        </div>
        <p><small>Este hash garante que o documento não foi alterado desde a assinatura.</small></p>

        <h3>Assinaturas Registradas</h3>
        <?php if (empty($assinaturas)): ?>
            <p>Nenhuma assinatura registrada.</p>
        <?php else: ?>
            <?php foreach ($assinaturas as $assinatura): ?>
                <div class="assinatura-item">
                    <strong><?php echo htmlspecialchars($assinatura['usuario_nome']); ?></strong>
                    <br>
                    <small>Email: <?php echo $assinatura['email']; ?></small>
                    <br>
                    <small>Data: <?php echo date('d/m/Y H:i', strtotime($assinatura['timestamp'])); ?></small>
                    <br>
                    <small>Status: 
                        <span style="color: <?php echo $assinatura['valida'] ? 'green' : 'red'; ?>">
                            <?php echo $assinatura['valida'] ? 'VÁLIDA' : 'INVÁLIDA'; ?>
                        </span>
                    </small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top: 30px; text-align: center; border-top: 1px solid #ccc; padding-top: 15px;">
            <p><strong>AssinDocs</strong> - Plataforma Segura de Assinatura Digital</p>
            <p>Verificação realizada em: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>
</body>
</html>