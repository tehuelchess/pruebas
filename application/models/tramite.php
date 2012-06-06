<?php

class Tramite extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('pendiente');
        $this->hasColumn('proceso_id');
        $this->hasColumn('created_at');
        $this->hasColumn('updated_at');
        $this->hasColumn('ended_at');
    }

    function setUp() {
        parent::setUp();

        $this->actAs('Timestampable');

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Etapa as Etapas', array(
            'local' => 'id',
            'foreign' => 'tramite_id'
        ));

        $this->hasMany('Dato as Datos', array(
            'local' => 'id',
            'foreign' => 'tramite_id'
        ));
    }

    public function iniciar($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        $this->proceso_id = $proceso->id;
        $this->pendiente = 1;

        $etapa = new Etapa();
        $etapa->tarea_id = $proceso->getTareaInicial()->id;
        $etapa->usuario_id = UsuarioSesion::usuario()->id;
        $etapa->pendiente = 1;

        $this->Etapas[] = $etapa;

        $this->save();
    }

    //Avanza el tramite a la siguiente etapa.
    //Si se desea especificar el usuario a cargo de la prox etapa, se debe pasar como parametro $usuario_a_asignar_id.
    //Este parametro solamente es valido si la asignacion de la prox tarea es manual.
    public function avanzarEtapa($usuario_a_asignar_id = NULL) {
        Doctrine_Manager::connection()->beginTransaction();
        //Cerramos la etapa antigua
        $etapa_antigua = $this->getEtapaActual();
        if ($etapa_antigua->Tarea->almacenar_usuario) {
            $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($this->id, $etapa_antigua->Tarea->almacenar_usuario_variable);
            if (!$dato)
                $dato = new Dato();
            $dato->nombre = $etapa_antigua->Tarea->almacenar_usuario_variable;
            $dato->valor = UsuarioSesion::usuario()->id;
            $dato->tramite_id = $this->id;
            $dato->save();
        }


        //Generamos la etapa nueva
        $tarea_proxima = $this->getTareaProxima();
        $usuario_asignado_id = NULL;
        if ($tarea_proxima) {
            if ($tarea_proxima->asignacion == 'ciclica') {
                $usuarios_asignables = $tarea_proxima->getUsuarios();
                $usuario_asignado_id = $usuarios_asignables[0]->id;
                $ultimo_usuario = $tarea_proxima->getUltimoUsuarioAsignado($this->Proceso->id)->id;
                if ($ultimo_usuario) {
                    foreach ($usuarios_asignables as $key => $u) {
                        if ($u->id == $ultimo_usuario->id) {
                            $usuario_asignado_id = $usuarios_asignables[$key + 1 % $usuarios_asignables->count()]->id;
                            break;
                        }
                    }
                }
            } else if ($tarea_proxima->asignacion == 'manual') {
                if ($tarea_proxima->hasUsuario($usuario_a_asignar_id))
                    $usuario_asignado_id = $usuario_a_asignar_id;
            } else if ($tarea_proxima->asignacion == 'usuario'){
                $regla=new Regla($tarea_proxima->asignacion_usuario);
                $u=$regla->evaluar($this->id);
                if($tarea_proxima->hasUsuario($u))
                    $usuario_asignado_id=$u;
            }


            $etapa = new Etapa();
            $etapa->tramite_id = $this->id;
            $etapa->usuario_id = $usuario_asignado_id;
            $etapa->tarea_id = $tarea_proxima->id;
            $etapa->pendiente = 1;
            $this->Etapas[] = $etapa;
            $this->save();
        } else {
            $this->pendiente = 0;
            $this->ended_at = date('Y-m-d H:i:s');
            $this->save();
        }

        $etapa_antigua->pendiente = 0;
        $etapa_antigua->ended_at = date('Y-m-d H:i:s');
        $etapa_antigua->save();

        Doctrine_Manager::connection()->commit();
    }

    public function getEtapaActual() {
        return Doctrine_Query::create()
                        ->from('Etapa e, e.Tramite t')
                        ->where('t.id = ? AND e.pendiente=1', $this->id)
                        ->fetchOne();
    }

    public function getTareaProxima() {
        $tarea_actual = $this->getEtapaActual()->Tarea;

        if ($tarea_actual->final)
            return NULL;

        $conexiones = $tarea_actual->ConexionesOrigen;

        foreach ($conexiones as $c) {
            if ($c->evaluarRegla($this->id))
                return $c->TareaDestino;
        }

        return NULL;
    }

    //Chequea si el usuario_id ha tenido participacion en este tramite.
    public function usuarioHaParticipado($usuario_id) {
        $tramite = Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('t.id = ? AND u.id = ?', array($this->id, $usuario_id))
                ->fetchOne();

        if ($tramite)
            return TRUE;

        return FALSE;
    }

}
