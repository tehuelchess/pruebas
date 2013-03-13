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

    public function generar($file_id, $tramite_id, $firmar = false) {
        $regla = new Regla($this->contenido);
        $contenido = $regla->getExpresionParaOutput($tramite_id);

        $resultado->key=$this->tipo=='certificado'?strtolower(random_string('alnum', 12)):null;
        $resultado->validez=$this->tipo=='certificado'?$this->validez:null;
        $filename_uniqid = uniqid();     

        $resultado->filename = $filename_uniqid . '.pdf';
        $this->render($contenido, $file_id, $resultado->key, $resultado->filename, false, $firmar);
        $filename_copia = $filename_uniqid . '.copia.pdf';
        $this->render($contenido, $file_id, $resultado->key, $filename_copia, true, $firmar);

        return $resultado;
    }

    public function previsualizar() {
        $this->render($this->contenido, '123456789', 'abcdefghijkl');
    }

    private function render($contenido, $identifier, $key, $filename = false, $copia = false, $firmar = false) {


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
        } else {
            $obj->Output($filename);
        }



        return;
    }

}
