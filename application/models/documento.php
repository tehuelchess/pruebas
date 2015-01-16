<?php

class Documento extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('tipo');
        $this->hasColumn('nombre');
        $this->hasColumn('titulo');
        $this->hasColumn('subtitulo');
        $this->hasColumn('contenido');
        $this->hasColumn('servicio');
        $this->hasColumn('servicio_url');
        $this->hasColumn('validez');
        $this->hasColumn('validez_habiles');
        $this->hasColumn('firmador_nombre');
        $this->hasColumn('firmador_cargo');
        $this->hasColumn('firmador_servicio');
        $this->hasColumn('firmador_imagen');
        $this->hasColumn('proceso_id');
        $this->hasColumn('timbre');
        $this->hasColumn('logo');
        $this->hasColumn('hsm_configuracion_id');
        $this->hasColumn('tamano');
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

    public function generar($etapa_id) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        $filename_uniqid = uniqid();
        
        //Generamos el file
        $file = new File();
        $file->tramite_id = $etapa->tramite_id;
        $file->tipo = 'documento';
        $file->llave = strtolower(random_string('alnum', 12));
        $file->llave_copia = $this->tipo == 'certificado' ? strtolower(random_string('alnum', 12)) : null;
        $file->llave_firma = strtolower(random_string('alnum', 12));
        if($this->tipo=='certificado'){
            $file->validez = $this->validez;
            $file->validez_habiles= $this->validez_habiles;
        }
        $file->filename = $filename_uniqid . '.pdf';
        $file->save();

        //Renderizamos     
        $this->render($file->id, $file->llave_copia, $etapa->id, $file->filename, false);
        $filename_copia = $filename_uniqid . '.copia.pdf';
        $this->render($file->id, $file->llave_copia, $etapa->id,$filename_copia, true);

        return $file;
    }

    public function previsualizar() {
        $this->render('123456789', 'abcdefghijkl');
    }

    private function render($identifier, $key, $etapa_id=null ,$filename = false, $copia = false) {


        $uploadDirectory = 'uploads/documentos/';

        $CI = &get_instance();

        if ($this->tipo == 'certificado') {
            $CI->load->library('certificadopdf');
            $obj = new $CI->certificadopdf($this->tamano);
            
            $contenido=$this->contenido;
            $titulo=$this->titulo;
            $subtitulo=$this->subtitulo;
            $firmador_nombre = $this->firmador_nombre;
            $firmador_cargo = $this->firmador_cargo;
            $firmador_servicio = $this->firmador_servicio;
            if($etapa_id){
                $regla = new Regla($contenido);
                $contenido = $regla->getExpresionParaOutput($etapa_id);  
                $regla = new Regla($titulo);
                $titulo = $regla->getExpresionParaOutput($etapa_id);
                $regla = new Regla($subtitulo);
                $subtitulo = $regla->getExpresionParaOutput($etapa_id);
                $regla = new Regla($firmador_nombre);
                $firmador_nombre = $regla->getExpresionParaOutput($etapa_id); 
                $regla = new Regla($firmador_cargo);
                $firmador_cargo = $regla->getExpresionParaOutput($etapa_id); 
                $regla = new Regla($firmador_servicio);
                $firmador_servicio = $regla->getExpresionParaOutput($etapa_id); 
            }

            $obj->content = $contenido;
            $obj->id = $identifier;
            $obj->key = $key;
            $obj->servicio = $this->servicio;
            $obj->servicio_url = $this->servicio_url;
            if ($this->logo)
                $obj->logo = 'uploads/logos_certificados/' . $this->logo;
            $obj->titulo = $titulo;
            $obj->subtitulo = $subtitulo;
            $obj->validez = $this->validez;
            $obj->validez_habiles = $this->validez_habiles;
            if ($this->timbre)
                $obj->timbre = 'uploads/timbres/' . $this->timbre;
            $obj->firmador_nombre = $firmador_nombre;
            $obj->firmado_cargo = $firmador_cargo;
            $obj->firmador_servicio = $firmador_servicio;
            if ($this->firmador_imagen)
                $obj->firmador_imagen = 'uploads/firmas/' . $this->firmador_imagen;
            $obj->firma_electronica = $this->hsm_configuracion_id ? true : false;
            $obj->copia = $copia;
        }else {
            $CI->load->library('blancopdf');
            $obj = new $CI->blancopdf($this->tamano);
            
            $contenido=$this->contenido;
            if($etapa_id){
                $regla = new Regla($contenido);
                $contenido = $regla->getExpresionParaOutput($etapa_id);  
            }
            
            $obj->content = $contenido;
        }

        if ($filename) {
            $obj->Output($uploadDirectory . $filename, 'F');
            if (!$copia && $this->hsm_configuracion_id) {
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
