<?php

class UsuarioBackend extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('usuario');
        $this->hasColumn('password');
        $this->hasColumn('nombre');
        $this->hasColumn('apellidos');
        $this->hasColumn('salt');
        $this->hasColumn('cuenta_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Cuenta',array(
            'local'=>'cuenta_id',
            'foreign'=>'id'
        ));
    }
    
    function setPassword($password,$salt=null) {        
        $hashPassword = sha1($password.$this->salt);
        $this->_set('password', $hashPassword);
    }
    
    function setPasswordWithSalt($password,$salt=null){
        if($salt!==null)
            $this->salt=$salt;
        else
            $this->salt=random_string ('alnum', 32);
        
        $this->setPassword($password);
    }

}
