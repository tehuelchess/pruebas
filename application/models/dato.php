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

}
