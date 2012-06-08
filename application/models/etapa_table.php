<?php

class EtapaTable extends Doctrine_Table {
    
    //busca las etapas que no han sido asignadas y que usuario_id se podria asignar
    public function findSinAsignar($usuario_id){
        return Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, tar.GruposUsuarios g')
                ->where('e.usuario_id IS NULL')
                ->andWhere('g.id IN (SELECT gru.id FROM GrupoUsuarios gru, gru.Usuarios usr WHERE usr.id = ?) OR g.registrados = 1',$usuario_id)
                ->orderBy('e.updated_at desc')
                ->execute();
    }
    
    //busca las etapqas donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id){        
        return Doctrine_Query::create()
                ->from('Etapa e, e.Usuario u')
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                ->orderBy('e.updated_at desc')
                ->execute();
    }
    
}
