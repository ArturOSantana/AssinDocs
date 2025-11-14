<?php
// check_permissions.php
echo "<h3>Verificação de Permissões - AssinDocs</h3>";

$pasta_uploads = $_SERVER['DOCUMENT_ROOT'] . '/Assindocs/uploads/';

echo "<p><strong>Pasta uploads:</strong> " . $pasta_uploads . "</p>";

// Verificar se a pasta existe
if (is_dir($pasta_uploads)) {
    echo "<p style='color: green'>✓ Pasta uploads existe</p>";
    
    // Verificar permissões
    if (is_writable($pasta_uploads)) {
        echo "<p style='color: green'>✓ Pasta uploads tem permissão de escrita</p>";
    } else {
        echo "<p style='color: red'>✗ Pasta uploads NÃO tem permissão de escrita</p>";
        
        // Tentar corrigir
        if (chmod($pasta_uploads, 0755)) {
            echo "<p style='color: green'>✓ Permissões corrigidas para 755</p>";
        } else {
            echo "<p style='color: red'>✗ Não foi possível corrigir as permissões</p>";
        }
    }
} else {
    echo "<p style='color: red'>✗ Pasta uploads não existe</p>";
    
    // Tentar criar
    if (mkdir($pasta_uploads, 0755, true)) {
        echo "<p style='color: green'>✓ Pasta uploads criada com sucesso</p>";
    } else {
        echo "<p style='color: red'>✗ Não foi possível criar a pasta uploads</p>";
    }
}

// Verificar usuário do servidor
echo "<p><strong>Usuário do servidor:</strong> " . get_current_user() . "</p>";
echo "<p><strong>Grupo do servidor:</strong> " . getmygid() . "</p>";

// Testar escrita
$arquivo_teste = $pasta_uploads . 'teste.txt';
if (file_put_contents($arquivo_teste, 'teste')) {
    echo "<p style='color: green'>✓ Escrita na pasta funcionando</p>";
    unlink($arquivo_teste); // Limpar
} else {
    echo "<p style='color: red'>✗ Não é possível escrever na pasta</p>";
}
?>