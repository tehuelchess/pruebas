<?php

class Evento extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('regla');
        $this->hasColumn('instante');
        $this->hasColumn('accion_id');
        $this->hasColumn('tarea_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Accion',array(
            'local'=>'accion_id',
            'foreign'=>'id'
        ));
        
        $this->hasOne('Tarea',array(
            'local'=>'tarea_id',
            'foreign'=>'id'
        ));
    }

}
