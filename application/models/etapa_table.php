<?php

class EtapaTable extends Doctrine_Table {
    
    //busca las etapas que no han sido asignadas y que usuario_id se podria asignar
    public function findSinAsignar($usuario_id){
        $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
        
        return Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, tar.GruposUsuarios g')
                ->where('e.usuario_id IS NULL')
                ->andWhere('(tar.acceso_modo="grupos_usuarios" AND g.id IN (SELECT gru.id FROM GrupoUsuarios gru, gru.Usuarios usr WHERE usr.id = ?)) OR (tar.acceso_modo = "registrados" AND 1 = ?) OR (tar.acceso_modo="publico")',array($usuario->id,$usuario->registrado))
                ->orderBy('e.updated_at desc')
                ->execute();
    }
    
    //busca las etapqas donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id){        
        return Doctrine_Query::create()
                ->from('Etapa e, e.Usuario u, e.Tramite.Etapas hermanas')
                ->select('e.*,COUNT(hermanas.id) as netapas')
                ->groupBy('e.id')
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                ->orderBy('e.updated_at desc')
                ->execute();
    }
    
}
