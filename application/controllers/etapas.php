<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Etapas extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioSesion::force_login();
    }
    
    public function inbox() {
        $data['etapas']=Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'etapas/inbox';
        $data['title'] = 'Bandeja de Entrada';
        $this->load->view('template', $data);
    }
    
    public function sinasignar() {
        $data['etapas']=Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'etapas/sinasignar';
        $data['title'] = 'Sin Asignar';
        $this->load->view('template', $data);
    }

    public function ejecutar($etapa_id, $secuencia = 0) {
        $iframe=$this->input->get('iframe');
        
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }
        
        $paso = $etapa->getPasoEjecutable($secuencia);
        if(!$paso){
            $qs=$this->input->server('QUERY_STRING');
            redirect('etapas/ejecutar_fin/'.$etapa->id.($qs?'?'.$qs:''));
        }

        $data['secuencia']=$secuencia;
        $data['etapa'] = $etapa;
        $data['paso'] = $paso;
        $data['qs']=$this->input->server('QUERY_STRING');

        $data['content'] = 'etapas/ejecutar';
        $data['title'] = $etapa->Tarea->nombre;
        $template=$this->input->get('iframe')?'template_iframe':'template';

        $this->load->view($template, $data);
    }

    public function ejecutar_form($etapa_id, $secuencia) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        $paso=$etapa->getPasoEjecutable($secuencia);
        $formulario = $paso->Formulario;
        $modo = $paso->modo;

        if ($modo == 'edicion') {
            foreach ($formulario->Campos as $c){
                //Validamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                if((!$c->readonly) && 
                   (!$c->dependiente_campo || $this->input->post($c->dependiente_campo)==$c->dependiente_valor))
                    $c->formValidate();
            }

            if ($this->form_validation->run() == TRUE) {
                foreach ($formulario->Campos as $c) {
                    //Almacenamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                    if((!$c->readonly) && 
                       (!$c->dependiente_campo || $this->input->post($c->dependiente_campo)==$c->dependiente_valor)){
                        $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($etapa->Tramite->id, $c->nombre);
                        if (!$dato)
                            $dato = new Dato();
                        $dato->nombre = $c->nombre;
                        $dato->valor = $this->input->post($c->nombre);
                        $dato->tramite_id = $etapa->Tramite->id;
                        $dato->save();
                    }
                }
                $etapa->save();

                $respuesta->validacion = TRUE;

                $qs=$this->input->server('QUERY_STRING');
                if ($etapa->Tarea->Pasos->count() - 1 == $secuencia) 
                    $respuesta->redirect = site_url('etapas/ejecutar_fin/' . $etapa_id).($qs?'?'.$qs:'');
                else 
                    $respuesta->redirect = site_url('etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1)).($qs?'?'.$qs:'');
                
                
            } else {
                $respuesta->validacion = FALSE;
                $respuesta->errores = validation_errors();
            }
        } else if ($modo == 'visualizacion') {
            $respuesta->validacion = TRUE;

            $qs=$this->input->server('QUERY_STRING');
            if ($etapa->Tarea->Pasos->count() - 1 == $secuencia) 
                $respuesta->redirect = site_url('etapas/ejecutar_fin/' . $etapa_id).($qs?'?'.$qs:'');
            else 
                $respuesta->redirect = site_url('etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1)).($qs?'?'.$qs:'');
            
        }

        echo json_encode($respuesta);
    }

    public function asignar($etapa_id) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id) {
            echo 'Etapa ya fue asignada.';
            exit;
        }

        if (!$etapa->canUsuarioAsignarsela(UsuarioSesion::usuario()->id)) {
            echo 'Usuario no puede asignarse esta etapa.';
            exit;
        }

        $etapa->asignar(UsuarioSesion::usuario()->id);

        redirect('etapas/inbox');
    }

    public function ejecutar_fin($etapa_id) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        //if($etapa->Tarea->asignacion!='manual'){
        //    $etapa->Tramite->avanzarEtapa();
        //    redirect();
        //    exit;
        //}

        $data['etapa'] = $etapa;
        $data['tareas_proximas']=$etapa->getTareasProximas();
        $data['qs']=$this->input->server('QUERY_STRING');
        
        $data['content'] = 'etapas/ejecutar_fin';
        $data['title'] = $etapa->Tarea->nombre;
        $template=$this->input->get('iframe')?'template_iframe':'template';
        
        $this->load->view($template, $data);
    }

    public function ejecutar_fin_form($etapa_id) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }


        $etapa->avanzar($this->input->post('usuarios_a_asignar'));

        $respuesta->validacion = TRUE;
        
        if($this->input->get('iframe'))
            $respuesta->redirect=site_url('etapas/ejecutar_exito');
        else    
            $respuesta->redirect = site_url();

        echo json_encode($respuesta);
    }

    //Pagina que indica que la etapa se completo con exito. Solamente la ven los que acceden mediante iframe.
    public function ejecutar_exito(){
        $data['content']='etapas/ejecutar_exito';
        $data['title']='Etapa completada con éxito';
        
        $this->load->view('template_iframe',$data);
    }
    
}
