<?php

class DatoSeguimientoTable extends Doctrine_Table{
      
    //Busca el valor del dato hasta la etapa $etapa_id
    public function findByNombreHastaEtapa($nombre,$etapa_id){
        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
        
        return Doctrine_Query::create()
                ->from('DatoSeguimiento d, d.Etapa e, e.Tramite t')
                ->where('d.nombre = ?',$nombre)
                ->andWhere('t.id = ?',$etapa->tramite_id)
                ->andWhere('e.id <= ?',$etapa->id)
                ->orderBy('d.id DESC')
                ->fetchOne();        
    }
    
    //Busca todos los dato hasta la ultima etapa del $tramite_id
    public function findByTramite($tramite_id){
        
        return Doctrine_Query::create()
                ->from('DatoSeguimiento d, d.Etapa e, e.Tramite t')
                ->where('t.id = ?',$tramite_id)
                ->having('d.id = MAX(d.id)')
                ->groupBy('d.nombre')
                ->execute();
    }
    
    //Devuelve un arreglo con los valores del dato recopilados durante todo el proceso
    public function findGlobalByNombreAndProceso($nombre,$proceso_id){        
        $datos= Doctrine_Query::create()
                ->from('DatoSeguimiento d, d.Etapa.Tramite t,t.Proceso p')
                ->where('d.nombre = ?',$nombre)
                ->andWhere('p.id = ?',$proceso_id)
                ->having('d.id = MAX(d.id)')
                ->groupBy('t.id')
                ->execute();
        
        $result=array();
        foreach($datos as $d)
            $result[]=$d->valor;
        
        return $result;
    }
    
    
}