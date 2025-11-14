<?php
require "autoload.php";
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
include 'includes/header_logado.php';
require_once 'classes/Usuario.php';


$usuario = new Usuario();
$dados = $usuario->buscarPorId($_SESSION['usuario_id']);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $senha = trim($_POST['senha']);

    if ($usuario->atualizar($_SESSION['usuario_id'], $nome, $email)) {
        $_SESSION['usuario_nome'] = $nome;
        $msg = 'Perfil atualizado!';
        if (!empty($senha)) {
            $usuario->alterarSenha($_SESSION['usuario_id'], $senha);
            $msg .= ' Senha alterada.';
        }
    } else {
        $msg = 'Erro ao atualizar.';
    }
}
?>

<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <h3 class="mb-4">Meu Perfil</h3>
        <?php if ($msg): ?><div class="alert alert-info"><?php echo $msg; ?></div><?php endif; ?>
        <form action="#" method="post">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" name="nome" value="<?php echo htmlspecialchars($dados['nome']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($dados['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova Senha (opcional)</label>
                <input type="password" class="form-control" name="senha" placeholder="Digite nova senha">
            </div>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>