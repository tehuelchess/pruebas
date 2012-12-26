<?php

class TramiteTable extends Doctrine_Table {
    

    //busca los tramites donde el $usuario_id ha participado
    public function findParticipados($usuario_id,$cuenta=null){        
        $query=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso.Cuenta c, t.Etapas e, e.Usuario u')
                ->where('u.id = ?',$usuario_id)
                ->andWhere('e.pendiente=0')
                ->orderBy('t.updated_at desc');
        
        if($cuenta)
            $query->andWhere('c.nombre = ?',$cuenta->nombre);
        
        return $query->execute();
    }
    
    //Limpia los tramites que han sido iniciados por usuarios no registrados, y que llevan mas de 1 dia sin modificarse, y sin avanzar de etapa.
    public function cleanIniciadosPorNoRegistrados(){
        $noregistrados=Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('u.registrado = 0 AND DATEDIFF(NOW(),t.updated_at) >= 1')
                ->groupBy('t.id')
                ->having('COUNT(e.id) = 1')
                ->execute();
        
        $noregistrados->delete();
    }
    
}
