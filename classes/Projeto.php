<?php
require_once __DIR__ . '/Conexao.php';

class Projeto
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

    public function criarProjeto($usuario_id, $nome, $descricao = "")
    {
        try {
            $sql = "INSERT INTO projetos (usuario_id, nome, descricao) VALUES (:usuario_id, :nome, :descricao)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":usuario_id" => (int)$usuario_id,
                ":nome" => htmlspecialchars($nome),
                ":descricao" => htmlspecialchars($descricao)
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function listarProjetos($usuario_id)
    {
        try {
            $sql = "SELECT * FROM projetos WHERE usuario_id = :usuario_id ORDER BY criado_em DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":usuario_id" => (int)$usuario_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function buscarProjeto($projeto_id, $usuario_id)
    {
        try {
            $sql = "SELECT * FROM projetos WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":id" => (int)$projeto_id, ":usuario_id" => (int)$usuario_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function atualizarProjeto($id, $usuario_id, $nome, $descricao)
    {
        try {
            $sql = "UPDATE projetos SET nome = :nome, descricao = :descricao WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":nome" => htmlspecialchars($nome),
                ":descricao" => htmlspecialchars($descricao),
                ":id" => (int)$id,
                ":usuario_id" => (int)$usuario_id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function deletarProjeto($id, $usuario_id)
    {
        try {
            $sql = "DELETE FROM projetos WHERE id = :id AND usuario_id = :usuario_id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":id" => (int)$id,
                ":usuario_id" => (int)$usuario_id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}