<?php

class UsuarioBackend extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('usuario');
        $this->hasColumn('password');
        $this->hasColumn('nombre');
        $this->hasColumn('apellidos');
        $this->hasColumn('cuenta_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Cuenta',array(
            'local'=>'id',
            'foreign'=>'cuenta_id'
        ));
    }
    
    function setPassword($password) {
        $hashPassword = sha1($password);
        $this->_set('password', $hashPassword);
    }

}
