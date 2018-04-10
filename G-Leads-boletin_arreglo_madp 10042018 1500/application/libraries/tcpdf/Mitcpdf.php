<?php
//require "tcpdf.php";
require APPPATH . "libraries/tcpdf/tcpdf.php"; // CAMBIADO POR R.
class Mitcpdf extends TCPDF{
    protected $last_page_flag = false;
    public function Header() {
        $headerData = $this->getHeaderData();
        $this->SetFont('helvetica', '', 10);
        $this->writeHTML($headerData['string']);
    }
    public function Footer() {
        // Position at 15 mm from bottom
        
        // Page number
        //$this->Cell(0, 10, 'Págs: '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        
        if ($this->last_page_flag) {
            $html = '
                <table width="100%" style="color: #7c7c7c;">
                    <tr align="left">
                        <td style="font-size:7pt;">Cantidad personas: Es el total de personas únicas web gestionadas por el ejecutivo.
                        <br />Cantidad de gestiones: Es el número total de gestiones realizadas por los ejecutivos a sus cotizantes web durante el mes del informe.
                        <br />Días de primer contacto: Es cantidad promedio de días que se tardó un ejecutivo en darle un primer contacto a un cotizante que cotizó durante el mes del informe.
                        <br />Ratio de seguimiento: Representa a la cantidad promedio de gestiones/seguimiento que un ejecutivo hizo a un cotizante que haya realizado una cotizacióndurante el mes del informe</td>
                    </tr>
                    <tr align="center">
                        <td>
                            <a href="http://www.tga.cl"><img src="https://gleads.tgapps.net//images/sitio/reporte_pro/boletin/pdf/tga_logon.png" width="45px"/></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:8pt;" align="center"></td>
                    </tr>
                </table>
            ';
            $this->SetY(-40);
            $this->setFooterMargin(40);
            $this->SetAutoPageBreak(TRUE, 40); 
            $this->writeHTML($html);
        } else {
            $html= "";
            $this->writeHTML($html);
            $this->SetY(-10);
        }
    }

    public function Close() {
        $this->last_page_flag = true;
        parent::Close();
    }
}
?>
