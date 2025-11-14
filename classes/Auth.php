<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Auditoria.php';

class Auth
{
    public static function iniciarSessao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            session_regenerate_id(true); // Segurança
        }
    }

    public static function login($email, $senha)
    {
        self::iniciarSessao();
        $usuario = new Usuario();
        $dados = $usuario->buscarPorEmail($email);

        if (!$dados || !password_verify($senha, $dados['senha'])) {
            return false;
        }

        $_SESSION['usuario_id'] = $dados['id'];
        $_SESSION['usuario_nome'] = $dados['nome'];
        $_SESSION['logado'] = true;
        $auditoria = new Auditoria();
        $auditoria->registrar('LOGIN', 'login', $dados['id'], null, 'Login realizado com sucesso');
        return true;
    }

    public static function verificarLogin()
    {
        self::iniciarSessao();
        return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
    }

    public static function usuarioId()
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function logout()
    {
        self::iniciarSessao();
        session_destroy();
    }

    public static function enviarCodigo2FA($email)
    {
        $codigo = rand(100000, 999999);
        $_SESSION['2fa_code'] = $codigo;
        $_SESSION['2fa_email'] = $email;
        $_SESSION['2fa_expires'] = time() + 300; // 5 minutos

        // Simulação de e-mail (use PHPMailer em produção)
        $assunto = "Código de Verificação - AssinDocs";
        $mensagem = "Seu código de verificação é: $codigo. Expira em 5 minutos.";
        mail($email, $assunto, $mensagem);
        return true;
    }

    public static function verificar2FA($codigo)
    {
        if (isset($_SESSION['2fa_code']) && $_SESSION['2fa_code'] == $codigo && time() < $_SESSION['2fa_expires']) {
            unset($_SESSION['2fa_code'], $_SESSION['2fa_email'], $_SESSION['2fa_expires']);
            return true;
        }
        return false;
    }
}
