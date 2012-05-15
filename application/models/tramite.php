<?php

class Tramite extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('pendiente');
        $this->hasColumn('proceso_id');
        $this->hasColumn('created_at');
        $this->hasColumn('updated_at');
        $this->hasColumn('ended_at');
    }

    function setUp() {
        parent::setUp();
        
        $this->actAs('Timestampable');
        
        $this->hasOne('Proceso',array(
            'local'=>'proceso_id',
            'foreign'=>'id'
        ));
        
        $this->hasMany('Etapa as Etapas',array(
            'local'=>'id',
            'foreign'=>'tramite_id'
        ));
        
        $this->hasMany('Dato as Datos',array(
            'local'=>'id',
            'foreign'=>'tramite_id'
        ));
    }
    
    public function iniciar($proceso_id){
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        
        $this->proceso_id=$proceso->id;
        $this->pendiente=1;  
        
        $etapa=new Etapa();
        $etapa->tarea_id=$proceso->getTareaInicial()->id;
        $etapa->usuario_id=UsuarioSesion::usuario()->id;
        $etapa->pendiente=1;
        
        $this->Etapas[]=$etapa;
        
        $this->save();
    }
    
    public function avanzarEtapa(){
        Doctrine_Manager::connection()->beginTransaction();
        
        $etapa_antigua=$this->getEtapaActual();
        $tarea_proxima=$this->getTareaProxima();
        
        if($tarea_proxima){
            $etapa=new Etapa();
            $etapa->tramite_id=$this->id;
            $etapa->usuario_id=NULL;
            $etapa->tarea_id=$tarea_proxima->id;
            $etapa->pendiente=1;
            $this->Etapas[]=$etapa;
            $this->save();         
        } else{
            $this->pendiente=0;
            $this->ended_at=date( 'Y-m-d H:i:s' );
            $this->save();
        }
        
        $etapa_antigua->pendiente=0;
        $etapa_antigua->ended_at=date( 'Y-m-d H:i:s' );
        $etapa_antigua->save();
        
        Doctrine_Manager::connection()->commit();
    }
    
    
    public function getEtapaActual(){
        return Doctrine_Query::create()
                ->from('Etapa e, e.Tramite t')
                ->where('t.id = ? AND e.pendiente=1',$this->id)
                ->fetchOne();
    }
    
    public function getTareaProxima(){
        $tarea_actual=$this->getEtapaActual()->Tarea;
        
        if($tarea_actual->final)
            return NULL;
        
        $conexiones=$tarea_actual->ConexionesOrigen;
        $tarea_proxima=$conexiones[0]->TareaDestino;
        
        return $tarea_proxima;
    }

}
