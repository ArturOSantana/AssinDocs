// classes/Notificacao.php
<?php
class Notificacao {
    
    public static function enviar($usuario_id, $titulo, $mensagem, $tipo = 'info', $link = null) {
        $sql = "INSERT INTO notificacoes 
                (usuario_id, titulo, mensagem, tipo, link, lida) 
                VALUES (:user_id, :titulo, :msg, :tipo, :link, 0)";
        
        $stmt = Conexao::getConexao()->prepare($sql);
        return $stmt->execute([
            ':user_id' => $usuario_id,
            ':titulo' => $titulo,
            ':msg' => $mensagem,
            ':tipo' => $tipo,
            ':link' => $link
        ]);
    }
    
    public static function notificarSignatarios($documento_id, $acao) {
        $signatarios = self::buscarSignatariosDocumento($documento_id);
        $documento = self::buscarDocumento($documento_id);
        
        foreach ($signatarios as $signatario) {
            $mensagem = "Documento '{$documento['titulo']}' foi {$acao}";
            self::enviarEmail($signatario['email'], $mensagem);
        }
    }
}
?>