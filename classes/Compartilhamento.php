<?php
require_once __DIR__ . '/Conexao.php';

class Compartilhamento
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Gerar link seguro para compartilhamento
     */
    public function gerarLink($documento_id, $usuario_id, $tipo = 'visualizacao', $expira_horas = 48, $max_usos = null, $senha = null)
    {
        try {
            // Verificar se documento existe e pertence ao usuário
            if (!$this->verificarPermissaoDocumento($documento_id, $usuario_id)) {
                throw new Exception("Documento não encontrado ou sem permissão");
            }

            // Gerar token único
            $token = bin2hex(random_bytes(32));
            $expira_em = date('Y-m-d H:i:s', time() + ($expira_horas * 3600));

            $sql = "INSERT INTO links_compartilhamento 
                    (token, documento_id, usuario_id, tipo, expira_em, max_usos, senha) 
                    VALUES (:token, :doc_id, :user_id, :tipo, :expira, :max_usos, :senha)";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':token' => $token,
                ':doc_id' => $documento_id,
                ':user_id' => $usuario_id,
                ':tipo' => $tipo,
                ':expira' => $expira_em,
                ':max_usos' => $max_usos,
                ':senha' => $senha ? password_hash($senha, PASSWORD_DEFAULT) : null
            ]);

            if ($result) {
                $link_id = $this->conn->lastInsertId();

                // Tentar registrar log (se a tabela existir)
                $this->registrarLogCriacao($link_id, $documento_id, $usuario_id);

                return [
                    'success' => true,
                    'link_id' => $link_id,
                    'token' => $token,
                    'url_completa' => $this->gerarUrlCompleta($token),
                    'expira_em' => $expira_em,
                    'message' => 'Link de compartilhamento gerado com sucesso'
                ];
            } else {
                throw new Exception("Erro ao gerar link no banco de dados");
            }
            $auditoria = new Auditoria();
            $auditoria->registrar(
                'GERAR_LINK_COMPARTILHAMENTO',
                'compartilhamento',
                $usuario_id,
                $documento_id,
                "Link de compartilhamento gerado",
                ['tipo' => $tipo, 'expira_em' => $expira_em]
            );
        } catch (Exception $e) {
            error_log("Erro ao gerar link: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validar e acessar link compartilhado
     */
    public function validarEAcessarLink($token, $senha = null, $acao = 'visualizacao')
    {
        try {
            // Buscar link
            $link = $this->buscarLinkPorToken($token);

            if (!$link) {
                throw new Exception("Link não encontrado ou inválido");
            }

            // Verificar se está ativo
            if (!$link['ativo']) {
                throw new Exception("Link desativado pelo criador");
            }

            // Verificar expiração
            if (strtotime($link['expira_em']) < time()) {
                throw new Exception("Link expirado");
            }

            // Verificar limite de usos
            if ($link['max_usos'] !== null && $link['usos'] >= $link['max_usos']) {
                throw new Exception("Limite de usos atingido");
            }

            // Verificar senha se existir
            if ($link['senha'] && !password_verify($senha, $link['senha'])) {
                throw new Exception("Senha incorreta");
            }

            // Verificar se a ação é permitida pelo tipo de link
            if (!$this->acaoPermitida($link['tipo'], $acao)) {
                throw new Exception("Ação não permitida para este tipo de link");
            }

            // Incrementar contador de usos
            $this->incrementarUso($link['id']);

            // Tentar registrar acesso (se a tabela existir)
            $this->registrarLogAcesso($link['id'], $link['documento_id'], $acao);

            return [
                'success' => true,
                'link' => $link,
                'documento' => $this->buscarDocumento($link['documento_id']),
                'permissoes' => $this->obterPermissoes($link['tipo']),
                'message' => 'Acesso autorizado'
            ];
        } catch (Exception $e) {
            error_log("Erro ao validar link: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Listar links de um usuário
     */
    public function listarLinksUsuario($usuario_id)
    {
        try {
            $sql = "SELECT lc.*, d.titulo as documento_titulo, d.status as documento_status
                    FROM links_compartilhamento lc
                    JOIN documentos d ON lc.documento_id = d.id
                    WHERE lc.usuario_id = :user_id
                    ORDER BY lc.criado_em DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id' => $usuario_id]);
            $links = $stmt->fetchAll();

            // Adicionar informações adicionais
            foreach ($links as &$link) {
                $link['url_completa'] = $this->gerarUrlCompleta($link['token']);
                $link['status'] = $this->calcularStatusLink($link);
                $link['expira_em_formatado'] = date('d/m/Y H:i', strtotime($link['expira_em']));
            }

            return $links;
        } catch (Exception $e) {
            error_log("Erro ao listar links: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Desativar/ativar link
     */
    public function toggleLink($link_id, $usuario_id)
    {
        try {
            // Verificar permissão
            $link = $this->buscarLinkPorId($link_id);
            if (!$link || $link['usuario_id'] != $usuario_id) {
                throw new Exception("Link não encontrado ou sem permissão");
            }

            $novo_status = !$link['ativo'];
            $sql = "UPDATE links_compartilhamento SET ativo = :ativo WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':ativo' => $novo_status,
                ':id' => $link_id
            ]);

            return $result ? ['success' => true, 'novo_status' => $novo_status] : ['success' => false];
        } catch (Exception $e) {
            error_log("Erro ao alterar status do link: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Excluir link
     */
    public function excluirLink($link_id, $usuario_id)
    {
        try {
            // Verificar permissão
            $link = $this->buscarLinkPorId($link_id);
            if (!$link || $link['usuario_id'] != $usuario_id) {
                throw new Exception("Link não encontrado ou sem permissão");
            }

            $sql = "DELETE FROM links_compartilhamento WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([':id' => $link_id]);

            return $result ? ['success' => true] : ['success' => false];
        } catch (Exception $e) {
            error_log("Erro ao excluir link: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obter estatísticas de um link
     */
    public function obterEstatisticasLink($link_id, $usuario_id)
    {
        try {
            // Verificar permissão
            $link = $this->buscarLinkPorId($link_id);
            if (!$link || $link['usuario_id'] != $usuario_id) {
                throw new Exception("Link não encontrado ou sem permissão");
            }

            // Verificar se a tabela de logs existe
            $tabela_existe = $this->verificarTabelaExiste('logs_acesso_links');

            if (!$tabela_existe) {
                return [
                    'success' => true,
                    'estatisticas' => [
                        'total_acessos' => $link['usos'],
                        'ips_unicos' => 'N/A',
                        'ultimo_acesso' => 'N/A'
                    ],
                    'acessos_por_tipo' => [],
                    'link' => $link,
                    'message' => 'Tabela de logs não disponível'
                ];
            }

            // Estatísticas de acesso
            $sql_acessos = "SELECT COUNT(*) as total_acessos, 
                                   COUNT(DISTINCT ip_address) as ips_unicos,
                                   MAX(criado_em) as ultimo_acesso
                            FROM logs_acesso_links 
                            WHERE link_id = :link_id";

            $stmt = $this->conn->prepare($sql_acessos);
            $stmt->execute([':link_id' => $link_id]);
            $estatisticas = $stmt->fetch();

            // Acessos por tipo
            $sql_tipos = "SELECT acao, COUNT(*) as total 
                          FROM logs_acesso_links 
                          WHERE link_id = :link_id 
                          GROUP BY acao";

            $stmt = $this->conn->prepare($sql_tipos);
            $stmt->execute([':link_id' => $link_id]);
            $acessos_por_tipo = $stmt->fetchAll();

            return [
                'success' => true,
                'estatisticas' => $estatisticas,
                'acessos_por_tipo' => $acessos_por_tipo,
                'link' => $link
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ========== MÉTODOS PRIVADOS ==========

    private function verificarPermissaoDocumento($documento_id, $usuario_id)
    {
        $sql = "SELECT id FROM documentos WHERE id = :doc_id AND usuario_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':doc_id' => $documento_id, ':user_id' => $usuario_id]);
        return $stmt->fetch() !== false;
    }

    private function buscarLinkPorToken($token)
    {
        $sql = "SELECT * FROM links_compartilhamento WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    private function buscarLinkPorId($link_id)
    {
        $sql = "SELECT * FROM links_compartilhamento WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $link_id]);
        return $stmt->fetch();
    }

    private function buscarDocumento($documento_id)
    {
        $sql = "SELECT * FROM documentos WHERE id = :doc_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':doc_id' => $documento_id]);
        return $stmt->fetch();
    }

    private function gerarUrlCompleta($token)
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $base_url . "/Assindocs/shared.php?token=" . $token;
    }

    private function incrementarUso($link_id)
    {
        $sql = "UPDATE links_compartilhamento SET usos = usos + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $link_id]);
    }

    private function acaoPermitida($tipo_link, $acao)
    {
        $permissoes = [
            'visualizacao' => ['visualizacao'],
            'download' => ['visualizacao', 'download'],
            'assinatura' => ['visualizacao', 'download', 'assinatura']
        ];

        return isset($permissoes[$tipo_link]) && in_array($acao, $permissoes[$tipo_link]);
    }

    private function obterPermissoes($tipo)
    {
        $permissoes = [
            'visualizacao' => ['Visualizar documento'],
            'download' => ['Visualizar documento', 'Baixar documento'],
            'assinatura' => ['Visualizar documento', 'Baixar documento', 'Assinar documento']
        ];

        return $permissoes[$tipo] ?? [];
    }

    private function calcularStatusLink($link)
    {
        if (!$link['ativo']) {
            return 'desativado';
        }
        if (strtotime($link['expira_em']) < time()) {
            return 'expirado';
        }
        if ($link['max_usos'] !== null && $link['usos'] >= $link['max_usos']) {
            return 'limite_atingido';
        }
        return 'ativo';
    }

    private function registrarLogCriacao($link_id, $documento_id, $usuario_id)
    {
        try {
            // Verificar se a tabela existe antes de tentar inserir
            if (!$this->verificarTabelaExiste('logs_acesso_links')) {
                return false;
            }

            $sql = "INSERT INTO logs_acesso_links (link_id, documento_id, ip_address, user_agent, acao) 
                    VALUES (:link_id, :doc_id, :ip, :ua, 'criacao')";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':link_id' => $link_id,
                ':doc_id' => $documento_id,
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
        } catch (Exception $e) {
            // Silenciosamente ignora erros de tabela não existente
            error_log("Aviso: Tabela logs_acesso_links não disponível - " . $e->getMessage());
            return false;
        }
    }

    private function registrarLogAcesso($link_id, $documento_id, $acao)
    {
        try {
            // Verificar se a tabela existe antes de tentar inserir
            if (!$this->verificarTabelaExiste('logs_acesso_links')) {
                return false;
            }

            $sql = "INSERT INTO logs_acesso_links (link_id, documento_id, ip_address, user_agent, acao) 
                    VALUES (:link_id, :doc_id, :ip, :ua, :acao)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':link_id' => $link_id,
                ':doc_id' => $documento_id,
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':ua' => $_SERVER['HTTP_USER_AGENT'],
                ':acao' => $acao
            ]);
        } catch (Exception $e) {
            // Silenciosamente ignora erros de tabela não existente
            error_log("Aviso: Tabela logs_acesso_links não disponível - " . $e->getMessage());
            return false;
        }
    }

    private function verificarTabelaExiste($tabela)
    {
        try {
            $sql = "SHOW TABLES LIKE :tabela";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':tabela' => $tabela]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}
