// classes/TwoFactorAuth.php
<?php
class TwoFactorAuth {
    
    public static function gerarQRCode($usuario_id, $email) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();
        
        // Salvar secret no banco
        self::salvarSecret($usuario_id, $secret);
        
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($email, $secret, 'AssinDocs');
        return ['secret' => $secret, 'qr_url' => $qrCodeUrl];
    }
    
    public static function verificarCodigo($usuario_id, $codigo) {
        $secret = self::buscarSecret($usuario_id);
        $ga = new PHPGangsta_GoogleAuthenticator();
        
        return $ga->verifyCode($secret, $codigo, 2); // 2 = 2 minutos de tolerância
    }
}
?>