<?php

class Usuario extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('usuario');
        $this->hasColumn('password');
        $this->hasColumn('nombre');
        $this->hasColumn('apellidos');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasMany('GrupoUsuarios as GruposUsuarios',array(
            'local'=>'usuario_id',
            'foreign'=>'grupo_usuarios_id',
            'refClass' => 'GrupoUsuariosHasUsuario'
        ));
    }
    
    function setPassword($password) {
        $hashPassword = sha1($password);
        $this->_set('password', $hashPassword);
    }

}
