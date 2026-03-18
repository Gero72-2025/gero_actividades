<?php
require_once __DIR__ . '/fpdf/fpdf.php';

class TableroReporteriaPdf extends FPDF {
    private $headers = [];
    private $widths = [22, 24, 32, 16, 15, 12, 20, 18, 34, 24, 20, 20];
    private $lineHeight = 4.5;
    private $tableroNombre = 'Reporte de Tablero';

    public function download($filename, array $headers, array $rows, $tableroNombre = 'Reporte de Tablero'){
        $this->headers = array_values($headers);
        $this->tableroNombre = (string)$tableroNombre;

        $this->AliasNbPages();
        $this->SetTitle($this->encode('Reporte de Tablero'));
        $this->SetAuthor($this->encode('Gero Actividades'));
        $this->SetMargins(10, 14, 10);
        $this->SetAutoPageBreak(true, 14);
        $this->AddPage('L', 'A4');

        $this->renderTableHeader();
        $this->SetFont('Arial', '', 7);

        foreach($rows as $row){
            $values = [];
            foreach($this->headers as $header){
                $values[] = (string)($row[$header] ?? '');
            }
            $this->renderTableRow($values);
        }

        $this->Output('D', $filename);
        exit;
    }

    public function Header(){
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 7, $this->encode('Reporte de Tablero Activo'), 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, $this->encode('Tablero: ' . $this->tableroNombre), 0, 1, 'L');
        $this->Cell(0, 5, $this->encode('Generado: ' . date('d/m/Y H:i:s')), 0, 1, 'L');
        $this->Ln(2);
    }

    public function Footer(){
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, $this->encode('Pagina ' . $this->PageNo() . ' de {nb}'), 0, 0, 'C');
    }

    private function renderTableHeader(){
        $this->SetFillColor(31, 41, 55);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 7);

        foreach($this->headers as $index => $header){
            $width = $this->widths[$index] ?? 20;
            $this->Cell($width, 8, $this->encode($header), 1, 0, 'C', true);
        }

        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }

    private function renderTableRow(array $values){
        $lineCounts = [];
        foreach($values as $index => $value){
            $lineCounts[] = $this->nbLines($this->widths[$index] ?? 20, $this->encode($value));
        }

        $height = max($lineCounts) * $this->lineHeight;
        $this->checkPageBreak($height);

        $x = $this->GetX();
        $y = $this->GetY();

        foreach($values as $index => $value){
            $width = $this->widths[$index] ?? 20;
            $this->Rect($x, $y, $width, $height);
            $this->MultiCell($width, $this->lineHeight, $this->encode($value), 0, 'L');
            $x += $width;
            $this->SetXY($x, $y);
        }

        $this->SetXY($this->lMargin, $y + $height);
    }

    private function checkPageBreak($height){
        if($this->GetY() + $height > $this->PageBreakTrigger){
            $this->AddPage('L', 'A4');
            $this->renderTableHeader();
            $this->SetFont('Arial', '', 7);
        }
    }

    private function nbLines($width, $text){
        $cw = &$this->CurrentFont['cw'];
        if($width === 0){
            $width = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($width - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$text);
        $nb = strlen($s);
        if($nb > 0 && $s[$nb - 1] === "\n"){
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while($i < $nb){
            $c = $s[$i];
            if($c === "\n"){
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c === ' '){
                $sep = $i;
            }
            $l += $cw[$c] ?? 500;
            if($l > $wmax){
                if($sep === -1){
                    if($i === $j){
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    private function encode($value){
        $value = (string)$value;
        $encoded = @iconv('UTF-8', 'windows-1252//TRANSLIT', $value);
        return $encoded !== false ? $encoded : $value;
    }
}