<?php
include "includes/header.php";
require_once "classes/Auth.php";
require "autoload.php";

Auth::iniciarSessao();
$msg = "";
$tipo_msg = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $msg = "Erro de segurança.";
        $tipo_msg = "erro";
    } else {
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $senha = trim($_POST["senha"]);

        if (empty($email) || empty($senha)) {
            $msg = "Preencha todos os campos!";
            $tipo_msg = "erro";
        } else {
            if (Auth::login($email, $senha)) {
                Auth::enviarCodigo2FA($email); // 2FA simulado
                header("Location: dashboard.php"); // Em produção, redirecione para verificação 2FA
                exit;
            } else {
                $msg = "E-mail ou senha incorretos.";
                $tipo_msg = "erro";
            }
        }
    }
}
?>

<main class="login-container">
  <div class="login-card">
    <h2>Entrar no AssinDocs</h2>
    <?php if (!empty($msg)): ?>
      <p class="erro-login"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>
      </div>
      <button type="submit" class="btn-primary">Entrar</button>
    </form>
    <p class="login-link">Ainda não tem conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
  </div>
</main>
<?php include 'includes/footer.php'; ?>