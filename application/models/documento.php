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
        $this->hasColumn('timbre');
        $this->hasColumn('logo');
        $this->hasColumn('hsm_configuracion_id');
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
        
        $this->hasOne('HsmConfiguracion', array(
            'local' => 'hsm_configuracion_id',
            'foreign' => 'id'
        ));
    }

    public function setValidez($validez) {
        if (!$validez)
            $validez = null;

        $this->_set('validez', $validez);
    }
    
    public function setHsmConfiguracionId($hsm_configuracion_id) {
        if (!$hsm_configuracion_id)
            $hsm_configuracion_id = null;

        $this->_set('hsm_configuracion_id', $hsm_configuracion_id);
    }

    public function generar($file_id, $etapa_id) {
        $regla = new Regla($this->contenido);
        $contenido = $regla->getExpresionParaOutput($etapa_id);

        $resultado->llave_copia=$this->tipo=='certificado'?strtolower(random_string('alnum', 12)):null;
        $resultado->validez=$this->tipo=='certificado'?$this->validez:null;
        $filename_uniqid = uniqid();     

        $resultado->filename = $filename_uniqid . '.pdf';
        $this->render($contenido, $file_id, $resultado->llave_copia, $resultado->filename, false);
        $filename_copia = $filename_uniqid . '.copia.pdf';
        $this->render($contenido, $file_id, $resultado->llave_copia, $filename_copia, true);

        return $resultado;
    }

    public function previsualizar() {
        $this->render($this->contenido, '123456789', 'abcdefghijkl');
    }

    private function render($contenido, $identifier, $key, $filename = false, $copia = false) {


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
            if($this->logo)
                $obj->logo = 'uploads/logos_certificados/'.$this->logo;
            $obj->titulo = $this->nombre;
            $obj->validez = $this->validez;
            if($this->timbre)
                $obj->timbre = 'uploads/timbres/'.$this->timbre;
            $obj->firmador_nombre = $this->firmador_nombre;
            $obj->firmado_cargo = $this->firmador_cargo;
            $obj->firmador_servicio = $this->firmador_servicio;
            if ($this->firmador_imagen)
                $obj->firmador_imagen = 'uploads/firmas/' . $this->firmador_imagen;
            $obj->firma_electronica = $this->hsm_configuracion_id?true:false;
            $obj->copia = $copia;
        }else {
            $CI->load->library('blancopdf');
            $obj = new $CI->blancopdf;
            $obj->content=$contenido;
        }

        if ($filename) {
            $obj->Output($uploadDirectory . $filename, 'F');
            if(!$copia && $this->hsm_configuracion_id) {
                $client = new SoapClient($CI->config->item('hsm_url'));
                
                $result = $client->IntercambiaDoc(array(
                    'Encabezado' => array(
                        'User' => $CI->config->item('hsm_user'),
                        'Password' => $CI->config->item('hsm_password'),
                        'TipoIntercambio' => 'pdf',
                        'NombreConfiguracion' => $this->HsmConfiguracion->nombre,
                        'FormatoDocumento' => 'b64'
                    ),
                    'Parametro' => array(
                        'Documento' => base64_encode(file_get_contents($uploadDirectory . $filename)),
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
