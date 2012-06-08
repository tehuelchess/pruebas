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
    

    
}
