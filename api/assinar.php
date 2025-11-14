// api/assinar.php
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = $_POST['api_key'] ?? '';
    $documento_id = $_POST['documento_id'] ?? '';
    $usuario_id = $_POST['usuario_id'] ?? '';
    
    if (APIAuth::validarChave($api_key)) {
        $resultado = AssinaturaDigital::assinarDocumento($documento_id, $usuario_id);
        
        echo json_encode([
            'success' => true,
            'assinatura' => $resultado,
            'timestamp' => time()
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'API key inválida']);
    }
}
?>