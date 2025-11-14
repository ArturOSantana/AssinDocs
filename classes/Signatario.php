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
}