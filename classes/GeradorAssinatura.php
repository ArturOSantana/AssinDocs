<?php
require_once 'fpdf/fpdf.php';

class GeradorAssinatura {
    
    /**
     * Gerar página de assinatura para anexar ao documento
     */
    public function gerarPaginaAssinatura($documento, $assinaturas) {
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Cabeçalho
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'TERMO DE ASSINATURA DIGITAL', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Informações do Documento
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'DOCUMENTO: ' . utf8_decode($documento['titulo']), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, 'ID do Documento: ' . $documento['id'], 0, 1);
        $pdf->Cell(0, 8, 'Hash SHA-256: ' . $documento['hash_documento'], 0, 1);
        $pdf->Cell(0, 8, 'Data de Criacao: ' . date('d/m/Y H:i', strtotime($documento['criado_em'])), 0, 1);
        $pdf->Ln(10);
        
        // Linha do tempo das assinaturas
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'LINHA DO TEMPO DAS ASSINATURAS', 0, 1, 'C');
        $pdf->Ln(5);
        
        $y_pos = $pdf->GetY();
        
        foreach ($assinaturas as $index => $assinatura) {
            $this->adicionarAssinaturaVisual($pdf, $assinatura, $index + 1, $y_pos);
            $y_pos += 45;
            
            // Adicionar nova página se necessário
            if ($y_pos > 250) {
                $pdf->AddPage();
                $y_pos = 30;
            }
        }
        
        // Rodapé com validade jurídica
        $pdf->SetY(-40);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 8, 'Documento gerado automaticamente por AssinDocs - Plataforma de Assinatura Digital', 0, 1, 'C');
        $pdf->Cell(0, 8, 'Data de geracao: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 8, 'Conforme MP 2.200-2/2001 - ICP-Brasil', 0, 1, 'C');
        
        return $pdf;
    }
    
    /**
     * Adicionar assinatura visual individual
     */
    private function adicionarAssinaturaVisual($pdf, $assinatura, $numero, $y_pos) {
        $pdf->SetY($y_pos);
        
        // Container da assinatura
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Rect(10, $y_pos, 190, 40, 'DF');
        
        // Número da assinatura
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(15, $y_pos + 5);
        $pdf->Cell(10, 6, '#' . $numero, 0, 0);
        
        // Nome do signatário
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(30, $y_pos + 5);
        $pdf->Cell(0, 6, utf8_decode($assinatura['usuario_nome']), 0, 1);
        
        // Email
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY(30, $y_pos + 12);
        $pdf->Cell(0, 6, $assinatura['email'], 0, 1);
        
        // Data e hora
        $pdf->SetXY(30, $y_pos + 18);
        $pdf->Cell(0, 6, 'Assinado em: ' . date('d/m/Y H:i', strtotime($assinatura['timestamp'])), 0, 1);
        
        // Hash reduzido da assinatura
        $pdf->SetFont('Courier', '', 8);
        $pdf->SetXY(30, $y_pos + 24);
        $pdf->Cell(0, 6, 'Hash: ' . substr($assinatura['assinatura'], 0, 20) . '...', 0, 1);
        
        // Carimbo de validade
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(140, $y_pos + 30);
        
        if ($assinatura['valida']) {
            $pdf->SetTextColor(0, 128, 0);
            $pdf->Cell(0, 6, '✓ ASSINATURA VÁLIDA', 0, 1);
        } else {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 6, '✗ ASSINATURA INVÁLIDA', 0, 1);
        }
        
        $pdf->SetTextColor(0, 0, 0);
    }
    
    /**
     * Gerar certificado individual de assinatura
     */
    public function gerarCertificadoAssinatura($documento, $assinatura) {
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Moldura decorativa
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->SetLineWidth(1);
        $pdf->Rect(5, 5, 200, 287);
        
        // Cabeçalho ornamental
        $pdf->SetFillColor(230, 230, 250);
        $pdf->Rect(5, 5, 200, 30, 'F');
        
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(50, 50, 150);
        $pdf->SetY(15);
        $pdf->Cell(0, 10, 'CERTIFICADO DE ASSINATURA DIGITAL', 0, 1, 'C');
        
        // Informações principais
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetY(50);
        $pdf->Cell(0, 10, 'Documento: ' . utf8_decode($documento['titulo']), 0, 1, 'C');
        
        // Dados do signatário
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(80);
        $pdf->Cell(0, 8, 'Signatário: ' . utf8_decode($assinatura['usuario_nome']), 0, 1, 'C');
        $pdf->Cell(0, 8, 'Email: ' . $assinatura['email'], 0, 1, 'C');
        $pdf->Cell(0, 8, 'Data e Hora: ' . date('d/m/Y H:i:s', strtotime($assinatura['timestamp'])), 0, 1, 'C');
        
        // Hash da assinatura
        $pdf->SetY(120);
        $pdf->SetFont('Courier', '', 10);
        $pdf->MultiCell(0, 6, 'Hash da Assinatura: ' . $assinatura['assinatura'], 0, 'C');
        
        // QR Code para verificação (espaço para implementação futura)
        $pdf->SetY(160);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 8, '[Área para QR Code de Verificação]', 0, 1, 'C');
        
        // Rodapé legal
        $pdf->SetY(230);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, 
            'Este certificado atesta que a assinatura digital acima foi validada e registrada ' .
            'no sistema AssinDocs. A integridade do documento e a autenticidade da assinatura ' .
            'são garantidas por criptografia de alto nível.', 0, 'C');
        
        $pdf->SetY(250);
        $pdf->Cell(0, 5, 'AssinDocs - Plataforma Legalmente Reconhecida', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Conforme MP 2.200-2/2001 - ICP-Brasil', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Certificado gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        
        return $pdf;
    }
}
?>