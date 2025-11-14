// scripts/verificar_integridade.php
<?php
// Script para verificar integridade periódica
$documentos = $doc->listarTodosDocumentos();

foreach ($documentos as $doc) {
    $hash_atual = Arquivo::verificarIntegridade($doc['arquivo_path']);
    
    if ($hash_atual !== $doc['hash_documento']) {
        // Documento corrompido!
        Auditoria::registrar(
            'Violação de integridade', 
            'seguranca', 
            null, 
            $doc['id'],
            "Hash original: {$doc['hash_documento']}, Hash atual: $hash_atual"
        );
        
        // Notificar administrador
        Notificacao::enviar(1, 'Violação de Integridade', 
            "Documento {$doc['titulo']} foi alterado ilegalmente");
    }
}
?>