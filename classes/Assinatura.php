<?php
require_once __DIR__ . '/Conexao.php';

class LogAssinatura
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

    public function registrar($documento_id, $acao, $descricao = "")
    {
        try {
            $sql = "INSERT INTO logs_assinatura (documento_id, acao, descricao) VALUES (:doc, :acao, :desc)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":doc" => (int)$documento_id,
                ":acao" => htmlspecialchars($acao),
                ":desc" => htmlspecialchars($descricao)
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function listar($documento_id)
    {
        try {
            $sql = "SELECT * FROM logs_assinatura WHERE documento_id = :doc ORDER BY criado_em DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":doc" => (int)$documento_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}