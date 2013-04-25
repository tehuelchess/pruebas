<?php

class File extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('filename');
        $this->hasColumn('tipo');
        $this->hasColumn('llave_copia');
        $this->hasColumn('validez');
        $this->hasColumn('tramite_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->actAs('Timestampable');

        $this->hasOne('Tramite',array(
            'local'=>'tramite_id',
            'foreign'=>'id'
        ));

      
        

    }
    
    public function setLlaveCopia($llave){
        if($llave)
            $this->_set('llave_copia',sha1($llave));
        else
            $this->_set('llave_copia',null);
    }

}
