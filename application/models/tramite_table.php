<?php

class TramiteTable extends Doctrine_Table {
    

    //busca los tramites donde el $usuario_id ha participado
    public function findParticipados($usuario_id){        
        return Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('u.id = ?',$usuario_id)
                ->orderBy('t.updated_at desc')
                ->execute();
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
