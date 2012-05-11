<?php

class Etapa extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('tarea_id');
        $this->hasColumn('tramite_id');
        $this->hasColumn('usuario_id');
        $this->hasColumn('pendiente');
        $this->hasColumn('created_at');
        $this->hasColumn('updated_at');
        $this->hasColumn('ended_at');

    }

    function setUp() {
        parent::setUp();
        
        $this->actAs('Timestampable');
        
        $this->hasOne('Tarea',array(
            'local'=>'tarea_id',
            'foreign'=>'id'
        ));
        
        $this->hasOne('Tramite',array(
            'local'=>'tramite_id',
            'foreign'=>'id'
        ));
        
        $this->hasOne('Usuario',array(
            'local'=>'usuario_id',
            'foreign'=>'id'
        ));
        
    }

}
