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
        $grupos = Doctrine::getTable('Usuario')->find($usuario_id)->GruposUsuarios;
        $grupos_array = array();
        foreach ($grupos as $g)
            $grupos_array[] = $g->id;

        $tramite = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, tar.GruposUsuarios g')
                ->where('e.usuario_id IS NULL')
                ->andWhere('e.id = ?', $this->id)
                ->andWhereIn('g.id', $grupos_array)
                ->fetchOne();

        if ($tramite)
            return TRUE;

        return FALSE;
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
                    if ($tarea_proxima->hasUsuario($usuarios_a_asignar[$tarea_proxima->id]))
                        $usuario_asignado_id = $usuarios_a_asignar[$tarea_proxima->id];
                } else if ($tarea_proxima->asignacion == 'usuario') {
                    $regla = new Regla($tarea_proxima->asignacion_usuario);
                    $u = $regla->evaluar($this->Tramite->id);
                    if ($tarea_proxima->hasUsuario($u))
                        $usuario_asignado_id = $u;
                }

                $etapa = new Etapa();
                $etapa->tramite_id = $this->Tramite->id;
                $etapa->usuario_id = $usuario_asignado_id;
                $etapa->tarea_id = $tarea_proxima->id;
                $etapa->pendiente = 1;
                $this->Tramite->Etapas[] = $etapa;     
            }
            $this->Tramite->save();
        }

        //Cerramos esta etapa
        $this->cerrar();
        
        if($this->Tramite->getEtapasActuales()->count()==0)
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
            if ($c->evaluarRegla($this->Tramite->id)){
                $tareas[] = $c->TareaDestino;
                if($c->tipo=='secuencial' || $c->tipo=='evaluacion')
                    break;
            }
        }

        return $tareas;
    }
    
    public function cerrar(){
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
    }

}
