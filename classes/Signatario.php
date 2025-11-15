<?php
require_once __DIR__ . '/Conexao.php';

class Signatario
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

    public function adicionar($documento_id, $nome, $email)
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
            return false;
        }
    }

    public function listarPorDocumento($documento_id)
    {
        try {
            $sql = "SELECT * FROM signatarios WHERE documento_id = :documento_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":documento_id" => (int)$documento_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function adicionarComToken($documento_id, $nome, $email)
    {
        try {
            $token = bin2hex(random_bytes(16));

            $sql = "INSERT INTO signatarios (documento_id, nome, email, token_acesso) 
                VALUES (:documento_id, :nome, :email, :token)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ":documento_id" => (int)$documento_id,
                ":nome" => htmlspecialchars($nome),
                ":email" => filter_var($email, FILTER_SANITIZE_EMAIL),
                ":token" => $token
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'token' => $token,
                    'link_assinatura' => $this->gerarLinkAssinaturaExterna($token)
                ];
            }
            return false;
        } catch (Exception $e) {
            error_log("Erro ao adicionar signatário: " . $e->getMessage());
            return false;
        }
    }

    private function gerarLinkAssinaturaExterna($token)
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $base_url . "/Assindocs/assinar_externo.php?token=" . $token;
    }
}
