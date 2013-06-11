<?php

class Etapa extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('tarea_id');
        $this->hasColumn('tramite_id');
        $this->hasColumn('usuario_id');
        $this->hasColumn('pendiente');
        $this->hasColumn('etapa_ancestro_split_id');    //Etapa ancestro que provoco el split del flujo. (Sirve para calcular cuando se puede hacer la union del flujo)
        $this->hasColumn('vencimiento_at');
        $this->hasColumn('created_at');
        $this->hasColumn('updated_at');
        $this->hasColumn('ended_at');
    }

    function setUp() {
        parent::setUp();

        $this->actAs('Timestampable');

        $this->hasOne('Tarea', array(
            'local' => 'tarea_id',
            'foreign' => 'id'
        ));

        $this->hasOne('Tramite', array(
            'local' => 'tramite_id',
            'foreign' => 'id'
        ));

        $this->hasOne('Usuario', array(
            'local' => 'usuario_id',
            'foreign' => 'id'
        ));

        $this->hasMany('DatoSeguimiento as DatosSeguimiento', array(
            'local' => 'id',
            'foreign' => 'etapa_id'
        ));
        
        $this->hasOne('Etapa as EtapaAncestroSplit', array(
            'local'=>'etapa_ancestro_split_id',
            'foreign'=>'id'
        ));
        
        $this->hasMany('Etapa as EtapasDescendientesSplit', array(
            'local'=>'id',
            'foreign'=>'etapa_ancestro_split_id'
        ));
    }

    //Verifica si el usuario_id tiene permisos para asignarse esta etapa del tramite.
    public function canUsuarioAsignarsela($usuario_id) {
        return $this->Tarea->hasUsuario($usuario_id);
    }

    //Avanza a la siguiente etapa.
    //Si se desea especificar el usuario a cargo de la prox etapa, se debe pasar como parametros en un array: $usuarios_a_asignar[$tarea_id]=$usuario_id.
    //Este parametro solamente es valido si la asignacion de la prox tarea es manual.
    public function avanzar($usuarios_a_asignar = null) {
        Doctrine_Manager::connection()->beginTransaction();
        //Cerramos esta etapa
        $this->cerrar();

        $tp = $this->getTareasProximas();
        if ($tp->estado != 'sincontinuacion') {
            if ($tp->estado == 'completado') {
                if ($this->Tramite->getEtapasActuales()->count() == 0)
                    $this->Tramite->cerrar();
            }
            else {
                if ($tp->estado == 'pendiente') {
                    $tareas_proximas = $tp->tareas;
                    foreach ($tareas_proximas as $tarea_proxima) {
                        $usuario_asignado_id = NULL;
                        if ($tarea_proxima->asignacion == 'ciclica') {
                            $usuarios_asignables = $tarea_proxima->getUsuarios();
                            $usuario_asignado_id = $usuarios_asignables[0]->id;
                            $ultimo_usuario = $tarea_proxima->getUltimoUsuarioAsignado($this->Tramite->Proceso->id);
                            if ($ultimo_usuario) {
                                foreach ($usuarios_asignables as $key => $u) {
                                    if ($u->id == $ultimo_usuario->id) {
                                        $usuario_asignado_id = $usuarios_asignables[($key + 1) % $usuarios_asignables->count()]->id;
                                        break;
                                    }
                                }
                            }
                        } else if ($tarea_proxima->asignacion == 'manual') {
                            $usuario_asignado_id = $usuarios_a_asignar[$tarea_proxima->id];
                        } else if ($tarea_proxima->asignacion == 'usuario') {
                            $regla = new Regla($tarea_proxima->asignacion_usuario);
                            $u = $regla->evaluar($this->id);
                            $usuario_asignado_id = $u;
                        }

                        $etapa = new Etapa();
                        $etapa->tramite_id = $this->Tramite->id;
                        $etapa->tarea_id = $tarea_proxima->id;
                        $etapa->pendiente = 1;                     
                        
                        //Para mas adelante poder calcular como hacer las uniones
                        if($tp->conexion=='union')
                            $etapa->etapa_ancestro_split_id=null;
                        else if ($tp->conexion=='paralelo' || $tp->conexion=='paralelo_evaluacion')
                            $etapa->etapa_ancestro_split_id=$this->id;
                        else
                            $etapa->etapa_ancestro_split_id=$this->etapa_ancestro_split_id;
                        
                        $etapa->save();
                        $etapa->vencimiento_at=$etapa->calcularVencimiento();
                        $etapa->save();
                        
                        $etapa->asignar($usuario_asignado_id);
                        //$this->Tramite->Etapas[] = $etapa;     
                    }
                    $this->Tramite->updated_at = date("Y-m-d H:i:s");
                    $this->Tramite->save();
                }
            }
        }
        Doctrine_Manager::connection()->commit();
    }

    //Esta funcion entrega un listado de tareas a continuar y un estado que indica como se debe proceder con esta continuacion.
    //tareas:   -Arreglo de tareas para continuar
    //estado:   -sincontinuacion: No hay reglas para continuar. No se puede avanzar de etapa.
    //          -completado: Se completa el tramite luego de esta etapa.
    //          -pendiente: Hay etapas a continuacion
    //          -standby: Hay etapas a continuacion pero no se puede avanzar todavia hasta que que se completen etapas paralelas. 
    public function getTareasProximas() {
        $resultado = new stdClass();
        $resultado->tareas = null;
        $resultado->estado = 'sincontinuacion';
        $resultado->conexion=null;


        $tarea_actual = $this->Tarea;
        $conexiones = $tarea_actual->ConexionesOrigen;

        //$tareas = null;
        foreach ($conexiones as $c) {
            if ($c->evaluarRegla($this->id)) {
                //Si no hay destino es el fin del tramite.
                if (!$c->tarea_id_destino) {
                    $resultado->tareas = null;
                    $resultado->estado = 'completado';
                    $resultado->conexion=null;
                    break;
                }

                //Si no es en paralelo, retornamos con la tarea proxima.
                if ($c->tipo == 'secuencial' || $c->tipo == 'evaluacion') {
                    $resultado->tareas = array($c->TareaDestino);
                    $resultado->estado = 'pendiente';
                    $resultado->conexion=$c->tipo;
                    break;
                }
                //Si son en paralelo, vamos juntando el grupo de tareas proximas.
                else if ($c->tipo == 'paralelo' || $c->tipo == 'paralelo_evaluacion') {
                    $resultado->tareas[] = $c->TareaDestino;
                    $resultado->estado = 'pendiente';
                    $resultado->conexion=$c->tipo;
                }
                //Si es de union, chequeamos que las etapas paralelas se hayan completado antes de continuar con la proxima.
                else if ($c->tipo == 'union') {
                    if (!$this->hayEtapasParalelasPendientes()) {
                        $resultado->estado = 'pendiente';
                    } else {
                        $resultado->estado = 'standby';
                    }
                    $resultado->tareas = array($c->TareaDestino);
                    $resultado->conexion=$c->tipo;
                    break;
                }
            }
        }

        return $resultado;
    }

    public function hayEtapasParalelasPendientes() {
        if($this->etapa_ancestro_split_id){
            $n_etapas_paralelas= Doctrine_Query::create()
                    ->from('Etapa e')
                    ->where('e.etapa_ancestro_split_id = ?',$this->etapa_ancestro_split_id)
                    ->andWhere('e.pendiente = 1')
                    ->andWhere('e.id != ?',$this->id)
                    ->count();
        }else{  //Metodo antiguo (Deprecado)
            $n_etapas_paralelas = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea t, t.ConexionesOrigen c, c.TareaDestino tarea_hijo, tarea_hijo.ConexionesDestino c2, c2.TareaOrigen.Etapas etapa_this')
                ->andWhere('etapa_this.id = ?', $this->id)
                ->andWhere('c.tipo = "union" AND c2.tipo="union"')
                ->andWhere('e.tramite_id = ?',$this->tramite_id)
                ->andWhere('e.pendiente = 1')
                ->andWhere('e.id != ?',$this->id)
                ->count();
        }
        
        return $n_etapas_paralelas?true:false;
    }

    public function asignar($usuario_id) {
        if (!$this->canUsuarioAsignarsela($usuario_id))
            return;

        $this->usuario_id = $usuario_id;
        $this->save();

        if ($this->Tarea->asignacion_notificar) {
            $usuario = Doctrine::getTable('Usuario')->find($usuario_id);
            if ($usuario->email) {
                $CI = & get_instance();
                $CI->email->from('simple@chilesinpapeleo.cl', 'Simple');
                $CI->email->to($usuario->email);
                $CI->email->subject('Tramitador - Tiene una tarea pendiente');
                $CI->email->message('<p>' . $this->Tramite->Proceso->nombre . '</p><p>Tiene una tarea pendiente por realizar: ' . $this->Tarea->nombre . '</p><p>Podra realizarla en: ' . site_url('etapas/ejecutar/' . $this->id) . '</p>');
                $CI->email->send();
            }
        }


        //Ejecutamos los eventos
        $eventos=Doctrine::getTable('Evento')->findByTareaIdAndPasoId($this->Tarea->id,null);
        foreach ($eventos as $e) {
            if ($e->instante == 'antes') {
                $r = new Regla($e->regla);
                if ($r->evaluar($this->id))
                    $e->Accion->ejecutar($this);
            }
        }
    }

    public function cerrar() {
        //Si ya fue cerrada, retornamos inmediatamente.
        if (!$this->pendiente)
            return;

        if ($this->Tarea->almacenar_usuario) {
            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($this->Tarea->almacenar_usuario_variable,$this->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = $this->Tarea->almacenar_usuario_variable;
            $dato->valor = UsuarioSesion::usuario()->id;
            $dato->etapa_id = $this->id;
            $dato->save();
        }

        //Ejecutamos los eventos
        $eventos=Doctrine::getTable('Evento')->findByTareaIdAndPasoId($this->Tarea->id,null);
        foreach ($eventos as $e) {
            if ($e->instante == 'despues') {
                $r = new Regla($e->regla);
                if ($r->evaluar($this->id))
                    $e->Accion->ejecutar($this);
            }
        }

        //Cerramos la etapa
        $this->pendiente = 0;
        $this->ended_at = date('Y-m-d H:i:s');
        $this->save();
    }

    //Retorna el paso correspondiente a la secuencia, dado los datos ingresados en el tramite hasta el momento.
    //Es decir, tomando en cuenta las condiciones para que se ejecute cada paso.
    public function getPasoEjecutable($secuencia) {
        $pasos = $this->getPasosEjecutables($this->tramite_id);

        if (isset($pasos[$secuencia]))
            return $pasos[$secuencia];

        return null;
    }

    //Retorna un arreglo con todos los pasos que son ejecutables dado los datos ingresados en el tramite hasta el momento.
    //Es decir, tomando en cuenta las condiciones para que se ejecute cada paso.
    public function getPasosEjecutables() {
        $pasos = array();
        foreach ($this->Tarea->Pasos as $p) {
            $r = new Regla($p->regla);
            if ($r->evaluar($this->id))
                $pasos[] = $p;
        }

        return $pasos;
    }
    
    //Calcula la fecha en que deberia vencer esta etapa tomando en cuenta la configuracion de la tarea.
    public function calcularVencimiento(){
        if(!$this->Tarea->vencimiento)
            return NULL;
        
        $fecha=NULL;
        if($this->Tarea->vencimiento_unidad=='D')
            if($this->Tarea->vencimiento_habiles){
                $fecha=add_working_days($this->created_at,$this->Tarea->vencimiento_valor);
            }else{
                $temp = new DateTime($this->created_at);
                $fecha= $temp->add(new DateInterval('P' . $this->Tarea->vencimiento_valor . 'D'))->format('Y-m-d');
            }
        else if($this->Tarea->vencimiento_unidad=='W'){
            $temp = new DateTime($this->created_at);
            $fecha= $temp->add(new DateInterval('P' . $this->Tarea->vencimiento_valor . 'W'))->format('Y-m-d');
        }else if($this->Tarea->vencimiento_unidad=='M'){
            $temp = new DateTime($this->created_at);
            $fecha= $temp->add(new DateInterval('P' . $this->Tarea->vencimiento_valor . 'M'))->format('Y-m-d');
        }
        
        return $fecha;
    }

    /*
    public function getFechaVencimiento() {
        if (!($this->Tarea->vencimiento && $this->Tarea->vencimiento_valor))
            return NULL;

        //return strtotime($this->Tarea->vencimiento_valor.' '.$this->Tarea->vencimiento_unidad, mysql_to_unix($this->created_at));
        $creacion = new DateTime($this->created_at);
        //$creacion->setTime(0, 0, 0);
        return $creacion->add(new DateInterval('P' . $this->Tarea->vencimiento_valor . $this->Tarea->vencimiento_unidad));
    }
     * 
     */

    public function getFechaVencimientoAsString() {
        $now = new DateTime();
        $now->setTime(0,0,0);

        $interval = $now->diff(new DateTime($this->vencimiento_at));
        
        if($interval->invert)
            return 'vencida';
        else
            return 'vence en '. (1+$interval->d) . ' días';
    }
     

    public function vencida() {
        if (!$this->vencimiento_at)
            return FALSE;

        $vencimiento = new DateTime($this->vencimiento_at);
        $now = new DateTime();
        $now->setTime(0,0,0);

        return $vencimiento < $now;
    }

    public function iniciarPaso(Paso $paso) {
        //Ejecutamos los eventos iniciales
        foreach ($paso->Eventos as $e) {
            if ($e->instante == 'antes') {
                $r = new Regla($e->regla);
                if ($r->evaluar($this->id))
                    $e->Accion->ejecutar($this);
            }
        }
    }

    public function finalizarPaso(Paso $paso) {
        //Ejecutamos los eventos finales
        foreach ($paso->Eventos as $e) {
            if ($e->instante == 'despues') {
                $r = new Regla($e->regla);
                if ($r->evaluar($this->id))
                    $e->Accion->ejecutar($this);
            }
        }
    }

}
