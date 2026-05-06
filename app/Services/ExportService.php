<?php

namespace App\Services;

class ExportService
{
    /**
     * Export data to Excel XML format
     *
     * @param string $filename
     * @param array $sheets
     * @return void
     */
    public function exportToExcelXML($filename, $sheets)
    {
        // Final robustness measures
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');

        echo '<?xml version="1.0"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Styles
        echo ' <Styles>' . "\n";
        echo '  <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Bottom"/><Borders/><Font/><Interior/><NumberFormat/><Protection/></Style>' . "\n";
        echo '  <Style ss:ID="sHeader"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Interior ss:Color="#D7E4BC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>' . "\n";
        echo '  <Style ss:ID="sSubHeader"><Font ss:Bold="1"/><Interior ss:Color="#EBF1DE" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>' . "\n";
        echo '  <Style ss:ID="sInfoLabel"><Font ss:Bold="1"/></Style>' . "\n";
        echo ' </Styles>' . "\n";

        foreach ($sheets as $sheetName => $content) {
            $title = $content['title'] ?? null;
            $header = $content['header'] ?? [];
            $rows = $content['rows'] ?? [];

            echo ' <Worksheet ss:Name="' . htmlspecialchars(substr($sheetName, 0, 31)) . '">' . "\n";
            echo '  <Table>' . "\n";

            // Add Title if exists
            if ($title) {
                echo '   <Row ss:Height="20">' . "\n";
                echo '    <Cell ss:StyleID="sHeader"><Data ss:Type="String">' . htmlspecialchars($title) . '</Data></Cell>' . "\n";
                echo '   </Row>' . "\n";
                echo '   <Row></Row>' . "\n";
            }

            // Add Header if exists
            if (!empty($header)) {
                echo '   <Row ss:StyleID="sHeader">' . "\n";
                foreach ($header as $h) {
                    echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>' . "\n";
                }
                echo '   </Row>' . "\n";
            }

            // Add Rows
            foreach ($rows as $r) {
                if (empty($r)) {
                    echo '   <Row></Row>' . "\n";
                    continue;
                }

                $rowStyle = $r['_style'] ?? 'Default';
                if (isset($r['_style'])) unset($r['_style']);
                
                echo '   <Row>' . "\n";
                foreach ($r as $val) {
                    $strVal = (string)$val;
                    $forceString = (strlen($strVal) > 8 || preg_match('/^0/', $strVal));
                    $type = (is_numeric($val) && !$forceString) ? 'Number' : 'String';
                    
                    echo '    <Cell ss:StyleID="' . $rowStyle . '"><Data ss:Type="' . $type . '">' . htmlspecialchars($strVal) . '</Data></Cell>' . "\n";
                }
                echo '   </Row>' . "\n";
            }
            
            echo '  </Table>' . "\n";
            echo ' </Worksheet>' . "\n";
        }
        echo '</Workbook>' . "\n";
        exit;
    }
}
