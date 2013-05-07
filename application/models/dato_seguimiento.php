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
    
    //Al guardar, guardamos una copia en la tabla Dato, por si la deprecasion sale mal.
    public function save(\Doctrine_Connection $conn = null) {
        parent::save($conn);
        
        $dato=Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($this->Etapa->tramite_id,$this->nombre);
        if(!$dato)
            $dato=new Dato();
        $dato->nombre=$this->nombre;
        $dato->valor=$this->valor;
        $dato->tramite_id=$this->Etapa->tramite_id;
        $dato->save();
    }

}
