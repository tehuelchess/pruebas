<?php

class File extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('filename');
        $this->hasColumn('tipo');
        $this->hasColumn('llave');
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
    
    public function setLlave($llave){
        if($llave)
            $this->_set('llave',sha1($llave));
        else
            $this->_set('llave',null);
    }

}
