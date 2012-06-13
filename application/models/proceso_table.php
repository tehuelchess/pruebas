<?php

class ProcesoTable extends Doctrine_Table {

    public function findProcesosDisponiblesParaIniciar($usuario_id){
        $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
        
        return Doctrine_Query::create()
                ->from('Proceso p, p.Tareas t, t.GruposUsuarios g, g.Usuarios u')
                ->where('t.inicial = 1')
                ->andWhere('(g.tipo="manual" AND u.id = ?) OR (g.tipo = "registrados" AND 1 = ?) OR (g.tipo="todos")',array($usuario->id,$usuario->registrado))
                ->orderBy('p.id desc')
                ->execute();
    }
    
}
