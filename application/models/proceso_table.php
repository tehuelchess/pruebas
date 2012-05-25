<?php

class ProcesoTable extends Doctrine_Table {

    public function findProcesosDisponiblesParaIniciar($usuario_id){        
        return Doctrine_Query::create()
                ->from('Proceso p, p.Tareas t, t.GruposUsuarios.Usuarios u')
                ->where('t.inicial = 1 and u.id = ?',$usuario_id)
                ->orderBy('p.id desc')
                ->execute();
    }
    
}
