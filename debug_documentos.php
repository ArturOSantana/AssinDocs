<?php
// debug_documentos.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    die("Não logado");
}

require_once 'classes/Documento.php';
require_once 'classes/Conexao.php';

echo "<h3>Debug - Documentos no Banco</h3>";

try {
    $doc = new Documento();
    $usuario_id = $_SESSION['usuario_id'];
    
    echo "<p><strong>Usuário ID:</strong> $usuario_id</p>";
    
    // 1. Buscar documentos via classe
    $documentos_classe = $doc->listarPorUsuario($usuario_id);
    echo "<h4>Documentos via Classe Documento:</h4>";
    echo "<p>Total: " . count($documentos_classe) . "</p>";
    
    if (empty($documentos_classe)) {
        echo "<p style='color: red'>Nenhum documento encontrado via classe</p>";
    } else {
        echo "<table border='1' style='width:100%'>";
        echo "<tr><th>ID</th><th>Título</th><th>Status</th><th>Arquivo</th><th>Data</th></tr>";
        foreach ($documentos_classe as $doc_item) {
            echo "<tr>";
            echo "<td>{$doc_item['id']}</td>";
            echo "<td>{$doc_item['titulo']}</td>";
            echo "<td>{$doc_item['status']}</td>";
            echo "<td>{$doc_item['arquivo_path']}</td>";
            echo "<td>{$doc_item['data_envio']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 2. Buscar diretamente via SQL
    echo "<h4>Documentos via SQL Direto:</h4>";
    $conn = Conexao::getConexao();
    $sql = "SELECT * FROM documentos WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario_id]);
    $documentos_sql = $stmt->fetchAll();
    
    echo "<p>Total via SQL: " . count($documentos_sql) . "</p>";
    
    if (empty($documentos_sql)) {
        echo "<p style='color: red'>Nenhum documento encontrado via SQL</p>";
    } else {
        echo "<table border='1' style='width:100%'>";
        echo "<tr><th>ID</th><th>Título</th><th>Status</th><th>Arquivo</th><th>Data</th></tr>";
        foreach ($documentos_sql as $doc_item) {
            echo "<tr>";
            echo "<td>{$doc_item['id']}</td>";
            echo "<td>{$doc_item['titulo']}</td>";
            echo "<td>{$doc_item['status']}</td>";
            echo "<td>{$doc_item['arquivo_path']}</td>";
            echo "<td>{$doc_item['data_envio']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Verificar estrutura da tabela
    echo "<h4>Estrutura da Tabela documentos:</h4>";
    $sql_desc = "DESCRIBE documentos";
    $stmt_desc = $conn->prepare($sql_desc);
    $stmt_desc->execute();
    $estrutura = $stmt_desc->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($estrutura as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<p style='color: red'>Erro: " . $e->getMessage() . "</p>";
}
?>