<?php
// certificado_assinatura_externa.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/AssinaturaExterna.php';
require_once 'classes/Documento.php';

$assinatura_id = $_GET['id'] ?? null;

if (!$assinatura_id) {
    die("ID da assinatura não especificado");
}

try {
    // Buscar dados da assinatura externa
    $sql = "SELECT ae.*, se.email, se.nome as nome_convite, d.titulo, d.hash_documento 
            FROM assinaturas_externas ae
            JOIN signatarios_externos se ON ae.signatario_externo_id = se.id
            JOIN documentos d ON ae.documento_id = d.id
            WHERE ae.id = :id";
    
    $conn = Conexao::getConexao();
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $assinatura_id]);
    $assinatura = $stmt->fetch();

    if (!$assinatura) {
        die("Assinatura não encontrada");
    }

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Assinatura - AssinDocs</title>
    <style>
       
    </style>
</head>
<body>
    <div class="certificado">
        <div class="selo-oficial">
            <h1>CERTIFICADO DE ASSINATURA DIGITAL</h1>
            <p><em>AssinDocs - Plataforma Legalmente Reconhecida</em></p>
        </div>

        <h2>Documento: <?php echo htmlspecialchars($assinatura['titulo']); ?></h2>
        
        <div class="dados-assinatura">
            <h3>Informações do Signatário</h3>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($assinatura['nome_completo']); ?></p>
            <p><strong>CPF:</strong> <?php echo $assinatura['cpf']; ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($assinatura['email']); ?></p>
            <p><strong>Data e Hora da Assinatura:</strong> <?php echo date('d/m/Y H:i:s', strtotime($assinatura['criado_em'])); ?></p>
            <p><strong>IP de Origem:</strong> <?php echo $assinatura['ip_address']; ?></p>
        </div>

        <h3>Hash da Assinatura Digital</h3>
        <div class="hash-box">
            <?php echo $assinatura['hash_assinatura']; ?>
        </div>
        <p><small>Este hash único comprova a autenticidade e integridade da assinatura.</small></p>

        <h3>📄 Hash do Documento Original</h3>
        <div class="hash-box">
            <?php echo $assinatura['hash_documento']; ?>
        </div>
        <p><small>Garante que o documento não foi alterado após a assinatura.</small></p>

        <?php 
        $dados_identificacao = json_decode($assinatura['dados_identificacao'], true);
        if ($dados_identificacao): 
        ?>
        <h3>🆔 Documento de Identificação</h3>
        <p><strong>Tipo:</strong> <?php echo $dados_identificacao['tipo_documento']; ?></p>
        <p><strong>Número:</strong> <?php echo $dados_identificacao['numero_documento']; ?></p>
        <?php if ($dados_identificacao['data_emissao']): ?>
        <p><strong>Data de Emissão:</strong> <?php echo $dados_identificacao['data_emissao']; ?></p>
        <?php endif; ?>
        <?php endif; ?>

        <div class="carimbo-temporal">
            <p>Certificado gerado em: <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>ID do Certificado: <?php echo $assinatura_id; ?></p>
        </div>

       <?php include "includes/footer.php";?>