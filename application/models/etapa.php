<?php

class Etapa extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('tarea_id');
        $this->hasColumn('tramite_id');
        $this->hasColumn('usuario_id');
        $this->hasColumn('pendiente');
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


        $tarea_actual = $this->Tarea;
        $conexiones = $tarea_actual->ConexionesOrigen;

        //$tareas = null;
        foreach ($conexiones as $c) {
            if ($c->evaluarRegla($this->id)) {
                //Si no hay destino es el fin del tramite.
                if (!$c->tarea_id_destino) {
                    $resultado->tareas = null;
                    $resultado->estado = 'completado';
                    break;
                }

                //Si no es en paralelo, retornamos con la tarea proxima.
                if ($c->tipo == 'secuencial' || $c->tipo == 'evaluacion') {
                    $resultado->tareas = array($c->TareaDestino);
                    $resultado->estado = 'pendiente';
                    break;
                }
                //Si son en paralelo, vamos juntando el grupo de tareas proximas.
                else if ($c->tipo == 'paralelo' || $c->tipo == 'paralelo_evaluacion') {
                    $resultado->tareas[] = $c->TareaDestino;
                    $resultado->estado = 'pendiente';
                }
                //Si es de union, chequeamos que las etapas paralelas se hayan completado antes de continuar con la proxima.
                else if ($c->tipo == 'union') {
                    if (!$this->hayEtapasParalelasPendientes()) {
                        $resultado->estado = 'pendiente';
                    } else {
                        $resultado->estado = 'standby';
                    }
                    $resultado->tareas = array($c->TareaDestino);
                    break;
                }
            }
        }

        return $resultado;
    }

    public function hayEtapasParalelasPendientes() {
        /*
        $netapas_paralelas_pendientes = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea t, t.ConexionesDestino c, c.TareaOrigen tarea_padre, tarea_padre.ConexionesOrigen c2, c2.TareaDestino.Etapas etapa_this')
                ->where('c.tipo = "paralelo" OR c.tipo = "paralelo_evaluacion"') //Las conexiones hacia la etapa sean paralelas
                ->andWhere('c2.tipo = "paralelo" OR c2.tipo = "paralelo_evaluacion"') //Las conexiones hacia la etapa sean paralelas
                ->andWhere('e.pendiente = 1')   //Esten pendientes
                ->andWhere('e.tramite_id = ?', $this->tramite_id)    //Pertenezcan a este tramite
                ->andWhere('e.id != ?', $this->id)   //No sean esta misma etapa. Busco a las etapas hermanas.
                ->andWhere('etapa_this.id = ?', $this->id)
                ->count();
         * 
         */

        $tareas_paralelas = Doctrine_Query::create()
                ->from('Tarea t, t.ConexionesOrigen c, c.TareaDestino tarea_hijo, tarea_hijo.ConexionesDestino c2, c2.TareaOrigen.Etapas etapa_this')
                ->andWhere('etapa_this.id = ?', $this->id)
                ->andWhere('c.tipo = "union" AND c2.tipo="union"')
                ->execute();
        
        foreach ($tareas_paralelas as $t){
            if($t!=$this->Tarea){           //Si no es mi misma tarea
                if(!$t->Etapas->count()){   //Si no se han realizado las etapas de las siguientes tareas, es que hay pendientes
                    return true;
                }
                foreach($t->Etapas as $e){
                    if($e->pendiente){
                        return true;        //Si hay etapa pendiente retorno true
                    }
                }
            }
        }
        
        return false;
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
        foreach ($this->Tarea->Eventos as $e) {
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
        foreach ($this->Tarea->Eventos as $e) {
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

    public function getFechaVencimiento() {
        if (!($this->Tarea->vencimiento && $this->Tarea->vencimiento_valor))
            return NULL;

        //return strtotime($this->Tarea->vencimiento_valor.' '.$this->Tarea->vencimiento_unidad, mysql_to_unix($this->created_at));
        $creacion = new DateTime($this->created_at);
        //$creacion->setTime(0, 0, 0);
        return $creacion->add(new DateInterval('P' . $this->Tarea->vencimiento_valor . $this->Tarea->vencimiento_unidad));
    }

    public function getFechaVencimientoAsString() {
        //return floor(($this->getFechaVencimiento()-now())/60/60/24).' días';
        $now = new DateTime();

        $interval = $now->diff($this->getFechaVencimiento());
        return $interval->d . ' días';
    }

    public function vencida() {
        if (!$this->getFechaVencimiento())
            return FALSE;

        $vencimiento = $this->getFechaVencimiento();
        $now = new DateTime();

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
