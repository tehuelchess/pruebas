<?php

class Dato extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('valor');
        $this->hasColumn('tramite_id');
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Tramite',array(
            'local'=>'tramite_id',
            'foreign'=>'id'
        ));

      
        

    }
    
    public function setValor($valor){
        $this->_set('valor', json_encode($valor));
    }
    
    public function getValor(){
        return json_decode($this->_get('valor'));
    }
    
    //Al guardar se debe pasar como parametro la etapa de este dato para que se almacene en la tabla de seguimiento
    public function save(\Doctrine_Connection $conn = null, Etapa $etapa) {
        parent::save($conn);
        
        $dato_seguimiento=Doctrine::getTable('DatoSeguimiento')->findOneByEtapaIdAndNombre($etapa->id,$this->nombre);
        if(!$dato_seguimiento)
            $dato_seguimiento=new DatoSeguimiento();
        $dato_seguimiento->nombre=$this->nombre;
        $dato_seguimiento->valor=$this->valor;
        $dato_seguimiento->Etapa=$etapa;
        $dato_seguimiento->save();
    }

}
