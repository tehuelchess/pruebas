<?php

class DatoSeguimientoTable extends Doctrine_Table{
      
    //Busca el valor del dato hasta la etapa $etapa_id
    public function findByNombreHastaEtapa($nombre,$etapa_id){
        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
        
        return Doctrine_Query::create()
                ->from('DatoSeguimiento d, d.Etapa.Tramite t, t.Etapas e')
                ->where('d.nombre = ?',$nombre)
                ->andWhere('t.id = ?',$etapa->tramite_id)
                ->andWhere('e.id <= ?',$etapa->id)
                ->orderBy('d.id DESC')
                ->fetchOne();
    }
    
}