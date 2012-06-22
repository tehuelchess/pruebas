<?php

class Campo extends Doctrine_Record {

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
        
        $this->setSubclasses(array(
                'CampoText'  => array('tipo' => 'text'),
                'CampoTextArea'  => array('tipo' => 'textarea'),
                'CampoSelect'  => array('tipo' => 'select'),
                'CampoRadio'  => array('tipo' => 'radio'),
                'CampoCheckbox'  => array('tipo' => 'checkbox'),
                'CampoFile'  => array('tipo' => 'file'),
                'CampoDate'  => array('tipo' => 'date'),
                'CampoInstitucionesGob'  => array('tipo' => 'instituciones_gob')
            )
        );
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Formulario', array(
            'local' => 'formulario_id',
            'foreign' => 'id'
        ));
    }

    //Despliega la vista de un campo del formulario
    //tramite_id indica al tramite que pertenece este campo
    //modo es visualizacion o edicion
    public function display($modo = 'edicion', $tramite_id = NULL){
        return '';
    }
    
    public function formValidate(){
        $CI=& get_instance();
        $CI->form_validation->set_rules($this->nombre, $this->etiqueta, implode('|', $this->validacion));
    }
    
    protected function getDatoDeTramite($tramite_id){
        $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $this->nombre);
        
        return $dato;
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
