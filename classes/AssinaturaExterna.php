<?php
require_once __DIR__ . '/Conexao.php';

class AssinaturaExterna
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Adicionar signatário externo
     */
    public function adicionarSignatario($documento_id, $nome, $email, $cpf)
    {
        try {
            // Validar CPF
            if (!$this->validarCPF($cpf)) {
                return ['success' => false, 'message' => 'CPF inválido'];
            }

            // Verificar se já existe para este documento
            if ($this->signatarioExiste($documento_id, $email)) {
                return ['success' => false, 'message' => 'Signatário já convidado para este documento'];
            }

            $token = bin2hex(random_bytes(32));

            $sql = "INSERT INTO signatarios_externos 
                    (documento_id, nome, email, cpf, token_acesso) 
                    VALUES (:doc_id, :nome, :email, :cpf, :token)";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':doc_id' => $documento_id,
                ':nome' => htmlspecialchars($nome),
                ':email' => filter_var($email, FILTER_SANITIZE_EMAIL),
                ':cpf' => $this->formatarCPF($cpf),
                ':token' => $token
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'token' => $token,
                    'link_assinatura' => $this->gerarLinkAssinatura($token),
                    'signatario_id' => $this->conn->lastInsertId()
                ];
            }

            return ['success' => false, 'message' => 'Erro ao adicionar signatário'];
        } catch (Exception $e) {
            error_log("Erro ao adicionar signatário externo: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do sistema'];
        }
    }

    /**
     * Processar assinatura externa
     */
    public function processarAssinatura($token, $dados_assinante, $file_identificacao)
    {
        try {
            // Buscar signatário
            $signatario = $this->buscarSignatarioPorToken($token);
            if (!$signatario) {
                return ['success' => false, 'message' => 'Token inválido ou expirado'];
            }

            if ($signatario['assinado']) {
                return ['success' => false, 'message' => 'Documento já assinado por este signatário'];
            }

            // Validar dados
            $validacao = $this->validarDadosAssinatura($signatario, $dados_assinante);
            if (!$validacao['success']) {
                return $validacao;
            }

            // Processar documento de identificação
            $upload_identificacao = $this->processarDocumentoIdentificacao($file_identificacao);
            if (!$upload_identificacao['success']) {
                return $upload_identificacao;
            }

            // Gerar hash de assinatura
            $hash_assinatura = $this->gerarHashAssinatura($signatario, $dados_assinante);

            // Registrar assinatura
            $assinatura_id = $this->registrarAssinatura($signatario, $dados_assinante, $hash_assinatura, $upload_identificacao['path']);

            if ($assinatura_id) {
                // Marcar como assinado
                $this->marcarComoAssinado($signatario['id']);

                // Atualizar status do documento se necessário
                $this->atualizarStatusDocumento($signatario['documento_id']);

                return [
                    'success' => true,
                    'assinatura_id' => $assinatura_id,
                    'hash_assinatura' => $hash_assinatura,
                    'carimbo_temporal' => date('c'),
                    'certificado_url' => $this->gerarLinkCertificado($assinatura_id),
                    'message' => 'Documento assinado com sucesso!'
                ];
            }

            return ['success' => false, 'message' => 'Erro ao registrar assinatura'];
        } catch (Exception $e) {
            error_log("Erro ao processar assinatura externa: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do sistema'];
        }
    }

    /**
     * Buscar signatário por token - MÉTODO PÚBLICO PARA USO EXTERNO
     */
    public function buscarSignatarioPorToken($token)
    {
        $sql = "SELECT se.*, d.titulo as documento_titulo, d.hash_documento 
                FROM signatarios_externos se
                JOIN documentos d ON se.documento_id = d.id
                WHERE se.token_acesso = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    /**
     * Gerar link do certificado - MÉTODO PÚBLICO
     */
    public function gerarLinkCertificado($assinatura_id)
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $base_url . "/Assindocs/certificado_assinatura_externa.php?id=" . $assinatura_id;
    }

    /**
     * Validar CPF
     */
    private function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar se signatário já existe
     */
    private function signatarioExiste($documento_id, $email)
    {
        $sql = "SELECT id FROM signatarios_externos 
                WHERE documento_id = :doc_id AND email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':doc_id' => $documento_id,
            ':email' => $email
        ]);
        return $stmt->fetch() !== false;
    }

    /**
     * Formatador de CPF
     */
    private function formatarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    /**
     * Gerar hash único da assinatura
     */
    private function gerarHashAssinatura($signatario, $dados_assinante)
    {
        $dados_hash = [
            'documento_id' => $signatario['documento_id'],
            'signatario_id' => $signatario['id'],
            'nome' => $dados_assinante['nome_completo'],
            'cpf' => $dados_assinante['cpf'],
            'email' => $signatario['email'],
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'nonce' => bin2hex(random_bytes(16))
        ];

        return hash('sha256', json_encode($dados_hash));
    }

    /**
     * Processar upload do documento de identificação
     */
    private function processarDocumentoIdentificacao($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erro no upload do documento'];
        }

        // Validar tipo de arquivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $tipos_permitidos)) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido. Use JPG, PNG ou PDF'];
        }

        // Validar tamanho (máx 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Arquivo muito grande (máx. 2MB)'];
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nome_arquivo = 'identificacao_' . uniqid() . '.' . $ext;
        $pasta_uploads = $_SERVER['DOCUMENT_ROOT'] . '/Assindocs/uploads/identificacoes/';

        // Criar pasta se não existir
        if (!is_dir($pasta_uploads)) {
            mkdir($pasta_uploads, 0755, true);
        }

        $destino = $pasta_uploads . $nome_arquivo;

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            return [
                'success' => true,
                'path' => 'uploads/identificacoes/' . $nome_arquivo,
                'mime_type' => $mime_type
            ];
        }

        return ['success' => false, 'message' => 'Falha ao salvar documento'];
    }

    private function validarDadosAssinatura($signatario, $dados)
    {
        // Validar se CPF corresponde
        $cpf_limpo = preg_replace('/[^0-9]/', '', $signatario['cpf']);
        $cpf_fornecido = preg_replace('/[^0-9]/', '', $dados['cpf']);

        if ($cpf_limpo !== $cpf_fornecido) {
            return ['success' => false, 'message' => 'CPF não corresponde ao convite de assinatura'];
        }

        // Validar email
        if ($signatario['email'] !== $dados['email']) {
            return ['success' => false, 'message' => 'Email não corresponde ao convite de assinatura'];
        }

        // Validar nome (pode ter variações, mas deve ser similar)
        similar_text(strtolower($signatario['nome']), strtolower($dados['nome_completo']), $similaridade);
        if ($similaridade < 70) {
            return ['success' => false, 'message' => 'Nome não corresponde suficientemente ao convite'];
        }

        return ['success' => true];
    }

    private function registrarAssinatura($signatario, $dados, $hash_assinatura, $caminho_identificacao)
    {
        $sql = "INSERT INTO assinaturas_externas 
                (signatario_externo_id, documento_id, nome_completo, cpf, hash_assinatura, 
                 ip_address, user_agent, dados_identificacao, carimbo_temporal) 
                VALUES (:sig_id, :doc_id, :nome, :cpf, :hash, :ip, :ua, :identificacao, :carimbo)";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':sig_id' => $signatario['id'],
            ':doc_id' => $signatario['documento_id'],
            ':nome' => htmlspecialchars($dados['nome_completo']),
            ':cpf' => $this->formatarCPF($dados['cpf']),
            ':hash' => $hash_assinatura,
            ':ip' => $_SERVER['REMOTE_ADDR'],
            ':ua' => $_SERVER['HTTP_USER_AGENT'],
            ':identificacao' => json_encode([
                'tipo_documento' => $dados['tipo_documento'],
                'numero_documento' => $dados['numero_documento'],
                'caminho_arquivo' => $caminho_identificacao,
                'data_emissao' => $dados['data_emissao']
            ], JSON_UNESCAPED_UNICODE),
            ':carimbo' => date('c')
        ]);

        return $result ? $this->conn->lastInsertId() : false;
    }

    private function marcarComoAssinado($signatario_id)
    {
        $sql = "UPDATE signatarios_externos SET assinado = 1, assinado_em = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $signatario_id]);
    }

    private function atualizarStatusDocumento($documento_id)
    {
        // Lógica para atualizar status do documento quando todos assinarem
        // Por enquanto, apenas um placeholder
        return true;
    }

    private function gerarLinkAssinatura($token)
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $base_url . "/Assindocs/assinar_externo.php?token=" . $token;
    }

    // AssinaturaExterna.php - ADICIONE ESTE MÉTODO

    /**
     * Processar assinatura para links compartilhados (MÉTODO PÚBLICO)
     */
    public function processarAssinaturaExterna($token, $dados_assinante, $file_identificacao = null)
    {
        try {
            // Buscar signatário pelo token do link compartilhado
            $sql = "SELECT lc.*, d.id as documento_id, d.titulo, d.hash_documento 
                FROM links_compartilhamento lc
                JOIN documentos d ON lc.documento_id = d.id
                WHERE lc.token = :token AND lc.ativo = 1 AND lc.expira_em > NOW()";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':token' => $token]);
            $link = $stmt->fetch();

            if (!$link) {
                return ['success' => false, 'message' => 'Link inválido ou expirado'];
            }

            // Verificar se o tipo de link permite assinatura
            if (!in_array($link['tipo'], ['assinatura', 'download'])) {
                return ['success' => false, 'message' => 'Este link não permite assinatura'];
            }

            // Adicionar como signatário externo
            $resultado_signatario = $this->adicionarSignatario(
                $link['documento_id'],
                $dados_assinante['nome_completo'],
                $dados_assinante['email'],
                $dados_assinante['cpf']
            );

            if (!$resultado_signatario['success']) {
                return $resultado_signatario;
            }

            // Processar a assinatura
            return $this->processarAssinatura(
                $resultado_signatario['token'],
                $dados_assinante,
                $file_identificacao
            );
        } catch (Exception $e) {
            error_log("Erro ao processar assinatura externa: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno do sistema'];
        }
    }
}
