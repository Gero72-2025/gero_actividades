<?php
/**
 * Clase para generar reportes de actividades en formato Excel (CSV compatible).
 * Para usar PhpSpreadsheet en el futuro, instalar con: composer require phpoffice/phpspreadsheet
 */
class ReporteActividadesExcel {

    /**
     * Genera y descarga un archivo Excel con las actividades de la división
     * 
     * @param array $actividades Array de objetos con las actividades
     * @param object $division Objeto con información de la división
     * @param object $jefeDivision Objeto con información del jefe
     * @param string $fechaInicio Fecha de inicio del reporte
     * @param string $fechaFin Fecha de fin del reporte
     */
    public function generar(array $actividades, object $division, ?object $jefeDivision, string $fechaInicio, string $fechaFin): void {
        
        // Configurar headers para descarga de archivo Excel
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Reporte_Actividades_Division_' . date('Ymd') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Iniciar el buffer de salida
        echo "\xEF\xBB\xBF"; // BOM para UTF-8
        
        // === ENCABEZADO ===
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>';
        echo '<body>';
        echo '<table border="1">';
        
        // Título principal
        echo '<tr><td colspan="7" style="font-size:16px; font-weight:bold; text-align:center; background-color:#4472C4; color:white;">REPORTE DE ACTIVIDADES POR DIVISIÓN</td></tr>';
        echo '<tr><td colspan="7"></td></tr>'; // Espacio
        
        // Información de la división
        echo '<tr>';
        echo '<td colspan="2" style="font-weight:bold; background-color:#D9E1F2;">Nombre División:</td>';
        echo '<td colspan="5">' . htmlspecialchars($division->Nombre) . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<td colspan="2" style="font-weight:bold; background-color:#D9E1F2;">Jefe Asignado:</td>';
        $nombreJefe = $jefeDivision ? htmlspecialchars($jefeDivision->Nombre_Completo . ' ' . $jefeDivision->Apellido_Completo) : 'No asignado';
        echo '<td colspan="5">' . $nombreJefe . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<td colspan="2" style="font-weight:bold; background-color:#D9E1F2;">Fecha Reporte:</td>';
        echo '<td colspan="5">' . date('d/m/Y H:i:s') . '</td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<td colspan="2" style="font-weight:bold; background-color:#D9E1F2;">Rango de Fechas:</td>';
        echo '<td colspan="5">' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . '</td>';
        echo '</tr>';
        
        echo '<tr><td colspan="7"></td></tr>'; // Espacio
        
        // === CABECERA DE TABLA ===
        echo '<tr style="font-weight:bold; background-color:#4472C4; color:white;">';
        echo '<td>No. Actividad</td>';
        echo '<td>Responsable</td>';
        echo '<td>Expediente</td>';
        echo '<td>Alcance</td>';
        echo '<td>Actividad</td>';
        echo '<td>Estado</td>';
        echo '<td>Fecha</td>';
        echo '</tr>';
        
        // === CONTENIDO - DETALLE DE ACTIVIDADES ===
        if (!empty($actividades)) {
            $conteo = 1;
            
            foreach ($actividades as $actividad) {
                $responsable = htmlspecialchars($actividad->personal_nombre . ' ' . $actividad->personal_apellido);
                $contrato = htmlspecialchars($actividad->contrato_expediente);
                $alcance = htmlspecialchars($actividad->alcance_descripcion);

                // Imprimir fila de actividad (sin agrupar alcances)
                echo '<tr>';
                echo '<td style="text-align:center;">' . $conteo++ . '</td>';
                echo '<td>' . $responsable . '</td>';
                echo '<td style="text-align:center;">' . $contrato . '</td>';
                echo '<td>' . $alcance . '</td>';
                echo '<td>' . htmlspecialchars($actividad->Descripcion_realizada) . '</td>';
                echo '<td style="text-align:center;">' . htmlspecialchars($this->getEstadoTexto($actividad->Estado_actividad)) . '</td>';
                echo '<td style="text-align:center;">' . date('d/m/Y', strtotime($actividad->Fecha_ingreso)) . '</td>';
                echo '</tr>';
            }
            
            // Fila de resumen
            echo '<tr><td colspan="7"></td></tr>';
            echo '<tr style="font-weight:bold; background-color:#D9E1F2;">';
            echo '<td colspan="4" style="text-align:right;">TOTAL DE ACTIVIDADES:</td>';
            echo '<td colspan="3" style="text-align:center;">' . (count($actividades)) . '</td>';
            echo '</tr>';
        } else {
            echo '<tr><td colspan="7" style="text-align:center; color:red;">No se encontraron actividades en el rango de fechas seleccionado.</td></tr>';
        }
        
        echo '</table>';
        echo '</body>';
        echo '</html>';
        
        exit;
    }
    
    /**
     * Convierte el código de estado a texto legible
     */
    private function getEstadoTexto($estado): string {
        // Normalizar a string en minúsculas para cubrir valores numéricos y de texto
        $mapa = [
            '0' => 'Pendiente',
            'pendiente' => 'Pendiente',
            '1' => 'En Progreso',
            'en progreso' => 'En Progreso',
            '2' => 'Completada',
            'completada' => 'Completada',
            'completado' => 'Completada',
            '3' => 'Cancelada',
            'cancelada' => 'Cancelada',
            'cancelado' => 'Cancelada'
        ];

        // Si es numérico, convertir a string; si es texto, limpiar y a minúsculas
        if (is_numeric($estado)) {
            $clave = (string)(int)$estado;
        } else {
            $clave = strtolower(trim((string)$estado));
        }

        return $mapa[$clave] ?? 'Desconocido';
    }
}
