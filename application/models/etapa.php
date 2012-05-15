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
    
    //Verifica si el usuario_id tiene permisos para asignarse esta etapa del tramite.
    public function canUsuarioAsignarsela($usuario_id){
        $grupos=Doctrine::getTable('Usuario')->find($usuario_id)->GruposUsuarios;
        $grupos_array=array();
        foreach($grupos as $g)
            $grupos_array[]=$g->id;
        
        $tramite=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, tar.GruposUsuarios g')
                ->where('e.usuario_id IS NULL')
                ->andWhere('e.id = ?',$this->id)
                ->andWhereIn('g.id',$grupos_array)
                ->fetchOne();
        
        if($tramite)
            return TRUE;
        
        return FALSE;
    }

}
