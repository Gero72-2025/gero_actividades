<?php
// Si tu autoloader no maneja FPDF, inclúyelo aquí
require_once 'fpdf/fpdf.php'; 


class ReporteActividadesPdf extends FPDF {

    private $fechaInicio;
    private $fechaFin;

    /**
     * Helper para agrupar las actividades por Alcance.
     * @param array $actividades Lista plana de actividades completadas.
     * @param array $alcances Lista de alcances con información de recurrencia.
     * @return array Estructura agrupada y consolidada.
     */
    private function agruparActividadesPorAlcance(array $actividades, array $alcances): array {
        $grupos = [];
        
        // Crear un mapa de alcances para acceso rápido a es_recurrente
        $mapaAlcances = [];
        foreach ($alcances as $alcance) {
            $mapaAlcances[$alcance->Id_alcance] = [
                'es_recurrente' => isset($alcance->es_recurrente) ? (int)$alcance->es_recurrente : 0
            ];
        }
        
        foreach ($actividades as $act) {
            $idAlcance = $act->Id_alcance;
            
            // Si el alcance no está en el grupo, inicializarlo
            if (!isset($grupos[$idAlcance])) {
                $esRecurrente = isset($mapaAlcances[$idAlcance]) ? $mapaAlcances[$idAlcance]['es_recurrente'] : 0;
                
                $grupos[$idAlcance] = [
                    'Id_alcance'            => $idAlcance,
                    'Alcance_Descripcion'   => $act->Alcance_Descripcion,
                    'Contrato_Expediente'   => $act->Contrato_Expediente,
                    'es_recurrente'         => $esRecurrente,
                    'Actividades_List'      => [], // Almacena solo las descripciones (para no recurrentes)
                    'Cantidad_Total'        => 0,  // Suma de cantidades (para recurrentes)
                ];
            }
            
            // Si es recurrente, sumar las cantidades
            if ($grupos[$idAlcance]['es_recurrente'] == 1) {
                // Sumar cantidad_realizada si existe
                if (isset($act->cantidad_realizada) && is_numeric($act->cantidad_realizada)) {
                    $grupos[$idAlcance]['Cantidad_Total'] += (int)$act->cantidad_realizada;
                }
            } else {
                // Si no es recurrente, añadir la descripción al listado
                if (!empty($act->Descripcion_realizada)) {
                    $grupos[$idAlcance]['Actividades_List'][] = trim($act->Descripcion_realizada);
                }
            }
        }
        
        // Convertir el array de actividades a una cadena según el tipo
        $reporteFinal = [];
        foreach ($grupos as $grupo) {
            if ($grupo['es_recurrente'] == 1) {
                // Para recurrentes: mostrar "Alcance Recurrente: [suma]"
                $grupo['Actividad_Concatenada'] = 'Alcance Recurrente: ' . $grupo['Cantidad_Total'];
            } else {
                // Para no recurrentes: concatenar descripciones con coma
                $grupo['Actividad_Concatenada'] = implode(', ', $grupo['Actividades_List']);
            }
            
            // Si no hay actividades, el campo Actividad_Concatenada quedará vacío o con "Alcance Recurrente: 0"
            unset($grupo['Actividades_List']); // Limpiar el array temporal
            unset($grupo['Cantidad_Total']);   // Limpiar el total
            unset($grupo['es_recurrente']);     // Limpiar el flag
            $reporteFinal[] = (object)$grupo; // Convertir a objeto para consistencia
        }
        
        return $reporteFinal;
    }
    
    // Función auxiliar para convertir número de mes a nombre
    private function obtenerNombreMes(int $mes): string {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $meses[$mes] ?? 'Mes inválido';
    }
    
