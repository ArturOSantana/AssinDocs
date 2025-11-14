<?php
// Verificação de arquivo
require "autoload.php";
if (!file_exists('includes/header.php')) {
    die("Erro: Arquivo header.php não encontrado.");
}
include "includes/header.php";
;

if (!file_exists('classes/Usuario.php')) {
    die("Erro: Arquivo Usuario.php não encontrado.");
}
require_once "classes/Usuario.php";

// Depuração: Confirme se o PHP está executando (remova após testar)
echo "<!-- PHP executando: Cadastro Page -->";

// Iniciar sessão e gerar token CSRF
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$usuario = new Usuario();
$msg = "";
$tipo_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $msg = "Erro de segurança. Tente novamente.";
        $tipo_msg = "erro";
    } else {
        $nome = htmlspecialchars(trim($_POST["nome"]));
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $senha = trim($_POST["senha"]);
        $confirmar = trim($_POST["confirmar_senha"]);

        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar)) {
            $msg = "Preencha todos os campos!";
            $tipo_msg = "erro";
        } elseif ($senha !== $confirmar) {
            $msg = "As senhas não coincidem.";
            $tipo_msg = "erro";
        } elseif (strlen($senha) < 6) {
            $msg = "A senha deve ter pelo menos 6 caracteres.";
            $tipo_msg = "erro";
        } else {
            $resultado = $usuario->cadastrar($nome, $email, $senha);
            if ($resultado === true) {
                $msg = "Cadastro realizado com sucesso! Faça login.";
                $tipo_msg = "sucesso";
            } else {
                $msg = $resultado;
                $tipo_msg = "erro";
            }
        }
    }
}
?>

<main class="cadastro-container">
  <div class="cadastro-card">
    <h2>Crie sua conta no AssinDocs</h2>

    <?php if (!empty($msg)): ?>
      <p class="<?php echo $tipo_msg === 'erro' ? 'erro-login' : 'sucesso-msg'; ?>"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <div class="form-group">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
      </div>

      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
      </div>

      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
      </div>

      <div class="form-group">
        <label for="confirmar_senha">Confirmar senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita sua senha" required>
      </div>

      <button type="submit" class="btn-primary">Cadastrar</button>
    </form>

    <p class="login-link">Já tem conta?
      <a href="login.php">Entre aqui</a>
    </p>
  </div>
</main>

<?php
if (!file_exists('includes/footer.php')) {
    die("Erro: Arquivo footer.php não encontrado.");
}
include 'includes/footer.php';
?>