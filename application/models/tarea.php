<?php

class Tarea extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('identificador');
        $this->hasColumn('inicial');
        //$this->hasColumn('final');
        $this->hasColumn('proceso_id');
        $this->hasColumn('nombre');
        $this->hasColumn('posx');
        $this->hasColumn('posy');
        $this->hasColumn('asignacion');                     //Modo de asignacion
        $this->hasColumn('asignacion_usuario');             //Id de usuario al que se le va a asignar en caso que modo de asignacion sea 'usuario'
        $this->hasColumn('asignacion_notificar');             //Indica si se le debe notificar via email al usuario que se le asigna esta tarea
        $this->hasColumn('almacenar_usuario');              //Se almacena el usuario o no
        $this->hasColumn('almacenar_usuario_variable');     //Nombre de la variable con que se debe almacenar
        $this->hasColumn('acceso_modo');                    //Quienes pueden acceder: grupos_usuarios, publico o registrados
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Etapa as Etapas', array(
            'local' => 'id',
            'foreign' => 'tarea_id'
        ));

        $this->hasMany('Conexion as ConexionesOrigen', array(
            'local' => 'id',
            'foreign' => 'tarea_id_origen'
        ));

        $this->hasMany('Conexion as ConexionesDestino', array(
            'local' => 'id',
            'foreign' => 'tarea_id_destino'
        ));

        $this->hasMany('GrupoUsuarios as GruposUsuarios', array(
            'local' => 'tarea_id',
            'foreign' => 'grupo_usuarios_id',
            'refClass' => 'TareaHasGrupoUsuarios'
        ));

        $this->hasMany('Paso as Pasos', array(
            'local' => 'id',
            'foreign' => 'tarea_id',
            'orderBy' => 'orden'
        ));

        $this->hasMany('Evento as Eventos', array(
            'local' => 'id',
            'foreign' => 'tarea_id'
        ));
    }

    public function hasGrupoUsuarios($grupo_id) {
        foreach ($this->GruposUsuarios as $g)
            if ($g->id == $grupo_id)
                return true;

        return false;
    }

    //Obtiene el listado de usuarios que tienen acceso a esta tarea.
    public function getUsuarios() {
        if ($this->acceso_modo == 'publico')
            return Doctrine::getTable('Usuario')->findAll();
        else if ($this->acceso_modo == 'registrados')
            return Doctrine::getTable('Usuario')->findByRegistrado(1);


        return Doctrine_Query::create()
                        ->from('Usuario u, u.GruposUsuarios g, g.Tareas t')
                        ->where('t.id = ?', $this->id)
                        ->execute();
    }

    //Obtiene si el usuarios tiene acceso a esta tarea.
    public function hasUsuario($usuario_id) {
        if ($this->acceso_modo == 'publico')
            return Doctrine::getTable('Usuario')->findById($usuario_id)->count() ? true : false;
        else if ($this->acceso_modo == 'registrados')
            return Doctrine::getTable('Usuario')->findByIdAndRegistrado($usuario_id, 1) ? true : false;

        return Doctrine_Query::create()
                        ->from('Usuario u, u.GruposUsuarios g, g.Tareas t')
                        ->where('t.id = ? AND u.id=?', array($this->id, $usuario_id))
                        ->count() ? true : false;
    }

    //Obtiene el ultimo usuario que fue a asignado a esta tarea dentro del tramite tramite_id
    public function getUltimoUsuarioAsignado($proceso_id) {
        return Doctrine_Query::create()
                        ->from('Usuario u, u.Etapas e, e.Tarea t, e.Tramite.Proceso p')
                        ->where('t.id = ? AND p.id = ?', array($this->id, $proceso_id))
                        ->orderBy('e.created_at DESC')
                        ->fetchOne();
    }

    public function setGruposUsuariosFromArray($grupos_usuarios_ids) {
        //Limpiamos la lista antigua
        foreach ($this->GruposUsuarios as $key => $val)
            unset($this->GruposUsuarios[$key]);

        //Agregamos los nuevos
        if (is_array($grupos_usuarios_ids))
            foreach ($grupos_usuarios_ids as $g)
                $this->GruposUsuarios[] = Doctrine::getTable('GrupoUsuarios')->find($g);
    }

    public function setConexionesFromArray($conexiones_array) {
        //Limpiamos la lista antigua
        foreach ($this->ConexionesOrigen as $key => $c)
            unset($this->ConexionesOrigen[$key]);

        //Agregamos los nuevos
        if (is_array($conexiones_array)) {
            $tipo = $conexiones_array[0]['tipo'];     //Todas deben ser del mismo tipo si vienen de un origen
            foreach ($conexiones_array as $key => $p) {
                $conexion = new Conexion();
                $conexion->tipo = $tipo;
                $conexion->tarea_id_destino = $p['tarea_id_destino'] ? $p['tarea_id_destino'] : null;
                $conexion->regla = isset($p['regla']) ? $p['regla'] : null;
                $this->ConexionesOrigen[] = $conexion;
            }
        }
    }

    public function setPasosFromArray($pasos_array) {
        //Limpiamos la lista antigua
        foreach ($this->Pasos as $key => $val)
            unset($this->Pasos[$key]);

        //Agregamos los nuevos
        if (is_array($pasos_array)) {
            foreach ($pasos_array as $key => $p) {
                $paso = new Paso();
                $paso->orden = $key;
                $paso->regla = $p['regla'];
                $paso->modo = $p['modo'];
                $paso->formulario_id = $p['formulario_id'];
                $this->Pasos[] = $paso;
            }
        }
    }

    public function setEventosFromArray($eventos_array) {
        //Limpiamos la lista antigua
        foreach ($this->Eventos as $key => $val)
            unset($this->Eventos[$key]);

        //Agregamos los nuevos
        if (is_array($eventos_array)) {
            foreach ($eventos_array as $key => $p) {
                $evento = new Evento();
                $evento->instante = $p['instante'];
                $evento->accion_id = $p['accion_id'];
                $this->Eventos[] = $evento;
            }
        }
    }


    //Setea esta tarea como final
    public function setFinal($final) {
        if($final && !$this->final){
            //Limpiamos las conexiones antiguas
            foreach ($this->ConexionesOrigen as $key => $c)
                unset($this->ConexionesOrigen[$key]);

            $conexion = new Conexion();
            $conexion->tipo = 'secuencial';
            $conexion->tarea_id_destino = null;
            $conexion->regla = null;
            $this->ConexionesOrigen[] = $conexion;
        }else if(!$final && $this->final){
            //Limpiamos las conexiones antiguas
            foreach ($this->ConexionesOrigen as $key => $c)
                unset($this->ConexionesOrigen[$key]);
        }
    }


    //Retorna true si es una tarea final.
    public function getFinal() {
        if ($this->ConexionesOrigen->count() == 1 && $this->ConexionesOrigen[0]->tipo == 'secuencial' && !$this->ConexionesOrigen[0]->tarea_id_destino)
            return true;

        return false;
    }

}
