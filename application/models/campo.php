<?php

class Campo extends Doctrine_Record {
        
    public $requiere_datos=true;
    
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
        else if($tipo=='comunas')
            $campo=new CampoComunas();
        else if($tipo=='title')
            $campo=new CampoTitle();
        else if($tipo=='subtitle')
            $campo=new CampoSubtitle();
        else if($tipo=='paragraph')
            $campo=new CampoParagraph();
        else if($tipo=='documento')
            $campo=new CampoDocumento();
        
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
        $this->hasColumn('ayuda');
        $this->hasColumn('dependiente_tipo');
        $this->hasColumn('dependiente_campo');
        $this->hasColumn('dependiente_valor');
        $this->hasColumn('datos');
        $this->hasColumn('readonly');           //Indica que en este campo solo se mostrara la informacion.
        $this->hasColumn('estatico');           //Indica si es un campo estatico, es decir que no es un input con informacion. Ej: Parrafos, titulos, etc.
        $this->hasColumn('valor_default');
        $this->hasColumn('documento_id');
        $this->hasColumn('extra');
        
        $this->setSubclasses(array(
                'CampoText'  => array('tipo' => 'text'),
                'CampoTextArea'  => array('tipo' => 'textarea'),
                'CampoSelect'  => array('tipo' => 'select'),
                'CampoRadio'  => array('tipo' => 'radio'),
                'CampoCheckbox'  => array('tipo' => 'checkbox'),
                'CampoFile'  => array('tipo' => 'file'),
                'CampoDate'  => array('tipo' => 'date'),
                'CampoInstitucionesGob'  => array('tipo' => 'instituciones_gob'),
                'CampoComunas'  => array('tipo' => 'comunas'),
                'CampoTitle'  => array('tipo' => 'title'),
                'CampoSubtitle'  => array('tipo' => 'subtitle'),
                'CampoParagraph'  => array('tipo' => 'paragraph'),
                'CampoDocumento'  => array('tipo' => 'documento')
            ));
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Formulario', array(
            'local' => 'formulario_id',
            'foreign' => 'id'
        ));
        
        $this->hasOne('Documento', array(
            'local' => 'documento_id',
            'foreign' => 'id'
        ));
        
        $this->hasOne('Reporte', array(
            'local' => 'reporte_id',
            'foreign' => 'id'
        ));
    }
    
    //Despliega la vista de un campo del formulario utilizando el dato real del tramite en este momento
    //etapa_id indica a la etapa que pertenece este campo
    //modo es visualizacion o edicion
    public function displayConDato($etapa_id, $modo = 'edicion'){
        $dato = NULL;
        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
        $dato =Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($etapa->Tramite->id, $this->nombre);
        if($this->readonly)$modo='visualizacion';
        
        return $this->display($modo,$dato,$etapa_id);
    }
    
    //Despliega la vista de un campo del formulario utilizando los datos de seguimiento (El dato que contenia el tramite al momento de cerrar la etapa)
    //etapa_id indica a la etapa que pertenece este campo
    //modo es visualizacion o edicion
    public function displayConDatoSeguimiento($etapa_id, $modo = 'edicion'){
        $dato = NULL;
        $dato =  Doctrine::getTable('DatoSeguimiento')->findOneByEtapaIdAndNombre($etapa_id, $this->nombre);
        if($this->readonly)$modo='visualizacion';
        
        return $this->display($modo,$dato,$etapa_id);
    }
    
    public function displaySinDato($modo = 'edicion'){   
        if($this->readonly)$modo='visualizacion';
        return $this->display($modo,NULL,NULL);
    }

    
    protected function display($modo, $dato){
        return '';
    }
    
    //Funcion que retorna si este campo debiera poderse editar de acuerdo al input POST del usuario
    public function isEditableWithCurrentPOST(){
        $CI=& get_instance();
        
        if($this->readonly)
           return false; 
        
        if($this->dependiente_campo){
            $variable=preg_replace('/\[\]$/', '', $this->dependiente_campo);
            if(is_array($CI->input->post($variable))){ //Es un arreglo
                if($this->dependiente_tipo=='regex'){
                    foreach($CI->input->post($variable) as $x){
                        if(!preg_match('/'.$this->dependiente_valor.'/', $x))
                            return false;
                    }
                }else{
                    if(!in_array($this->dependiente_valor, $CI->input->post($variable)))
                        return false;
                }
            }else{
                if($this->dependiente_tipo=='regex'){
                    if(!preg_match('/'.$this->dependiente_valor.'/', $CI->input->post($variable)))
                        return false;
                }else{
                    if($CI->input->post($variable)!=$this->dependiente_valor)
                        return false;
                }
                
            }
            
            
        }
        
        return true;
    }
    
    public function formValidate(){
        $CI=& get_instance();
        $CI->form_validation->set_rules($this->nombre, $this->etiqueta, implode('|', $this->validacion));
    }
    
    
    //Señala como se debe mostrar en el formulario de edicion del backend, cualquier field extra.
    public function backendExtraFields(){
        return;
    }
    
    //Validaciones adicionales que se le deben hacer a este campo en su edicion en el backend.
    public function backendExtraValidate(){
        
    }
    
    public function setValidacion($validacion){
        if($validacion)
            $this->_set('validacion',  implode ('|', $validacion));
        else
            $this->_set('validacion',NULL);
    }
    
    public function getValidacion(){
        if($this->_get('validacion'))
            return explode('|',$this->_get('validacion'));
        else
            return array();
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
    
    public function setDocumentoId($documento_id){
        if($documento_id=='')
            $documento_id=null;
        
        $this->_set('documento_id',$documento_id);
    }
    
    public function extraForm(){
        return false;
    }
    
    public function setExtra($datos_array) {
        if ($datos_array) 
            $this->_set('extra' , json_encode($datos_array));
        else 
            $this->_set('extra' , NULL);
    }
    
    public function getExtra(){
        return json_decode($this->_get('extra'));
    }

}
