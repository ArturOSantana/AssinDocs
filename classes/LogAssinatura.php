<?php
require_once __DIR__ . '/../config/conexao.php';

class LogAssinatura
{
    private $conn;

    public function __construct()
    {
        // Correto para sua classe Conexao
        $this->conn = Conexao::getConexao();
    }

    // -----------------------------------------------------
    // Registrar log de assinatura
    // -----------------------------------------------------
    public function registrar($documento_id, $acao, $descricao = "")
    {
        $sql = "INSERT INTO logs_assinatura (documento_id, acao, descricao)
                VALUES (:doc, :acao, :desc)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":doc", $documento_id);
        $stmt->bindParam(":acao", $acao);
        $stmt->bindParam(":desc", $descricao);

        return $stmt->execute();
    }

    // -----------------------------------------------------
    // Listar logs por documento
    // -----------------------------------------------------
    public function listar($documento_id)
    {
        $sql = "SELECT * FROM logs_assinatura
                WHERE documento_id = :doc
                ORDER BY criado_em DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":doc", $documento_id);
        $stmt->execute();

        return $stmt->fetchAll(); // FETCH_ASSOC já é padrão
    }
}
