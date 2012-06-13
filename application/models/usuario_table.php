<?php

class UsuarioTable extends Doctrine_Table {
    
    //Elimina los registros no registrados con mas de 1 dia de antiguedad y que no hayan iniciado etapas
    public function cleanNoRegistrados(){
        $noregistrados=Doctrine_Query::create()
                ->from('Usuario u, u.Etapas e')
                ->where('u.registrado = 0 AND DATEDIFF(NOW(),u.updated_at) >= 1')
                ->groupBy('u.id')
                ->having('COUNT(e.id) = 0')
                ->execute();
        
        $noregistrados->delete();
    }
    
}
