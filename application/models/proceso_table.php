<?php

class ProcesoTable extends Doctrine_Table {

    public function findProcesosDisponiblesParaIniciar($usuario_id){
        $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
        
        return Doctrine_Query::create()
                ->from('Proceso p, p.Tareas t, t.GruposUsuarios g, g.Usuarios u')
                ->where('t.inicial = 1')
                ->andWhere('(t.acceso_modo="grupos_usuarios" AND u.id = ?) OR (t.acceso_modo = "registrados" AND 1 = ?) OR (t.acceso_modo="publico")',array($usuario->id,$usuario->registrado))
                ->orderBy('p.id desc')
                ->execute();
    }
    
}
