<?php
$required_files = [
    'classes/Arquivo.php',
    'classes/Auth.php',
    'classes/Conexao.php',
    'classes/Documento.php',
    'classes/LogAssinatura.php',
    'classes/Projeto.php',
    'classes/Signatario.php',
    'classes/Usuario.php',
    'includes/header.php',
    'includes/header_logado.php',
    'includes/footer.php'
];

echo "<h3>Verificação da Estrutura do AssinDocs</h3>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green'>✓ $file</p>";
    } else {
        echo "<p style='color: red'>✗ $file - NÃO ENCONTRADO</p>";
    }
}
?>