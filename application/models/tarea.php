<?php

class Tarea extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('identificador');
        $this->hasColumn('inicial');
        $this->hasColumn('final');
        $this->hasColumn('proceso_id');
        $this->hasColumn('nombre');
        $this->hasColumn('posx');
        $this->hasColumn('posy');
        $this->hasColumn('asignacion');                     //Modo de asignacion
        $this->hasColumn('asignacion_usuario');             //Id de usuario al que se le va a asignar en caso que modo de asignacion sea 'usuario'
        $this->hasColumn('asignacion_notificar');             //Indica si se le debe notificar via email al usuario que se le asigna esta tarea
        $this->hasColumn('almacenar_usuario');              //Se almacena el usuario o no
        $this->hasColumn('almacenar_usuario_variable');     //Nombre de la variable con que se debe almacenar
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Proceso',array(
            'local'=>'proceso_id',
            'foreign'=>'id'
        ));
        
        $this->hasMany('Etapa as Etapas',array(
            'local'=>'id',
            'foreign'=>'tarea_id'
        ));
        
        $this->hasMany('Conexion as ConexionesOrigen',array(
            'local'=>'id',
            'foreign'=>'tarea_id_origen'
        ));
        
        $this->hasMany('Conexion as ConexionesDestino',array(
            'local'=>'id',
            'foreign'=>'tarea_id_destino'
        ));
        
        $this->hasMany('GrupoUsuarios as GruposUsuarios',array(
            'local'=>'tarea_id',
            'foreign'=>'grupo_usuarios_id',
            'refClass' => 'TareaHasGrupoUsuarios'
        ));
        
        $this->hasMany('Paso as Pasos',array(
            'local'=>'id',
            'foreign'=>'tarea_id',
            'orderBy'=>'orden'
        ));
    }

    public function hasGrupoUsuarios($grupo_id){
        foreach($this->GruposUsuarios as $g)
            if($g->id==$grupo_id)
                return true;
            
        return false;
    }
    
    //Obtiene el listado de usuarios que tienen acceso a esta tarea.
    public function getUsuarios(){
        foreach($this->GruposUsuarios as $g){
            if($g->tipo=='todos')
                return Doctrine::getTable('Usuario')->findAll();
            else if($g->tipo=='registrados')
                return Doctrine::getTable('Usuario')->findByRegistrado(1);
        }
        
        return Doctrine_Query::create()
                ->from('Usuario u, u.GruposUsuarios g, g.Tareas t')
                ->where('t.id = ?',$this->id)
                ->execute();
    }
    
    //Obtiene si el usuarios tiene acceso a esta tarea.
    public function hasUsuario($usuario_id){
        foreach($this->GruposUsuarios as $g){
            if($g->tipo=='todos')
                return Doctrine::getTable('Usuario')->findById($usuario_id)->count()?true:false;
            else if($g->tipo=='registrados')
                return Doctrine::getTable('Usuario')->findByIdAndRegistrado($usuario_id,1)?true:false;
        }
        
        return Doctrine_Query::create()
                ->from('Usuario u, u.GruposUsuarios g, g.Tareas t')
                ->where('t.id = ? AND u.id=?',array($this->id,$usuario_id))
                ->count()?true:false;
        
    }
    
    //Obtiene el ultimo usuario que fue a asignado a esta tarea dentro del tramite tramite_id
    public function getUltimoUsuarioAsignado($proceso_id){
        return Doctrine_Query::create()
                ->from('Usuario u, u.Etapas e, e.Tarea t, e.Tramite.Proceso p')
                ->where('t.id = ? AND p.id = ?',array($this->id,$proceso_id))
                ->orderBy('e.created_at DESC')
                ->fetchOne();
    }
    
    public function setGruposUsuariosFromArray($grupos_usuarios_ids){        
        //Limpiamos la lista antigua
        foreach($this->GruposUsuarios as $key=>$val)
            unset($this->GruposUsuarios[$key]);
        
        //Agregamos los nuevos
        if(is_array($grupos_usuarios_ids))
            foreach($grupos_usuarios_ids as $g)
                $this->GruposUsuarios[]=Doctrine::getTable('GrupoUsuarios')->find($g);
    }
    
    public function setPasosFromArray($pasos_array){        
        //Limpiamos la lista antigua
        foreach($this->Pasos as $key=>$val)
            unset($this->Pasos[$key]);
        
        //Agregamos los nuevos
        if(is_array($pasos_array)){
            foreach($pasos_array as $key=>$p){
                $paso=new Paso();
                $paso->orden=$key;
                $paso->modo=$p['modo'];
                $paso->formulario_id=$p['formulario_id'];
                $this->Pasos[]=$paso;
            }
        }
    }
    
}
