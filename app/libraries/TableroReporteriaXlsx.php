<?php
class TableroReporteriaXlsx {
    public function download($filename, array $headers, array $rows, $sheetName = 'Reporte Tablero'){
        if(!class_exists('ZipArchive')){
            throw new RuntimeException('La extension ZipArchive no esta disponible para generar archivos XLSX.');
        }

        while(ob_get_level() > 0){
            ob_end_clean();
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'tablero_xlsx_');
        if($tempFile === false){
            throw new RuntimeException('No se pudo crear el archivo temporal para la exportacion XLSX.');
        }

        $zip = new ZipArchive();
        if($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true){
            @unlink($tempFile);
            throw new RuntimeException('No se pudo inicializar el archivo XLSX.');
        }

        $sheetXml = $this->buildWorksheetXml($headers, $rows);

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('docProps/app.xml', $this->buildAppXml());
        $zip->addFromString('docProps/core.xml', $this->buildCoreXml());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tempFile);
        @unlink($tempFile);
        exit;
    }

    private function buildWorksheetXml(array $headers, array $rows){
        $sheetData = [];
        $sheetData[] = $this->buildRowXml(1, $headers, true);

        $rowIndex = 2;
        foreach($rows as $row){
            $values = [];
            foreach($headers as $header){
                $values[] = $row[$header] ?? '';
            }
            $sheetData[] = $this->buildRowXml($rowIndex, $values, false);
            $rowIndex++;
        }

        $lastColumn = $this->columnName(max(1, count($headers)));
        $lastRow = max(1, count($rows) + 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="18"/>'
            . '<cols>' . $this->buildColumnsXml(count($headers)) . '</cols>'
            . '<sheetData>' . implode('', $sheetData) . '</sheetData>'
            . '</worksheet>';
    }

    private function buildColumnsXml($count){
        $xml = '';
        for($i = 1; $i <= $count; $i++){
            $width = ($i <= 6) ? 20 : 35;
            if($i >= 9){
                $width = 45;
            }
            if($i >= 11){
                $width = 18;
            }
            $xml .= '<col min="' . $i . '" max="' . $i . '" width="' . $width . '" customWidth="1"/>';
        }
        return $xml;
    }

    private function buildRowXml($rowIndex, array $values, $isHeader){
        $cells = [];
        $styleId = $isHeader ? 1 : 2;

        foreach($values as $index => $value){
            $cellRef = $this->columnName($index + 1) . $rowIndex;
            if($this->isNumericValue($value) && !$isHeader){
                $cells[] = '<c r="' . $cellRef . '" s="' . $styleId . '"><v>' . $value . '</v></c>';
                continue;
            }

            $text = $this->escapeXml((string)$value);
            $cells[] = '<c r="' . $cellRef . '" t="inlineStr" s="' . $styleId . '"><is><t xml:space="preserve">' . $text . '</t></is></c>';
        }

        return '<row r="' . $rowIndex . '">' . implode('', $cells) . '</row>';
    }

    private function buildContentTypesXml(){
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    private function buildRootRelsXml(){
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function buildAppXml(){
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Microsoft Excel</Application>'
            . '<DocSecurity>0</DocSecurity>'
            . '<ScaleCrop>false</ScaleCrop>'
            . '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            . '<TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>Reporte</vt:lpstr></vt:vector></TitlesOfParts>'
            . '</Properties>';
    }

    private function buildCoreXml(){
        $created = gmdate('Y-m-d\TH:i:s\Z');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>Reporte de Tablero</dc:title>'
            . '<dc:creator>Gero Actividades</dc:creator>'
            . '<cp:lastModifiedBy>Gero Actividades</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $created . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function buildWorkbookXml($sheetName){
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->escapeXml($this->normalizeSheetName($sheetName)) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRelsXml(){
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function buildStylesXml(){
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="10"/><name val="Calibri"/><family val="2"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1F2937"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function columnName($index){
        $name = '';
        while($index > 0){
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = (int)floor($index / 26);
        }
        return $name;
    }

    private function normalizeSheetName($name){
        $name = str_replace(['\\', '/', '*', '?', ':', '[', ']'], ' ', (string)$name);
        $name = trim($name);
        if($name === ''){
            $name = 'Reporte';
        }
        return mb_substr($name, 0, 31);
    }

    private function isNumericValue($value){
        return is_int($value) || is_float($value) || (is_string($value) && $value !== '' && is_numeric($value));
    }

    private function escapeXml($value){
        $value = preg_replace('/[^\P{C}\n\r\t]/u', '', (string)$value);
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}