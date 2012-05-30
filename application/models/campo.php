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
        $this->hasColumn('datos');
        
        $this->setSubclasses(array(
                'CampoText'  => array('tipo' => 'text'),
                'CampoTextArea'  => array('tipo' => 'textarea'),
                'CampoSelect'  => array('tipo' => 'select'),
                'CampoRadio'  => array('tipo' => 'radio'),
                'CampoCheckbox'  => array('tipo' => 'checkbox'),
                'CampoFile'  => array('tipo' => 'file')
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
    
    protected function getDatoDeTramite($tramite_id){
        $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $this->nombre);
        
        return $dato;
    }
    
    public function getValidacion(){
        return explode('|',$this->_get('validacion'));
    }

    public function setDatosFromArray($datos_array) {
        if ($datos_array) {
            $this->datos = json_encode($datos_array);
        } else {
            $this->datos = NULL;
        }
    }

    public function getDatosFromJSON() {
        return json_decode($this->datos);
    }

}
