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
    public function avanzar($usuarios_a_asignar) {
        Doctrine_Manager::connection()->beginTransaction();
        $tp = $this->getTareasProximas();

        if ($tp->estado != 'sincontinuacion') {
            //Cerramos esta etapa
            $this->cerrar();

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
                                        $usuario_asignado_id = $usuarios_asignables[$key + 1 % $usuarios_asignables->count()]->id;
                                        break;
                                    }
                                }
                            }
                        } else if ($tarea_proxima->asignacion == 'manual') {
                            $usuario_asignado_id = $usuarios_a_asignar[$tarea_proxima->id];
                        } else if ($tarea_proxima->asignacion == 'usuario') {
                            $regla = new Regla($tarea_proxima->asignacion_usuario);
                            $u = $regla->evaluar($this->Tramite->id);
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
        $resultado->tareas = null;
        $resultado->estado = 'sincontinuacion';


        $tarea_actual = $this->Tarea;
        $conexiones = $tarea_actual->ConexionesOrigen;

        //$tareas = null;
        foreach ($conexiones as $c) {
            if ($c->evaluarRegla($this->Tramite->id)) {
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
        $netapas_paralelas_pendientes = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea t, t.ConexionesDestino c, c.TareaOrigen tarea_padre, tarea_padre.ConexionesOrigen c2, c2.TareaDestino.Etapas etapa_this')
                ->where('c.tipo = "paralelo" OR c.tipo = "paralelo_evaluacion"') //Las conexiones hacia la etapa sean paralelas
                ->andWhere('c2.tipo = "paralelo" OR c2.tipo = "paralelo_evaluacion"') //Las conexiones hacia la etapa sean paralelas
                ->andWhere('e.pendiente = 1')   //Esten pendientes
                ->andWhere('e.tramite_id = ?', $this->tramite_id)    //Pertenezcan a este tramite
                ->andWhere('e.id != ?', $this->id)   //No sean esta misma etapa. Busco a las etapas hermanas.
                ->andWhere('etapa_this.id = ?', $this->id)
                ->count();

        return $netapas_paralelas_pendientes ? true : false;
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
                $CI->email->from($CI->config->item('email_from'), 'Tramitador');
                $CI->email->to($usuario->email);
                $CI->email->subject('Tramitador - Tiene una tarea pendiente');
                $CI->email->message('Tiene una tarea pendiente por realizar. Podra realizarla en: ' . site_url());
                $CI->email->send();
            }
        }


        //Ejecutamos los eventos
        foreach ($this->Tarea->Eventos as $e){
            if ($e->instante == 'antes'){
                $r=new Regla($e->regla);
                if($r->evaluar($this->tramite_id))
                    $e->Accion->ejecutar();
            }
        }
    }

    public function cerrar() {
        //Si ya fue cerrada, retornamos inmediatamente.
        if (!$this->pendiente)
            return;

        if ($this->Tarea->almacenar_usuario) {
            $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($this->Tramite->id, $this->Tarea->almacenar_usuario_variable);
            if (!$dato)
                $dato = new Dato();
            $dato->nombre = $this->Tarea->almacenar_usuario_variable;
            $dato->valor = UsuarioSesion::usuario()->id;
            $dato->tramite_id = $this->Tramite->id;
            $dato->save();
        }

        //Le generamos los datos para el seguimiento
        foreach ($this->Tramite->Datos as $d) {
            //$dato = Doctrine::getTable('DatoSeguimiento')->findOneByEtapaIdAndNombre($this->id, $nombre);
            //if (!$dato)
            $dato = new DatoSeguimiento();
            $dato->nombre = $d->nombre;
            $dato->valor = $d->valor;
            $dato->etapa_id = $this->id;
            $this->DatosSeguimiento[] = $dato;
        }

        //Cerramos la etapa
        $this->pendiente = 0;
        $this->ended_at = date('Y-m-d H:i:s');
        $this->save();

        //Ejecutamos los eventos
        foreach ($this->Tarea->Eventos as $e){
            if ($e->instante == 'despues'){
                $r=new Regla($e->regla);
                if($r->evaluar($this->tramite_id))
                    $e->Accion->ejecutar();
            }
        }
    }
    
    //Retorna el paso correspondiente a la secuencia, dado los datos ingresados en el tramite hasta el momento.
    //Es decir, tomando en cuenta las condiciones para que se ejecute cada paso.
    public function getPasoEjecutable($secuencia){
        $pasos=$this->getPasosEjecutables($this->tramite_id);
        
        if(isset($pasos[$secuencia]))
            return $pasos[$secuencia];
        
        return null;
    }
    
    //Retorna un arreglo con todos los pasos que son ejecutables dado los datos ingresados en el tramite hasta el momento.
    //Es decir, tomando en cuenta las condiciones para que se ejecute cada paso.
    public function getPasosEjecutables(){
        $pasos=array();
        foreach($this->Tarea->Pasos as $p){
            $r=new Regla($p->regla);
            if($r->evaluar($this->tramite_id))
                $pasos[]=$p;
        }
        
        return $pasos;
    }

}
