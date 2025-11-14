<?php
class Arquivo
{
    public static function uploadPDF($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro no upload do arquivo.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return ['success' => false, 'message' => 'Apenas arquivos PDF são permitidos.'];
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Arquivo muito grande (máx. 5MB).']; // CORRIGIDO: faltava =>
        }

        // GERAR HASH DO ARQUIVO
        $file_hash = hash_file('sha256', $file['tmp_name']);
        $nomeFinal = uniqid() . ".pdf";
        
        // CAMINHO CORRIGIDO - usar caminho absoluto da raiz do projeto
        $destino = $_SERVER['DOCUMENT_ROOT'] . '/Assindocs/uploads/' . $nomeFinal;
        $pasta_uploads = $_SERVER['DOCUMENT_ROOT'] . '/Assindocs/uploads/';

        // Criar pasta uploads se não existir
        if (!is_dir($pasta_uploads)) {
            if (!mkdir($pasta_uploads, 0755, true)) {
                return ['success' => false, 'message' => 'Não foi possível criar a pasta uploads.'];
            }
        }

        // Verificar se a pasta é gravável
        if (!is_writable($pasta_uploads)) {
            // Tentar corrigir permissões
            if (!chmod($pasta_uploads, 0755)) {
                return ['success' => false, 'message' => 'Pasta uploads não tem permissão de escrita.'];
            }
        }

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            return [
                'success' => true, 
                'path' => 'uploads/' . $nomeFinal,
                'hash' => $file_hash
            ];
        }

        return ['success' => false, 'message' => 'Falha ao salvar o arquivo. Verifique as permissões da pasta.'];
    }
    
    public static function verificarIntegridade($caminho_arquivo) {
        $caminho_absoluto = $_SERVER['DOCUMENT_ROOT'] . '/Assindocs/' . $caminho_arquivo;
        
        if (!file_exists($caminho_absoluto)) {
            return false;
        }
        return hash_file('sha256', $caminho_absoluto);
    }

    public static function validarPDF($caminho_arquivo) {
        $caminho_absoluto = $_SERVER['DOCUMENT_ROOT'] . '/Assindocs/' . $caminho_arquivo;
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $caminho_absoluto);
        finfo_close($finfo);
        
        return $mime_type === 'application/pdf';
    }
}
?>