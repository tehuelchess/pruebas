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

    public function generar($tramite_id, $firmar = false) {
        $CI = & get_instance();

        $regla = new Regla($this->contenido);
        $contenido = $regla->getExpresionParaOutput($tramite_id);

        $CI->load->library('tcpdf');

        $obj = new $CI->tcpdf;

        $obj->AddPage();
        $obj->writeHTML($contenido);

        $filename = sha1(uniqid()) . '.pdf';
        $uploadDirectory = 'uploads/documentos/';

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

        return $filename;
    }

    public function previsualizar() {
        $CI = & get_instance();

        $CI->load->library('tcpdf');

        $CI->tcpdf->AddPage();
        $CI->tcpdf->writeHTML($this->contenido);

        $CI->tcpdf->Output('preview.pdf');
    }

}