    // Función auxiliar para convertir número de pago a texto
    private function convertirNumeroPagoATexto(int $numeroPago): string {
        $conversion = [
            1 => 'Primer',
            2 => 'Segundo',
            3 => 'Tercero',
            4 => 'Cuarto',
            5 => 'Quinto',
            6 => 'Sexto',
            7 => 'Séptimo',
            8 => 'Octavo',
            9 => 'Noveno',
            10 => 'Décimo',
            11 => 'Décimo Primero',
            12 => 'Décimo Segundo'
        ];
        return $conversion[$numeroPago] ?? $numeroPago . 'º';
    }

    // Función auxiliar para calcular la altura necesaria de MultiCell
    private function GetStringHeight($w, $h, $txt) {
        // Implementación de FPDF para calcular la altura necesaria para MultiCell (el código que ya tienes)
        $cw = &$this->CurrentFont['cw'];
        if ($cw == null) return $h;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        // ... (resto de la lógica GetStringHeight) ...
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += (isset($cw[$c]) ? $cw[$c] : 500);
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else $i++;
        }
        return $nl * $h;
    }

    // Pie de página
    function Footer() {
        // Posición: a 15 mm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        
        // Número de página (centrado)
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
        
        // Rango de fechas (alineado a la derecha)
        if ($this->fechaInicio && $this->fechaFin) {
            $rangoFechas = 'Periodo: ' . date('d/m/Y', strtotime($this->fechaInicio)) . ' - ' . date('d/m/Y', strtotime($this->fechaFin));
            $this->SetXY($this->lMargin, -15);
            $this->Cell(0, 10, mb_convert_encoding($rangoFechas, 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
        }
    }


    public function generar(array $actividades, string $fechaInicio, string $fechaFin, object $personal, array $contrato, array $alcance, int $numeroPagoSeleccionado = 0, int $mesSeleccionado = 0, int $anioSeleccionado = 0, ?object $jefeDivision = null): void {
        
        // Guardar fechas para el pie de página
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        
        // Activar alias para número total de páginas
        $this->AliasNbPages();
        
        $this->AddPage();
        
        // 1. Procesar y agrupar actividades (pasando también los alcances para determinar recurrencia)
        $actividadesAgrupadas = $this->agruparActividadesPorAlcance($actividades, $alcance);
        
        // 2. Crear un mapa de actividades por Id_alcance para búsqueda rápida
        $mapaActividades = [];
        foreach ($actividadesAgrupadas as $act) {
            $mapaActividades[$act->Id_alcance] = $act->Actividad_Concatenada;
        }
        
        // 3. Extraer información de fechas y datos del contrato
        // Si se proporcionó mes y año específicos, usarlos; si no, extraer de la fecha
        if ($mesSeleccionado > 0 && $anioSeleccionado > 0) {
            $MesFiltro = $this->obtenerNombreMes($mesSeleccionado);
            $anioFiltro = $anioSeleccionado;
        } else {
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaInicio);
            $MesFiltro = $this->obtenerNombreMes((int)$fechaObj->format('m'));
            $anioFiltro = $fechaObj->format('Y');
        }
        
        // Obtener número de pago y convertir a texto
        // Si se proporcionó un número de pago específico, usarlo; si no, usar el total de pagos del contrato
        $NumeroPago = strtoupper($this->convertirNumeroPagoATexto(
            $numeroPagoSeleccionado > 0 ? $numeroPagoSeleccionado : (int)$contrato[0]->Numero_pagos
        ));
        
        // Obtener expediente del contrato
        $expediente = $contrato[0]->Expediente ?? 'NO ESPECIFICADO';
        
        // Obtener tipo de servicio del personal (1 = Servicios Profesionales, 0 = Servicios Técnicos)
        $tipoServicio = ($personal->Tipo_servicio == 1) 
            ? 'SERVICIOS PROFESIONALES' 
            : 'SERVICIOS TÉCNICOS';

        // --- 1. ENCABEZADO LARGO (USANDO MULTICELL) ---
        $textEncabezado = 'INFORME DE '.$tipoServicio.' A LA GERENCIA DE ELECTRIFICACIÓN RURAL Y OBRAS -GERO- CORRESPONDIENTE AL '. $NumeroPago. ' PAGO DE LA ORDEN DE COMPRA Y PAGO MATRIZ No. '. $expediente;

        $this->SetFont('Arial', 'B', 12); // Fuente un poco más pequeña
        $encodedText = mb_convert_encoding($textEncabezado, 'ISO-8859-1', 'UTF-8');

        // Usamos MultiCell: 0 = Ancho completo (hasta el margen derecho), 5 = Altura de línea, 0 = Sin borde, 'C' = Centrado
        $this->MultiCell(0, 5, $encodedText, 0, 'L'); 
        $this->Ln(5); // Espacio después del título

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'Objeto del Servicio: ' , 0, 1, 'L');

        $this->SetFont('Arial', '', 10);
        $objetivoText = mb_convert_encoding($contrato[0]->Descripcion, 'ISO-8859-1', 'UTF-8');
        $this->MultiCell(0, 5, $objetivoText, 0, 'L');
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'Alcance del Servicio: ' , 0, 1, 'L');
        $this->Ln(2);
        $this->SetFont('Arial', '', 10);
        // Márgenes para las viñetas
        $leftMargin = $this->lMargin;
        $rightMargin = $this->rMargin;
        $pageWidth = $this->w;
        $bulletWidth = 5;  // Ancho de la viñeta
        $textWidth = $pageWidth - $leftMargin - $rightMargin - $bulletWidth - 2;  // Ancho disponible para el texto
        
        foreach($alcance as $act) {
            $descripcion = mb_convert_encoding($act->Descripcion, 'ISO-8859-1', 'UTF-8');
            
            // Obtener la posición actual
            $startX = $this->GetX();
            $startY = $this->GetY();
            
            // Dibujar el punto (viñeta) - usando chr(149) que es compatible con ISO-8859-1
            $this->SetFont('Arial', '', 10);
            $this->Cell($bulletWidth, 5, chr(149), 0, 0, 'L');
            
            // Escribir el texto multilínea al lado de la viñeta
            $this->SetX($startX + $bulletWidth + 2);
            $this->MultiCell($textWidth, 5, $descripcion, 0, 'L');
            
            // Ajustar la posición para la siguiente viñeta
            $this->SetX($leftMargin);
        }
        
        $this->Ln(5);
        // Texto inicial sin negrita
        $this->SetFont('Arial', '', 10);
        $textInicio = 'A continuación, se detallan las actividades desarrolladas en el mes de '.$MesFiltro.' del año '.$anioFiltro.' , correspondientes al ';
        $this->Write(5, mb_convert_encoding($textInicio, 'ISO-8859-1', 'UTF-8'));
        
        // Texto en negrita (número de pago, tipo de pago y expediente)
        $this->SetFont('Arial', 'B', 10);
        $textBold = $NumeroPago.' PAGO DE LA ORDEN DE COMPRA Y PAGO MATRIZ '.$expediente.'.';
        $this->Write(5, mb_convert_encoding($textBold, 'ISO-8859-1', 'UTF-8'));
        $this->SetFont('Arial', '', 10);
        $this->Ln(10);
        // --- 3. CABECERA DE LA TABLA ---
        $anchoNo = 10;
        $anchoAlcance = 70;
        $anchoActividad = 110;
        
        $this->SetFont('Arial', 'B', 9);
        
        $this->Cell($anchoNo, 7, 'No.', 1, 0, 'C');
        $this->Cell($anchoAlcance, 7, 'Alcance', 1, 0, 'C');
        $this->Cell($anchoActividad, 7, 'Actividad Desarrollada', 1, 1, 'C'); 

        // --- 4. CONTENIDO DE LA TABLA (Iterando sobre TODOS los alcances del contrato) ---
        $this->SetFont('Arial', '', 9);
        $conteo = 1;

        if (!empty($alcance)) {
            foreach ($alcance as $alcanceItem) {
                // Obtener ID y descripción del alcance
                $idAlcance = $alcanceItem->Id_alcance;
                $descAlcance = mb_convert_encoding($alcanceItem->Descripcion, 'ISO-8859-1', 'UTF-8');
                
                // Verificar si existe actividad para este alcance, si no mostrar "Sin Actividad Realizada"
                $descripcion = isset($mapaActividades[$idAlcance]) && !empty($mapaActividades[$idAlcance]) 
                    ? mb_convert_encoding($mapaActividades[$idAlcance], 'ISO-8859-1', 'UTF-8')
                    : 'Sin Actividad Realizada';
                
                // Determinar la altura necesaria
                $alturaAlcance = $this->GetStringHeight($anchoAlcance, 5, $descAlcance);
                $alturaDescripcion = $this->GetStringHeight($anchoActividad, 5, $descripcion);
                $alturaFila = max($alturaAlcance, $alturaDescripcion, 6); 

                $xInicial = $this->GetX();
                $yInicial = $this->GetY();
                
                // Columna No. (Verticalmente centrado, si la alturaFila es grande)
                $this->Cell($anchoNo, $alturaFila, $conteo++, 1, 0, 'C');
                
                // Columna Alcance (MultiCell)
                $xAlcance = $this->GetX();
                $this->MultiCell($anchoAlcance, 5, $descAlcance, 0, 'L');
                
                // 4. Dibujar el borde de Alcance y Actividad
                // Regresar a la posición inicial Y para dibujar los bordes verticales de Alcance
                $this->SetXY($xInicial + $anchoNo, $yInicial); 
                $this->Cell($anchoAlcance, $alturaFila, '', 'LR', 0); // Dibujar borde IZQ y DER.
                
                // Mover a la posición de la Actividad
                $this->SetXY($xInicial + $anchoNo + $anchoAlcance, $yInicial);

                // Columna Actividad Desarrollada (MultiCell)
                $xActividad = $this->GetX();
                $this->MultiCell($anchoActividad, 5, $descripcion, 0, 'L');
                
                // Regresar a la posición inicial Y para dibujar el borde vertical de Actividad
                $this->SetXY($xActividad, $yInicial);
                $this->Cell($anchoActividad, $alturaFila, '', 'R', 0); // Dibujar borde DER.

                // 6. Dibujar la línea divisoria inferior (HORIZONTAL)
                $this->SetXY($xInicial, $yInicial + $alturaFila);
                $this->Cell($anchoNo + $anchoAlcance + $anchoActividad, 0, '', 'T', 1); // Dibujar la línea superior (T)
                
                // Control de salto de página
                if ($this->GetY() + 10 > $this->PageBreakTrigger) { // Agrega un margen de 10mm
                    $this->AddPage();
                    // Redibujar la cabecera
                    $this->SetFont('Arial', 'B', 9);
                    $this->Cell($anchoNo, 7, 'No.', 1, 0, 'C');
                    $this->Cell($anchoAlcance, 7, 'Alcance', 1, 0, 'C');
                    $this->Cell($anchoActividad, 7, 'Actividad Desarrollada', 1, 1, 'C');
                    $this->SetFont('Arial', '', 9);
                }
            }
        } else {
            $this->Cell(0, 10, 'No se encontraron alcances para este contrato.', 1, 1, 'C');
        }

        // --- 5. UNIDAD RESPONSABLE Y FIRMAS ---
        $this->Ln(10);
        
        // Verificar si hay suficiente espacio para UNIDAD RESPONSABLE + firmas (aproximadamente 55mm necesarios)
        $alturaSeccionFirmasCompleta = 55;
        if ($this->GetY() + $alturaSeccionFirmasCompleta > $this->PageBreakTrigger) {
            $this->AddPage();
        }
        
        // Texto "UNIDAD RESPONSABLE" (solo una vez)
        $this->SetFont('Arial', 'B', 10);
        $unidadResponsable = 'UNIDAD RESPONSABLE: ' . ($personal->division_nombre ?? 'NO ESPECIFICADA');
        $this->Cell(0, 5, mb_convert_encoding($unidadResponsable, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $this->Ln(25); // Espacio aumentado para sellos (aproximadamente 2.5 cm)
        
        // Configuración para las firmas: usar cuadrícula 4x4 y ocupar diagonal 1,1 2,2 3,3 4,4
        $firmaWidth = 60;
        $firmaSpacing = 5;
        $lineaFirmaY = $this->GetY();

        // Calcular columnas
        $availableWidth = $this->w - $this->lMargin - $this->rMargin;
        $colWidth = $availableWidth / 4;

        // Espaciado vertical entre filas (diagonal)
        $rowSpacing = 18; // ajustar según guste

        // Nombres y títulos (diagonal)
        $names = [
            mb_convert_encoding($personal->Nombre_Completo . ' ' . $personal->Apellido_Completo, 'ISO-8859-1', 'UTF-8'),
            $jefeDivision ? mb_convert_encoding($jefeDivision->Nombre_Completo . ' ' . $jefeDivision->Apellido_Completo, 'ISO-8859-1', 'UTF-8') : mb_convert_encoding('Jefe de Division', 'ISO-8859-1', 'UTF-8'),
            mb_convert_encoding('Ing. Melfin Adán de León Castrillo', 'ISO-8859-1', 'UTF-8'),
            mb_convert_encoding('Vo.Bo. Ing. Edward Enrique Fuentes López', 'ISO-8859-1', 'UTF-8')
        ];
        $titles = [
            mb_convert_encoding($personal->Puesto ?? 'Personal', 'ISO-8859-1', 'UTF-8'),
            $jefeDivision ? mb_convert_encoding($jefeDivision->Puesto ?? 'Jefe de División', 'ISO-8859-1', 'UTF-8') : mb_convert_encoding('Jefe de División', 'ISO-8859-1', 'UTF-8'),
            mb_convert_encoding('Subgerente de Electrificación Rural y Obras - GERO -', 'ISO-8859-1', 'UTF-8'),
            mb_convert_encoding('Gerente de Electrificación Rural y Obras - GERO -', 'ISO-8859-1', 'UTF-8')
        ];

        $positions = [];

        for ($i = 0; $i < 4; $i++) {
            // Columna i
            $colX = $this->lMargin + ($i * $colWidth);
            // Centrar la firma dentro de la columna
            $centerX = $colX + ($colWidth / 2);
            $lineX1 = $centerX - ($firmaWidth / 2);
            $lineX2 = $centerX + ($firmaWidth / 2);

            // Fila diagonal: y base desplazada por i*rowSpacing
            $lineY = $lineaFirmaY + ($i * $rowSpacing);

            // Dibujar línea de firma
            $this->SetDrawColor(0, 0, 0);
            $this->SetLineWidth(0.4);
            $this->Line($lineX1, $lineY, $lineX2, $lineY);

            // Nombre y título
            $this->SetFont('Arial', '', 9);
            $nameH = $this->GetStringHeight($firmaWidth, 4, $names[$i]);
            $this->SetXY($lineX1, $lineY + 2);
            $this->MultiCell($firmaWidth, 4, $names[$i], 0, 'C');

            $this->SetFont('Arial', 'B', 9);
            $titleH = $this->GetStringHeight($firmaWidth, 4, $titles[$i]);
            $this->SetXY($lineX1, $lineY + 2 + $nameH);
            $this->MultiCell($firmaWidth, 4, $titles[$i], 0, 'C');

            $positions[] = $lineY + 2 + $nameH + $titleH;
        }

        // Ajustar la posición vertical después de las firmas
        $finalY = max($positions);
        $this->SetY($finalY + 8);

        // 6. Output
        $pdfFileName = 'Reporte_Actividades_' . date('Ymd', strtotime($fechaInicio)) . '_' . date('Ymd', strtotime($fechaFin)) . '.pdf';
        $this->Output('D', $pdfFileName);
        exit;
    }
}