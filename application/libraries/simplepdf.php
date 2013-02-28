<?php

require_once 'tcpdf/tcpdf.php';

class SimplePDF extends TCPDF {
   /* 
    private $identifier='123456789';
    private $copia=false;
    * 
    */


    function __construct() {
        parent::__construct();


        # Set the page margins: 72pt on each side, 36pt on top/bottom.
        //$this->SetMargins(72, 36, 72, true);

    }

    //Page header
    public function Header() {
        /*
        // write RAW 2D Barcode
// set style for barcode
        $style = array(
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

// QRCODE,H : QR-CODE Best error correction
        //$this->write2DBarcode($this->identifier, 'QRCODE,H', 170, 5, 25, 25, $style, 'N');

        // Title
        //$this->SetFont('helvetica', '', 10 );
        //$this->Cell(0, 0, $this->identifier, 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $params=$this->serializeTCPDFtagParameters(array(site_url('validador/documento_form?codigo='.$this->identifier), 'QRCODE,H', 170, 5, 30, 30, $style, 'N'));
        $this->writeHTML('<tcpdf style="text-align: left;" method="write2DBarcode" params="'.$params.'" />');
        $this->writeHTML('<p style="text-align: right; font-size: 14px;">'.$this->identifier.'</p>');
        
        if($this->copia)
            $this->writeHTML('<img src="'.base_url('assets/img/copia.png').'" />');
         * 
         */
    }

    // Page footer
    public function Footer() {
        
    }
    /*
    public function setIdentifier($identifier){
        $this->identifier=$identifier;
    }
    
    public function setCopia($copia){
        $this->copia=$copia;
    }
     * 
     */

}