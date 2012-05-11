<?php

class Conexion extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('identificador');
        $this->hasColumn('tarea_id_origen');
        $this->hasColumn('tarea_id_destino');
        $this->hasColumn('tipo');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Tarea as TareaOrigen',array(
            'local'=>'tarea_id_origen',
            'foreign'=>'id'
        ));
        
        $this->hasOne('Tarea as TareaDestino',array(
            'local'=>'tarea_id_destino',
            'foreign'=>'id'
        ));
    }

}
