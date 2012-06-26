<?php

class DatoSeguimiento extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('valor');
        $this->hasColumn('etapa_id');
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Etapa',array(
            'local'=>'etapa_id',
            'foreign'=>'id'
        ));

      
        

    }
    
    public function setValor($valor){
        $this->_set('valor', json_encode($valor));
    }
    
    public function getValor(){
        return json_decode($this->_get('valor'));
    }

}
