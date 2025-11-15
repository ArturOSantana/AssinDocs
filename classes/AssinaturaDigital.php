<?php
require_once __DIR__ . '/Conexao.php';

class AssinaturaDigital
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Gerar par de chaves RSA para um usuário
     */
    public function gerarParChaves($usuario_id)
    {
        try {
            // Configuração para gerar chaves RSA
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );

            // Gerar par de chaves
            $keypair = openssl_pkey_new($config);

            if (!$keypair) {
                throw new Exception("Erro ao gerar par de chaves: " . openssl_error_string());
            }

            // Extrair chave privada
            openssl_pkey_export($keypair, $private_key);

            // Extrair chave pública
            $public_key_details = openssl_pkey_get_details($keypair);
            $public_key = $public_key_details['key'];

            // Criptografar chave privada
            $senha_criptografia = $this->gerarChaveCriptografia($usuario_id);
            $private_key_criptografada = $this->criptografarChavePrivada($private_key, $senha_criptografia);

            // Salvar no banco
            $sql = "INSERT INTO chaves_usuarios (usuario_id, chave_publica, chave_privada_criptografada) 
                    VALUES (:usuario_id, :publica, :privada)
                    ON DUPLICATE KEY UPDATE 
                    chave_publica = :publica, 
                    chave_privada_criptografada = :privada,
                    atualizado_em = CURRENT_TIMESTAMP";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':publica' => $public_key,
                ':privada' => $private_key_criptografada
            ]);

            if ($result) {
                // Registrar log
                $this->registrarLog(null, $usuario_id, 'GERAR_CHAVES', 'Par de chaves RSA gerado com sucesso');

                return [
                    'success' => true,
                    'public_key' => $public_key,
                    'message' => 'Par de chaves gerado com sucesso'
                ];
            } else {
                throw new Exception("Erro ao salvar chaves no banco");
            }
        } catch (Exception $e) {
            error_log("Erro ao gerar par de chaves: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Assinar um documento
     */
    public function assinarDocumento($documento_id, $usuario_id)
    {
        try {
            // Buscar hash do documento
            $hash_documento = $this->buscarHashDocumento($documento_id);
            if (!$hash_documento) {
                throw new Exception("Hash do documento não encontrado");
            }

            // Verificar se usuário já assinou este documento
            if ($this->verificarSeJaAssinou($documento_id, $usuario_id)) {
                throw new Exception("Você já assinou este documento");
            }

            // Buscar chave privada do usuário
            $chave_privada = $this->buscarChavePrivada($usuario_id);

            if (!$chave_privada) {
                // Gerar chaves se não existirem
                $geracao = $this->gerarParChaves($usuario_id);
                if (!$geracao['success']) {
                    throw new Exception("Falha ao gerar chaves: " . $geracao['message']);
                }
                $chave_privada = $this->buscarChavePrivada($usuario_id);
            }

            // Assinar o hash do documento
            $assinatura = '';
            $result = openssl_sign($hash_documento, $assinatura, $chave_privada, OPENSSL_ALGO_SHA256);

            if (!$result) {
                throw new Exception("Erro ao assinar documento: " . openssl_error_string());
            }

            // Codificar assinatura em base64 para armazenamento
            $assinatura_base64 = base64_encode($assinatura);

            // CORREÇÃO: Usar o nome correto da coluna 'assinatura'
            $sql = "INSERT INTO assinaturas_digitais 
        (documento_id, usuario_id, assinatura, ip_address, user_agent) 
        VALUES (:doc_id, :user_id, :assinatura, :ip, :ua)";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':doc_id' => $documento_id,
                ':user_id' => $usuario_id,
                ':assinatura' => $assinatura_base64,  // Nome corrigido
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':ua' => $_SERVER['HTTP_USER_AGENT']
            ]);

            if ($result) {
                // Atualizar status do documento se necessário
                $this->atualizarStatusDocumento($documento_id);

                // Registrar log
                $this->registrarLog($documento_id, $usuario_id, 'ASSINAR', 'Documento assinado digitalmente');

                return [
                    'success' => true,
                    'assinatura' => $assinatura_base64,
                    'assinatura_curta' => substr($assinatura_base64, 0, 50) . '...',
                    'message' => 'Documento assinado com sucesso!'

                ];
            } else {
                throw new Exception("Erro ao salvar assinatura no banco");
            }
            $auditoria = new Auditoria();
            $auditoria->registrar(
                'ASSINATURA_DOCUMENTO',
                'assinatura',
                $usuario_id,
                $documento_id,
                "Documento assinado digitalmente",
                ['assinatura_hash' => substr($assinatura_base64, 0, 50)]
            );
        } catch (Exception $e) {
            error_log("Erro ao assinar documento: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verificar assinatura de um documento
     */
    public function verificarAssinatura($documento_id, $usuario_id)
    {
        try {
            // Buscar assinatura e hash do documento
            $assinatura_info = $this->buscarAssinatura($documento_id, $usuario_id);
            $hash_documento = $this->buscarHashDocumento($documento_id);
            $chave_publica = $this->buscarChavePublica($usuario_id);

            if (!$assinatura_info || !$hash_documento || !$chave_publica) {
                return [
                    'success' => false,
                    'valida' => false,
                    'message' => 'Dados insuficientes para verificação'
                ];
            }

            // CORREÇÃO: Usar o nome correto da coluna 'assinatura'
            $assinatura_bin = base64_decode($assinatura_info['assinatura']);

            // Verificar assinatura
            $result = openssl_verify($hash_documento, $assinatura_bin, $chave_publica, OPENSSL_ALGO_SHA256);

            if ($result === 1) {
                return [
                    'success' => true,
                    'valida' => true,
                    'message' => 'Assinatura válida e autêntica',
                    'assinatura_info' => $assinatura_info
                ];
            } elseif ($result === 0) {
                return [
                    'success' => true,
                    'valida' => false,
                    'message' => 'Assinatura inválida - documento pode ter sido alterado'
                ];
            } else {
                throw new Exception("Erro na verificação: " . openssl_error_string());
            }
        } catch (Exception $e) {
            error_log("Erro ao verificar assinatura: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verificar integridade do documento (hash)
     */
    public function verificarIntegridadeDocumento($documento_id)
    {
        try {
            $documento = $this->buscarDocumento($documento_id);
            if (!$documento || !$documento['hash_documento']) {
                return ['success' => false, 'integridade' => false, 'message' => 'Documento ou hash não encontrado'];
            }

            // Verificar se arquivo existe
            if (!file_exists($documento['arquivo_path'])) {
                return ['success' => false, 'integridade' => false, 'message' => 'Arquivo físico não encontrado'];
            }

            // Calcular hash atual
            $hash_atual = hash_file('sha256', $documento['arquivo_path']);
            $integridade_ok = ($hash_atual === $documento['hash_documento']);

            return [
                'success' => true,
                'integridade' => $integridade_ok,
                'hash_original' => $documento['hash_documento'],
                'hash_atual' => $hash_atual,
                'message' => $integridade_ok ? 'Integridade preservada' : 'ALERTA: Documento alterado!'
            ];
        } catch (Exception $e) {
            error_log("Erro ao verificar integridade: " . $e->getMessage());
            return ['success' => false, 'integridade' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Buscar todas as assinaturas de um documento
     */
    public function listarAssinaturasDocumento($documento_id)
    {
        try {
            $sql = "SELECT ad.*, u.nome as usuario_nome, u.email 
                    FROM assinaturas_digitais ad
                    JOIN usuarios u ON ad.usuario_id = u.id
                    WHERE ad.documento_id = :doc_id
                    ORDER BY ad.timestamp DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':doc_id' => $documento_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erro ao listar assinaturas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar status completo do documento
     */
    public function verificarStatusCompleto($documento_id)
    {
        $resultado = [
            'documento_id' => $documento_id,
            'integridade' => false,
            'assinaturas' => [],
            'status_geral' => 'INVALIDO'
        ];

        // Verificar integridade
        $integridade = $this->verificarIntegridadeDocumento($documento_id);
        $resultado['integridade'] = $integridade['success'] ? $integridade['integridade'] : false;
        $resultado['detalhes_integridade'] = $integridade;

        // Buscar assinaturas
        $resultado['assinaturas'] = $this->listarAssinaturasDocumento($documento_id);

        // Verificar cada assinatura
        foreach ($resultado['assinaturas'] as &$assinatura) {
            $verificacao = $this->verificarAssinatura($documento_id, $assinatura['usuario_id']);
            $assinatura['valida'] = $verificacao['success'] ? $verificacao['valida'] : false;
            $assinatura['detalhes_verificacao'] = $verificacao;
        }

        // Determinar status geral
        if (!$resultado['integridade']) {
            $resultado['status_geral'] = 'CORROMPIDO';
        } elseif (empty($resultado['assinaturas'])) {
            $resultado['status_geral'] = 'NAO_ASSINADO';
        } else {
            $todas_validas = true;
            foreach ($resultado['assinaturas'] as $assinatura) {
                if (!$assinatura['valida']) {
                    $todas_validas = false;
                    break;
                }
            }
            $resultado['status_geral'] = $todas_validas ? 'VALIDO' : 'ASSINATURAS_INVALIDAS';
        }

        return $resultado;
    }

    // ========== MÉTODOS PRIVADOS ==========

    private function buscarChavePrivada($usuario_id)
    {
        $sql = "SELECT chave_privada_criptografada FROM chaves_usuarios WHERE usuario_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $usuario_id]);
        $result = $stmt->fetch();

        if ($result) {
            $senha_criptografia = $this->gerarChaveCriptografia($usuario_id);
            return $this->descriptografarChavePrivada($result['chave_privada_criptografada'], $senha_criptografia);
        }

        return null;
    }

    private function buscarChavePublica($usuario_id)
    {
        $sql = "SELECT chave_publica FROM chaves_usuarios WHERE usuario_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $usuario_id]);
        $result = $stmt->fetch();

        return $result ? $result['chave_publica'] : null;
    }

    private function buscarAssinatura($documento_id, $usuario_id)
    {
        $sql = "SELECT * FROM assinaturas_digitais 
                WHERE documento_id = :doc_id AND usuario_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':doc_id' => $documento_id, ':user_id' => $usuario_id]);
        return $stmt->fetch();
    }

    private function buscarHashDocumento($documento_id)
    {
        $sql = "SELECT hash_documento FROM documentos WHERE id = :doc_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':doc_id' => $documento_id]);
        $result = $stmt->fetch();

        return $result ? $result['hash_documento'] : null;
    }

    private function buscarDocumento($documento_id)
    {
        $sql = "SELECT * FROM documentos WHERE id = :doc_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':doc_id' => $documento_id]);
        return $stmt->fetch();
    }

    private function verificarSeJaAssinou($documento_id, $usuario_id)
    {
        $sql = "SELECT id FROM assinaturas_digitais 
                WHERE documento_id = :doc_id AND usuario_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':doc_id' => $documento_id, ':user_id' => $usuario_id]);
        return $stmt->fetch() !== false;
    }

    private function atualizarStatusDocumento($documento_id)
    {
        // Verificar se todos os signatários assinaram
        $sql = "UPDATE documentos SET status = 'assinado' WHERE id = :doc_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':doc_id' => $documento_id]);
    }

    private function registrarLog($documento_id, $usuario_id, $acao, $descricao)
    {
        try {
            $sql = "INSERT INTO logs_assinatura 
                    (documento_id, usuario_id, acao, descricao, ip_address, user_agent) 
                    VALUES (:doc_id, :user_id, :acao, :descricao, :ip, :ua)";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':doc_id' => $documento_id,
                ':user_id' => $usuario_id,
                ':acao' => $acao,
                ':descricao' => $descricao,
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
        } catch (Exception $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
            return false;
        }
    }

    private function gerarChaveCriptografia($usuario_id)
    {
        // Em produção, use uma chave mais segura baseada em dados do usuário + chave master
        return hash('sha256', 'chave_secreta_' . $usuario_id . '_assinadocs_2025');
    }

    private function criptografarChavePrivada($private_key, $password)
    {
        $method = 'aes-256-cbc';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encrypted = openssl_encrypt($private_key, $method, $password, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function descriptografarChavePrivada($encrypted_data, $password)
    {
        $method = 'aes-256-cbc';
        $data = base64_decode($encrypted_data);
        $iv_length = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        return openssl_decrypt($encrypted, $method, $password, 0, $iv);
    }
}
