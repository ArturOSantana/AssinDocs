<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Debug - Estrutura do Sistema</h3>";

// Verificar diretório atual
echo "<p>Diretório atual: " . __DIR__ . "</p>";

// Listar arquivos nas pastas
$pastas = ['classes', 'includes', '.'];
foreach ($pastas as $pasta) {
    echo "<h4>Arquivos em $pasta/:</h4>";
    if (is_dir($pasta)) {
        $arquivos = scandir($pasta);
        foreach ($arquivos as $arquivo) {
            if ($arquivo != '.' && $arquivo != '..') {
                echo "<p>$arquivo</p>";
            }
        }
    } else {
        echo "<p style='color: red'>Pasta $pasta não existe!</p>";
    }
}

// Testar inclusão de classes
echo "<h4>Testando inclusão de classes:</h4>";
$classes = ['Arquivo', 'Documento', 'Projeto'];
foreach ($classes as $classe) {
    $caminho = "classes/$classe.php";
    if (file_exists($caminho)) {
        echo "<p style='color: green'>✓ $caminho - EXISTE</p>";
    } else {
        echo "<p style='color: red'>✗ $caminho - NÃO ENCONTRADO</p>";
    }
}
?>