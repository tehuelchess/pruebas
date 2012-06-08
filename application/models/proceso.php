<?php

class Proceso extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('cuenta_id');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Cuenta',array(
            'local'=>'cuenta_id',
            'foreign'=>'id'
        ));
        
        $this->hasMany('Tarea as Tareas',array(
            'local'=>'id',
            'foreign'=>'proceso_id',
        ));
        
        $this->hasMany('Formulario as Formularios',array(
            'local'=>'id',
            'foreign'=>'proceso_id',
        ));
        
        $this->hasMany('Accion as Acciones',array(
            'local'=>'id',
            'foreign'=>'proceso_id',
        ));
    }
    
    public function updateModelFromJSON($json){
        Doctrine_Manager::connection()->beginTransaction();
        $modelo = json_decode($json);

        //Agregamos los elementos nuevos y/o existentes
        foreach ($modelo->elements as $e) {
            $tarea = Doctrine::getTable('Tarea')->findOneByIdentificadorAndProcesoId($e->id, $this->id);
            $tarea->posx = $e->left;
            $tarea->posy = $e->top;
            $tarea->save();
        }

        Doctrine_Manager::connection()->commit();
        
    }
    
    public function getJSONFromModel(){
        Doctrine_Manager::connection()->beginTransaction();
        
        $modelo->nombre=$this->nombre;
        $modelo->elements=array();
        $modelo->connections=array();
        
        $tareas=Doctrine::getTable('Tarea')->findByProcesoId($this->id);
        foreach($tareas as $t){
            $element->id=$t->identificador;
            $element->name=$t->nombre;
            $element->left=$t->posx;
            $element->top=$t->posy;
            $element->start=$t->inicial;
            $element->stop=$t->final;
            $modelo->elements[]=clone $element;
        }
        
        $conexiones=  Doctrine_Query::create()
                ->from('Conexion c, c.TareaOrigen.Proceso p')
                ->where('p.id = ?',$this->id)
                ->execute();
        foreach($conexiones as $c){
            $conexion->id=$c->identificador;
            $conexion->source=$c->TareaOrigen->identificador;
            $conexion->target=$c->TareaDestino->identificador;
            $conexion->tipo=$c->tipo;
            $modelo->connections[]=clone $conexion;
        }
        
        Doctrine_Manager::connection()->commit();
        
        return json_encode($modelo);
    }
    
    public function getTareaInicial(){
        return Doctrine_Query::create()
                ->from('Tarea t, t.Proceso p')
                ->where('t.inicial = 1 AND p.id = ?',$this->id)
                ->fetchOne();
    }
    
    //Verifica si el usuario_id tiene permisos para iniciar este proceso como tramite.
    public function canUsuarioIniciarlo($usuario_id){
        $proceso=Doctrine_Query::create()
                ->from('Proceso p, p.Tareas t, t.GruposUsuarios g, g.Usuarios u')
                ->where('t.inicial = 1 and p.id = ?',$this->id)
                ->andWhere('u.id = ? OR g.registrados = 1',$usuario_id)
                ->fetchOne();
        
        if($proceso)
            return TRUE;
        
        return FALSE;
    }

}
