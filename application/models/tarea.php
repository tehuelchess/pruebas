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
        $this->hasColumn('activacion');                     //'si','no','entre_fechas'
        $this->hasColumn('activacion_inicio');              //Si es que la activacion es entre_fechas, esta seria la fecha de inicio
        $this->hasColumn('activacion_fin');                 //Si es que la activacion es entre_fechas, esta seria la fecha de fin
        $this->hasColumn('vencimiento');                    //Indica si tiene o no vencimiento.
        $this->hasColumn('vencimiento_valor');              //Entero que indica el valor del vencimiento.
        $this->hasColumn('vencimiento_unidad');             //String que indica la unidad del vencimiento. Ej: days, weeks, months, etc.
        $this->hasColumn('vencimiento_notificar');          //Indica si se debe notificar en caso de que se acerque la fecha de vencimiento
        $this->hasColumn('vencimiento_notificar_email');    //Cual es el email donde se debe notificar
        
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

    //Obtiene el listado de usuarios que tienen acceso a esta tarea y que esten disponibles (no en vacaciones).
    public function getUsuarios() {
        if ($this->acceso_modo == 'publico')
            return Doctrine::getTable('Usuario')->findByVacaciones(0);
        else if ($this->acceso_modo == 'registrados')
            return Doctrine::getTable('Usuario')->findByRegistradoAndVacaciones(1,0);
        else if ($this->acceso_modo == 'claveunica')
            return Doctrine::getTable('Usuario')->findByOpenIdAndVacaciones(1,0);


        return Doctrine_Query::create()
                        ->from('Usuario u, u.GruposUsuarios g, g.Tareas t')
                        ->where('t.id = ? AND u.vacaciones = 0', $this->id)
                        ->execute();
    }
    
    //Obtiene el listado de usuarios que tienen acceso a esta tarea y que esten disponibles (no en vacaciones).
    //Ademas, deben pertenecer a alguno de los grupos de usuarios definidos en la cuenta
    public function getUsuariosFromGruposDeUsuarioDeCuenta() {
        $query=Doctrine_Query::create()
                ->from('Usuario u, u.GruposUsuarios g, g.Cuenta.Procesos.Tareas t')
                ->where('u.vacaciones = 0')
                ->andWhere('t.id = ?', $this->id);
        
        if($this->acceso_modo=='registrados')
            $query->andWhere('u.registrado = 1');
        else if($this->acceso_modo=='claveunica')
            $query->andWhere('u.open_id = 1');  
        else if($this->acceso_modo=='grupos_usuarios'){
            $query->leftJoin('g.Tareas tar');
            $query->andWhere('tar.id = ?',$this->id);
        }
            
        
        $usuarios=$query->execute();
        return $usuarios;
    }

    //Obtiene si el usuarios tiene acceso a esta tarea.
    public function hasUsuario($usuario_id) {
        if ($this->acceso_modo == 'publico')
            return Doctrine::getTable('Usuario')->findById($usuario_id)->count() ? true : false;
        else if ($this->acceso_modo == 'registrados')
            return Doctrine::getTable('Usuario')->findByIdAndRegistrado($usuario_id, 1) ? true : false;
        else if ($this->acceso_modo == 'claveunica')
            return Doctrine::getTable('Usuario')->findByIdAndOpenId($usuario_id, 1) ? true : false;

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
            foreach ($grupos_usuarios_ids as $g){
                $grupo=Doctrine::getTable('GrupoUsuarios')->find($g);
                if($grupo->cuenta_id==$this->Proceso->cuenta_id)
                    $this->GruposUsuarios[] = $grupo;
            }
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
                $paso->id=$p['id'];
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
                $evento->regla=$p['regla'];
                $evento->instante = $p['instante'];
                $evento->accion_id = $p['accion_id'];
                $evento->paso_id = $p['paso_id'];
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
    
    //Indica si esta tarea se encuentra activa. Es decir, se puede ejecutar.
    public function activa(){
        if($this->activacion=='no')
            return FALSE;
        
        if($this->activacion=='entre_fechas'){
            if($this->activacion_inicio && $this->activacion_inicio>now())
                return FALSE;
            if($this->activacion_fin && now()>$this->activacion_fin)
                return FALSE;
        }
        
        return TRUE;
    }
    
    public function getActivacionInicio(){
        if($this->_get('activacion_inicio'))
            return mysql_to_unix($this->_get('activacion_inicio'));
        else
            return NULL;
    }
    
    public function getActivacionFin(){
        if($this->_get('activacion_fin'))
            return mysql_to_unix($this->_get('activacion_fin'));
        else
            return NULL;
    }
    
    public function setActivacionInicio($date){
        if($date)
            $this->_set('activacion_inicio', date('Y-m-d', $date));
        else
            $this->_set('activacion_inicio', NULL);
    }
    
    public function setActivacionFin($date){
        if($date)
            $this->_set('activacion_fin', date('Y-m-d', $date));
        else
            $this->_set('activacion_fin', NULL);
    }
    
}
