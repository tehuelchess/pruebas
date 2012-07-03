<?php

class Campo extends Doctrine_Record {
        
    public $requiere_validacion=true;
    public $requiere_datos=true;
    public $requiere_readonly=true;
    
    public static function factory($tipo){
        if($tipo=='text')
            $campo=new CampoText();
        else if($tipo=='textarea')
            $campo=new CampoTextArea();
        else if($tipo=='select')
            $campo=new CampoSelect();
        else if($tipo=='radio')
            $campo=new CampoRadio();
        else if($tipo=='checkbox')
            $campo=new CampoCheckbox();
        else if($tipo=='file')
            $campo=new CampoFile();
        else if($tipo=='date')
            $campo=new CampoDate();
        else if($tipo=='instituciones_gob')
            $campo=new CampoInstitucionesGob();
        else if($tipo=='title')
            $campo=new CampoTitle();
        else if($tipo=='subtitle')
            $campo=new CampoSubtitle();
        
        $campo->assignInheritanceValues();
        
        return $campo;
    }

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('posicion');
        $this->hasColumn('tipo');
        $this->hasColumn('formulario_id');
        $this->hasColumn('etiqueta');
        $this->hasColumn('validacion');
        $this->hasColumn('dependiente_campo');
        $this->hasColumn('dependiente_valor');
        $this->hasColumn('datos');
        $this->hasColumn('readonly');
        
        $this->setSubclasses(array(
                'CampoText'  => array('tipo' => 'text'),
                'CampoTextArea'  => array('tipo' => 'textarea'),
                'CampoSelect'  => array('tipo' => 'select'),
                'CampoRadio'  => array('tipo' => 'radio'),
                'CampoCheckbox'  => array('tipo' => 'checkbox'),
                'CampoFile'  => array('tipo' => 'file'),
                'CampoDate'  => array('tipo' => 'date'),
                'CampoInstitucionesGob'  => array('tipo' => 'instituciones_gob'),
                'CampoTitle'  => array('tipo' => 'title'),
                'CampoSubtitle'  => array('tipo' => 'subtitle')
            ));
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Formulario', array(
            'local' => 'formulario_id',
            'foreign' => 'id'
        ));
    }
    
    //Despliega la vista de un campo del formulario utilizando el dato real del tramite en este momento
    //tramite_id indica a la etapa que pertenece este campo
    //modo es visualizacion o edicion
    public function displayConDato($tramite_id, $modo = 'edicion'){
        $dato = NULL;
        if ($tramite_id)
            $dato =  Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $this->nombre);
        
        return $this->display($modo,$dato);
    }
    
    //Despliega la vista de un campo del formulario utilizando los datos de seguimiento (El dato que contenia el tramite al momento de cerrar la etapa)
    //etapa_id indica a la etapa que pertenece este campo
    //modo es visualizacion o edicion
    public function displayConDatoSeguimiento($etapa_id, $modo = 'edicion'){
        $dato = NULL;
        if ($etapa_id)
            $dato =  Doctrine::getTable('DatoSeguimiento')->findOneByEtapaIdAndNombre($etapa_id, $this->nombre);
        
        return $this->display($modo,$dato);
    }
    
    public function displaySinDato($modo = 'edicion'){     
        return $this->display($modo,NULL);
    }

    
    protected function display($modo, $dato){
        return '';
    }
    
    public function formValidate(){
        $CI=& get_instance();
        $CI->form_validation->set_rules($this->nombre, $this->etiqueta, implode('|', $this->validacion));
    }
    
    
    public function setValidacion($validacion){
        if($validacion)
            $this->_set('validacion',  implode ('|', $validacion));
        else
            $this->_set('validacion',NULL);
    }
    
    public function getValidacion(){
        return explode('|',$this->_get('validacion'));
    }

    public function setDatos($datos_array) {
        if ($datos_array) 
            $this->_set('datos' , json_encode($datos_array));
        else 
            $this->_set('datos' , NULL);
    }

    public function getDatos() {
        return json_decode($this->_get('datos'));
    }

}
