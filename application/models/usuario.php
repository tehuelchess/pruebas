<?php

class Usuario extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('usuario');
        $this->hasColumn('password');
        $this->hasColumn('nombre');
        $this->hasColumn('apellidos');
        $this->hasColumn('email');
        $this->hasColumn('cuenta_id');
        $this->hasColumn('registrado');
    }

    function setUp() {
        parent::setUp();
        
        $this->actAs('Timestampable');
        
        $this->hasMany('GrupoUsuarios as GruposUsuarios',array(
            'local'=>'usuario_id',
            'foreign'=>'grupo_usuarios_id',
            'refClass' => 'GrupoUsuariosHasUsuario'
        ));
        
        $this->hasMany('Etapa as Etapas',array(
            'local'=>'id',
            'foreign'=>'usuario_id'
        ));
        
        $this->hasOne('Cuenta',array(
            'local'=>'cuenta_id',
            'foreign'=>'id'
        ));
    }
    
    function setPassword($password) {
        $hashPassword = sha1($password);
        $this->_set('password', $hashPassword);
    }
    
    public function hasGrupoUsuarios($grupo_usuarios_id){
        foreach($this->GruposUsuarios as $g)
            if($g->id==$grupo_usuarios_id)
                return TRUE;
            
        return FALSE;
    }
    
    public function setGruposUsuariosFromArray($grupos_usuarios_array){
        foreach($this->GruposUsuarios as $key=>$val)
            unset($this->GruposUsuarios[$key]);
        
        if($grupos_usuarios_array)
            foreach($grupos_usuarios_array as $g)
                $this->GruposUsuarios[]=Doctrine::getTable('GrupoUsuarios')->find($g);
    }

    public function displayName(){
        if($this->email)
            return $this->email;
        
        return $this->usuario;
    }
}
