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

        //Generamos la etapa nueva
        $tareas_proximas = $this->getTareasProximas();
        if ($tareas_proximas) {
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

        //Cerramos esta etapa
        $this->cerrar();

        if ($this->Tramite->getEtapasActuales()->count() == 0)
            $this->Tramite->cerrar();

        Doctrine_Manager::connection()->commit();
    }

    public function getTareasProximas() {
        $tarea_actual = $this->Tarea;

        if ($tarea_actual->final)
            return null;

        $conexiones = $tarea_actual->ConexionesOrigen;

        $tareas = null;
        foreach ($conexiones as $c) {
            if ($c->evaluarRegla($this->Tramite->id)) {
                if ($c->tipo == 'secuencial' || $c->tipo == 'evaluacion') {
                    $tareas[] = $c->TareaDestino;
                    break;
                } else if ($c->tipo == 'paralelo' || $c->tipo == 'paralelo_evaluacion') {
                    $tareas[] = $c->TareaDestino;
                } else if ($c->tipo == 'union') {
                    if (!$this->hayEtapasParalelasPendientes()) {
                        $tareas[] = $c->TareaDestino;
                        break;
                    }
                }
            }
        }

        return $tareas;
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
        foreach ($this->Tarea->Eventos as $e)
            if ($e->instante == 'antes')
                $e->Accion->ejecutar();
    }

    public function cerrar() {
        if ($this->Tarea->almacenar_usuario) {
            $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($this->Tramite->id, $this->Tarea->almacenar_usuario_variable);
            if (!$dato)
                $dato = new Dato();
            $dato->nombre = $this->Tarea->almacenar_usuario_variable;
            $dato->valor = UsuarioSesion::usuario()->id;
            $dato->tramite_id = $this->Tramite->id;
            $dato->save();
        }

        $this->pendiente = 0;
        $this->ended_at = date('Y-m-d H:i:s');
        $this->save();

        //Ejecutamos los eventos
        foreach ($this->Tarea->Eventos as $e)
            if ($e->instante == 'despues')
                $e->Accion->ejecutar();
    }

}
