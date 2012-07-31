<?php

class File extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('filename');
        $this->hasColumn('tipo');
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
