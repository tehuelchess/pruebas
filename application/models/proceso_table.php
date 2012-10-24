<?php

class ProcesoTable extends Doctrine_Table {

    public function findProcesosDisponiblesParaIniciar($usuario_id,$cuenta_nombre=null){
        $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
        
        $query=Doctrine_Query::create()
                ->from('Proceso p, p.Cuenta c, p.Tareas t, t.GruposUsuarios g, g.Usuarios u')
                ->where('t.inicial = 1')
                ->andWhere('(t.acceso_modo="grupos_usuarios" AND u.id = ?) OR (t.acceso_modo = "registrados" AND 1 = ?) OR (t.acceso_modo="publico")',array($usuario->id,$usuario->registrado))
                ->orderBy('p.id desc');
        
        if($cuenta_nombre)
            $query->andWhere('c.nombre = ?',$cuenta_nombre);
        
        return $query->execute();
    }
    
}
