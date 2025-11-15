<?php
require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/Arquivo.php';

class Documento
{
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = Conexao::getConexao();
        } catch (Exception $e) {
            die("Erro ao conectar ao banco: " . $e->getMessage());
        }
    }

    public function criarDocumento($usuario_id, $projeto_id, $titulo, $arquivo_path, $hash = null)
    {
        try {
            // Se o hash não foi passado, gerar um hash vazio
            if ($hash === null) {
                $hash = '';
            }

            $sql = "INSERT INTO documentos (usuario_id, projeto_id, titulo, arquivo_path, hash_documento, status, versao) 
                    VALUES (:usuario_id, :projeto_id, :titulo, :arquivo_path, :hash, 'pendente', 1)";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ":usuario_id" => (int)$usuario_id,
                ":projeto_id" => (int)$projeto_id,
                ":titulo" => htmlspecialchars($titulo),
                ":arquivo_path" => $arquivo_path,
                ":hash" => $hash
            ]);

            if ($result) {
                $documento_id = $this->conn->lastInsertId();
                error_log("DEBUG: Documento criado com ID: $documento_id");
                return $documento_id;
            } else {
                error_log("Erro ao executar INSERT: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
            $auditoria = new Auditoria();
            $auditoria->registrar(
                'UPLOAD_DOCUMENTO',
                'upload',
                $usuario_id,
                $documento_id,
                "Documento '{$titulo}' enviado",
                ['hash' => $hash, 'tamanho' => filesize($arquivo_path)]
            );
        } catch (Exception $e) {
            error_log("Erro ao criar documento: " . $e->getMessage());
            return false;
        }
    }
    // Adicionar ao Documento.php
    public function buscarParaCertificado($id)
    {
        try {
            $sql = "SELECT * FROM documentos WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":id" => (int)$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar documento para certificado: " . $e->getMessage());
            return false;
        }
    }
    public function listarPorUsuario($usuario_id)
    {
        try {
            $sql = "SELECT * FROM documentos 
                    WHERE usuario_id = :usuario_id 
                    ORDER BY criado_em DESC";  // CORRIGIDO: data_envio → criado_em

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":usuario_id" => (int)$usuario_id]);
            $documentos = $stmt->fetchAll();

            error_log("DEBUG Documento::listarPorUsuario: " . count($documentos) . " documentos encontrados para usuário $usuario_id");

            return $documentos;
        } catch (Exception $e) {
            error_log("Erro ao listar documentos: " . $e->getMessage());
            return [];
        }
    }

    public function buscar($id, $usuario_id)
    {
        try {
            $sql = "SELECT * FROM documentos WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":id" => (int)$id, ":usuario_id" => (int)$usuario_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erro ao buscar documento: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarStatus($id, $status)
    {
        try {
            $sql = "UPDATE documentos SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":status" => in_array($status, ['pendente', 'assinado', 'rejeitado']) ? $status : 'pendente',
                ":id" => (int)$id
            ]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return false;
        }
    }

    public function adicionarSignatario($documento_id, $nome, $email)
    {
        try {
            $sql = "INSERT INTO signatarios (documento_id, nome, email) VALUES (:documento_id, :nome, :email)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":documento_id" => (int)$documento_id,
                ":nome" => htmlspecialchars($nome),
                ":email" => filter_var($email, FILTER_SANITIZE_EMAIL)
            ]);
        } catch (Exception $e) {
            error_log("Erro ao adicionar signatário: " . $e->getMessage());
            return false;
        }
    }
}
