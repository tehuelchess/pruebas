<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Portada extends MY_Controller {

    public function __construct() {
        parent::__construct();

    }

    public function index() {
        $pendientes=Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id);
        
        if(UsuarioSesion::usuario()->registrado && $pendientes->count()>0)
            redirect('etapas/inbox');
        else
            redirect('tramites/disponibles');
    }
    
    public function test(){
        $etapas = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea t')
                ->where('e.pendiente = 1 AND t.vencimiento_notificar = 1')
                ->andWhere('DATEDIFF(e.vencimiento_at,NOW()) <= t.vencimiento_notificar_dias')
                ->execute();
        
        foreach($etapas as $e){
            print_r($e->toArray());
            $dias_por_vencer=ceil((strtotime($e->vencimiento_at)-time())/60/60/24);
            echo 'La etapa "' . $e->Tarea->nombre . '" se encuentra '.($dias_por_vencer>0?'a '.$dias_por_vencer.' días por vencer':'vencida');
        }
    }

}