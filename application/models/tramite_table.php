<?php

class TramiteTable extends Doctrine_Table {

    public function findSinAsignar($usuario_id){
        $grupos=Doctrine::getTable('Usuario')->find($usuario_id)->GruposUsuarios;
        $grupos_array=array();
        foreach($grupos as $g)
            $grupos_array[]=$g->id;
        
        return Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Tarea tar, tar.GruposUsuarios g')
                ->where('e.usuario_id IS NULL')
                ->andWhereIn('g.id',$grupos_array)
                ->execute();
    }
    

    //busca los tramites donde el $usuario_id ha participado
    public function findParticipados($usuario_id){        
        return Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('u.id = ?',$usuario_id)
                ->execute();
    }
    

    //busca los tramites donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id){        
        return Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                ->execute();
    }
    
    //Hace el conteo de tramites que va en cada seccion del menu.
    public function countBySeccion($usuario_id){
        $count->inbox=$this->findPendientes($usuario_id)->count();
        $count->sinasignar=$this->findSinAsignar($usuario_id)->count();
        $count->participados=$this->findParticipados($usuario_id)->count();
                
        return $count;
    }
    
}
