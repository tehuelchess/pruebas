<?php

class GrupoUsuarios extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasMany('Tarea as Tareas',array(
            'local'=>'grupo_usuarios_id',
            'foreign'=>'tarea_id',
            'refClass' => 'TareaHasGrupoUsuarios'
        ));
        
        $this->hasMany('Usuario as Usuarios',array(
            'local'=>'grupo_usuarios_id',
            'foreign'=>'usuario_id',
            'refClass' => 'GrupoUsuariosHasUsuario'
        ));
    }

}
