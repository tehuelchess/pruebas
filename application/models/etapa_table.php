<?php

class EtapaTable extends Doctrine_Table {
    
    //busca las etapas que no han sido asignadas y que usuario_id se podria asignar
    public function findSinAsignar($usuario_id, $cuenta='localhost'){
        $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
        
        $query=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, tar.GruposUsuarios g, e.Tramite.Proceso.Cuenta c')
                //Si la etapa no se encuentra asignada
                ->where('e.usuario_id IS NULL')
                //Si el usuario tiene permisos de acceso
                ->andWhere('(tar.acceso_modo="grupos_usuarios" AND g.id IN (SELECT gru.id FROM GrupoUsuarios gru, gru.Usuarios usr WHERE usr.id = ?)) OR (tar.acceso_modo = "registrados" AND 1 = ?) OR (tar.acceso_modo = "claveunica" AND 1 = ?) OR (tar.acceso_modo="publico")',array($usuario->id,$usuario->registrado,$usuario->open_id))
                ->orderBy('e.updated_at desc');
        
        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);
        
        return $query->execute();
    }
    
    //busca las etapas donde esta pendiente una accion de $usuario_id
    public function findPendientes($usuario_id,$cuenta='localhost'){        
        $query=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Usuario u, e.Tramite t, t.Etapas hermanas, t.Proceso.Cuenta c')
                ->select('e.*,COUNT(hermanas.id) as netapas')
                ->groupBy('e.id')
                //Si la etapa se encuentra pendiente y asignada al usuario
                ->where('e.pendiente = 1 and u.id = ?',$usuario_id)
                //Si la tarea se encuentra activa
                ->andWhere('1!=(tar.activacion="no" OR ( tar.activacion="entre_fechas" AND ((tar.activacion_inicio IS NOT NULL AND tar.activacion_inicio>NOW()) OR (tar.activacion_fin IS NOT NULL AND NOW()>tar.activacion_fin) )))')
                ->orderBy('e.updated_at desc');
        
        if($cuenta!='localhost')
            $query->andWhere('c.nombre = ?',$cuenta->nombre);
        
        return $query->execute();
    }
    
}
