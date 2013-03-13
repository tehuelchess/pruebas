<?php

class Documento extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('tipo');
        $this->hasColumn('nombre');
        $this->hasColumn('contenido');
        $this->hasColumn('servicio');
        $this->hasColumn('servicio_url');
        $this->hasColumn('validez');
        $this->hasColumn('firmador_nombre');
        $this->hasColumn('firmador_cargo');
        $this->hasColumn('firmador_servicio');
        $this->hasColumn('firmador_imagen');
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

    public function setValidez($validez) {
        if (!$validez)
            $validez = null;

        $this->_set('validez', $validez);
    }

    public function generar($file_id, $key, $tramite_id, $firmar = false) {



        $regla = new Regla($this->contenido);
        $contenido = $regla->getExpresionParaOutput($tramite_id);

        $filename_uniqid = uniqid();

        $filename = $filename_uniqid . '.pdf';
        $resultado = $this->render($contenido, $file_id, $key, $filename, false, $firmar);
        $filename = $filename_uniqid . '.copia.pdf';
        $this->render($contenido, $file_id, $key, $filename, true, $firmar);




        return $resultado->filename;
    }

    public function previsualizar() {
        $this->render($this->contenido, '123456789', 'abcdefghijkl');
    }

    private function render($contenido, $identifier, $key, $filename = false, $copia = false, $firmar = false) {
        $resultado = new stdClass();


        $uploadDirectory = 'uploads/documentos/';

        $CI = &get_instance();

        if ($this->tipo == 'certificado') {
            $CI->load->library('certificadopdf');
            $obj = new $CI->certificadopdf;

            $obj->content = $contenido;
            $obj->id = $identifier;
            $obj->key = $key;
            $obj->servicio = $this->servicio;
            $obj->servicio_url = $this->servicio_url;
            $obj->titulo = $this->nombre;
            $obj->validez = $this->validez;
            $obj->firmador_nombre = $this->firmador_nombre;
            $obj->firmado_cargo = $this->firmador_cargo;
            $obj->firmador_servicio = $this->firmador_servicio;
            if ($this->firmador_imagen)
                $obj->firmador_imagen = 'uploads/firmas/' . $this->firmador_imagen;
            $obj->firma_electronica = $firmar;
            $obj->copia = $copia;
        }else {
            $CI->load->library('blancopdf');
            $obj = new $CI->blancopdf;
            $obj->content=$contenido;
        }

        if ($filename) {
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
            $resultado->filename = $filename;
        } else {
            $obj->Output($filename);
        }

        $resultado->filename_uniqid = $filename;


        return $resultado;
    }

}
