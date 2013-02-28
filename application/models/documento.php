<?php

class Documento extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('contenido');
        $this->hasColumn('proceso_id');
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Campo as Campos', array(
            'local' => 'id',
            'foreign' => 'documento_id'
        ));
    }

    public function generar($tramite_id, $firmar = true) {
        
      

        $regla = new Regla($this->contenido);
        $contenido = $regla->getExpresionParaOutput($tramite_id);
        
        $resultado=$this->render($contenido,null,true, false, $firmar);
        $this->render($contenido,$resultado->identifier, true, true, $firmar);
        

        
        
        return $resultado->filename;
    }

    public function previsualizar() {
        $this->render($this->contenido,'preview');
    }

    private function render($contenido,$identifier,$saveToDisk=false,$copia=false, $firmar=false){
        $resultado=new stdClass();
        
        $identifier=$identifier?$identifier:sha1(uniqid(mt_rand()));
        $filename =  $copia?$identifier. '.copia.pdf':$identifier. '.pdf';
        $uploadDirectory = 'uploads/documentos/';
        
        $CI=&get_instance();
        $CI->load->library('simplepdf');

        $obj = new $CI->simplepdf;
        

        $obj->AddPage();
        
        if($copia){
            //$obj->writeHTML('<img src="'.base_url('assets/img/copia.png').'" />');
            $img_file =base_url('assets/img/copia.png');
            $obj->Image($img_file, 5, 50);
        }
        
        $style = array(
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );

        $params=$obj->serializeTCPDFtagParameters(array(site_url('validador/documento/'.$identifier), 'QRCODE,H', 170, 5, 30, 30, $style, 'N'));
        $obj->writeHTML('<tcpdf style="text-align: left;" method="write2DBarcode" params="'.$params.'" />');
        $obj->writeHTML('<p style="text-align: right; font-size: 14px;">'.$identifier.'</p>');
  
        $obj->writeHTML($contenido);
        
        
        
        if($saveToDisk){
            if (!$firmar) {
                $obj->Output($uploadDirectory . $filename, 'F');
            } else {
                $client = new SoapClient('http://200.111.181.86/wsv2/Wsintercambiadoc.asmx?wsdl');

                $result = $client->IntercambiaDoc(array(
                    'Encabezado' => array(
                        'User' => 'svs',
                        'Password' => 'svs',
                        'TipoIntercambio' => 'pdf',
                        'NombreConfiguracion' => 'test',
                        'FormatoDocumento' => 'b64'
                    ),
                    'Parametro' => array(
                        'Documento' => base64_encode($obj->Output($filename, 'S')),
                        'NombreDocumento' => $filename
                    )
                ));

                file_put_contents($uploadDirectory . $filename, base64_decode($result->IntercambiaDocResult->Documento));
            }
            $resultado->filename=$filename;
        }else{
            $obj->Output($filename);
        }
        
        $resultado->identifier=$identifier;
        
        
        return $resultado;
        
        
    }
}
