<?php
require_once __DIR__ . '/Conexao.php';

class Usuario {
    private $conn;

    public function __construct()
    {
        try {
            $this->conn = Conexao::getConexao();
        } catch (Exception $e) {
            die("Erro ao conectar ao banco: " . $e->getMessage());
        }
    }

    public function login($email, $senha)
    {
        try {
            $sql = "SELECT id, nome, email, senha FROM usuarios WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":email" => filter_var($email, FILTER_SANITIZE_EMAIL)]);
            $usuario = $stmt->fetch();

            if (!$usuario || !password_verify($senha, $usuario["senha"])) {
                return "E-mail ou senha incorretos.";
            }
            return true;
        } catch (Exception $e) {
            return "Erro interno: " . $e->getMessage();
        }
    }

    public function cadastrar($nome, $email, $senha)
    {
        try {
            if ($this->buscarPorEmail($email)) {
                return "E-mail já cadastrado.";
            }
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ":nome" => htmlspecialchars($nome),
                ":email" => filter_var($email, FILTER_SANITIZE_EMAIL),
                ":senha" => $hash
            ]);
            return true;
        } catch (Exception $e) {
            return "Erro ao cadastrar: " . $e->getMessage();
        }
    }

    public function buscarPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":email" => filter_var($email, FILTER_SANITIZE_EMAIL)]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function buscarPorId($id)
    {
        try {
            $sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([":id" => (int)$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function atualizar($id, $nome, $email)
    {
        try {
            $sql = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":nome" => htmlspecialchars($nome),
                ":email" => filter_var($email, FILTER_SANITIZE_EMAIL),
                ":id" => (int)$id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function alterarSenha($id, $senha)
    {
        try {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ":senha" => $hash,
                ":id" => (int)$id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}