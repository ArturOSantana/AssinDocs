<?php
require_once 'includes/fpdf/fpdf.php';

class GeradorAssinatura
{

    /**
     * Gerar página de assinatura para anexar ao documento
     */
    public function gerarPaginaAssinatura($documento, $assinaturas)
    {
        $pdf = new FPDF();
        $pdf->AddPage();

        // Configurações iniciais
        $pdf->SetAutoPageBreak(true, 20);

        // Cabeçalho
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->Cell(0, 15, 'TERMO DE ASSINATURA DIGITAL', 0, 1, 'C');
        $pdf->Ln(5);

        // Linha decorativa
        $pdf->SetDrawColor(30, 64, 175);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(10);

        // Informações do Documento
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'INFORMACOES DO DOCUMENTO', 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 6, 'Titulo:', 0, 0);
        $pdf->Cell(0, 6, utf8_decode($documento['titulo']), 0, 1);

        $pdf->Cell(40, 6, 'ID:', 0, 0);
        $pdf->Cell(0, 6, $documento['id'], 0, 1);

        $pdf->Cell(40, 6, 'Hash SHA-256:', 0, 0);
        $pdf->SetFont('Courier', '', 8);
        $pdf->Cell(0, 6, $documento['hash_documento'], 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 6, 'Data de Criacao:', 0, 0);
        $pdf->Cell(0, 6, date('d/m/Y H:i', strtotime($documento['criado_em'])), 0, 1);

        $pdf->Ln(10);

        // Assinaturas
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->Cell(0, 10, 'REGISTRO DE ASSINATURAS DIGITAIS', 0, 1, 'C');
        $pdf->Ln(5);

        foreach ($assinaturas as $index => $assinatura) {
            $this->adicionarAssinaturaVisual($pdf, $assinatura, $index + 1);
            $pdf->Ln(8);
        }

        // Rodapé
        $this->adicionarRodape($pdf);

        return $pdf;
    }

    /**
     * Adicionar assinatura visual individual
     */
    private function adicionarAssinaturaVisual($pdf, $assinatura, $numero)
    {
        $y_inicio = $pdf->GetY();

        // Container da assinatura
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetFillColor(250, 250, 250);
        $pdf->Rect(10, $y_inicio, 190, 35, 'DF');

        // Número e informações
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetXY(15, $y_inicio + 5);
        $pdf->Cell(10, 6, '#' . $numero, 0, 0);

        // Nome do signatário
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(30, $y_inicio + 5);
        $pdf->Cell(0, 6, utf8_decode($assinatura['usuario_nome']), 0, 1);

        // Informações
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(30, $y_inicio + 12);
        $pdf->Cell(0, 6, 'Email: ' . $assinatura['email'], 0, 1);

        $pdf->SetXY(30, $y_inicio + 18);
        $pdf->Cell(0, 6, 'Data: ' . date('d/m/Y H:i', strtotime($assinatura['timestamp'])), 0, 1);

        // Hash reduzido
        $pdf->SetFont('Courier', '', 7);
        $pdf->SetXY(30, $y_inicio + 24);
        $pdf->Cell(0, 6, 'Hash: ' . substr($assinatura['assinatura'], 0, 25) . '...', 0, 1);

        // Status
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(160, $y_inicio + 12);

        if ($assinatura['valida']) {
            $pdf->SetTextColor(0, 128, 0);
            $pdf->Cell(0, 6, '✓ ASSINATURA VALIDA', 0, 1);
        } else {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 6, '✗ ASSINATURA INVALIDA', 0, 1);
        }

        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Adicionar rodapé profissional
     */
    private function adicionarRodape($pdf)
    {
        $pdf->SetY(-40);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);

        $pdf->Cell(0, 5, 'Documento gerado automaticamente por AssinDocs - Plataforma de Assinatura Digital', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Data de geracao: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Conforme MP 2.200-2/2001 - ICP-Brasil', 0, 1, 'C');
        $pdf->Cell(0, 5, 'www.assindocs.com.br', 0, 1, 'C');
    }

    /**
     * Gerar certificado individual de assinatura
     */
    public function gerarCertificadoAssinatura($documento, $assinatura)
    {
        $pdf = new FPDF();
        $pdf->AddPage();

        // Moldura decorativa
        $pdf->SetDrawColor(30, 64, 175);
        $pdf->SetLineWidth(1);
        $pdf->Rect(5, 5, 200, 287);

        // Cabeçalho ornamental
        $pdf->SetFillColor(30, 64, 175);
        $pdf->Rect(5, 5, 200, 35, 'F');

        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetY(15);
        $pdf->Cell(0, 10, 'CERTIFICADO DE ASSINATURA DIGITAL', 0, 1, 'C');

        // Logo/Texto central
        $pdf->SetY(50);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'AssinDocs', 0, 1, 'C');

        // Informações principais
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetY(70);
        $pdf->Cell(0, 10, utf8_decode($documento['titulo']), 0, 1, 'C');

        // Linha decorativa
        $pdf->SetDrawColor(30, 64, 175);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(50, 85, 160, 85);
        $pdf->Ln(15);

        // Dados do signatário
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(100);
        $pdf->Cell(0, 8, 'Signatario: ' . utf8_decode($assinatura['usuario_nome']), 0, 1, 'C');
        $pdf->Cell(0, 8, 'Email: ' . $assinatura['email'], 0, 1, 'C');
        $pdf->Cell(0, 8, 'Data e Hora: ' . date('d/m/Y H:i:s', strtotime($assinatura['timestamp'])), 0, 1, 'C');

        // Container do hash
        $pdf->SetY(140);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 8, 'HASH DA ASSINATURA DIGITAL:', 0, 1, 'C');

        $pdf->SetFont('Courier', '', 8);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->MultiCell(0, 6, $assinatura['assinatura'], 1, 'C', true);

        // Área de assinatura
        $pdf->SetY(190);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(60, 190, 150, 190);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetXY(60, 192);
        $pdf->Cell(90, 6, 'Assinatura Digital Registrada', 0, 0, 'C');

        // Rodapé legal
        $pdf->SetY(230);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(
            0,
            5,
            utf8_decode('Este certificado atesta que a assinatura digital acima foi validada e registrada ') .
                utf8_decode('no sistema AssinDocs. A integridade do documento e a autenticidade da assinatura ') .
                utf8_decode('são garantidas por criptografia de alto nível.'),
            0,
            'C'
        );

        $pdf->SetY(250);
        $pdf->Cell(0, 5, 'AssinDocs - Plataforma Legalmente Reconhecida', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Conforme MP 2.200-2/2001 - ICP-Brasil', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Certificado gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

        return $pdf;
    }
}
