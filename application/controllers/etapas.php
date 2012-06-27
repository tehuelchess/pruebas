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

    public function ejecutar($etapa_id, $paso = 0) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        $data['etapa'] = $etapa;
        $data['paso'] = $paso;

        $data['content'] = 'etapas/ejecutar';
        $data['title'] = $etapa->Tarea->nombre;
        $this->load->view('template', $data);
    }

    public function ejecutar_form($etapa_id, $paso) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }

        $formulario = $etapa->Tarea->Pasos[$paso]->Formulario;
        $modo = $etapa->Tarea->Pasos[$paso]->modo;

        if ($modo == 'edicion') {
            foreach ($formulario->Campos as $c)
                $c->formValidate();

            if ($this->form_validation->run() == TRUE) {
                foreach ($formulario->Campos as $c) {

                    $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($etapa->Tramite->id, $c->nombre);
                    if (!$dato)
                        $dato = new Dato();
                    $dato->nombre = $c->nombre;
                    $dato->valor = $this->input->post($c->nombre);
                    $dato->tramite_id = $etapa->Tramite->id;
                    $dato->save();
                }
                $etapa->save();

                $respuesta->validacion = TRUE;

                if ($etapa->Tarea->Pasos->count() - 1 == $paso) {
                    //$etapa->Tramite->avanzarEtapa();
                    $respuesta->redirect = site_url('etapas/ejecutar_fin/' . $etapa_id);
                } else {
                    $respuesta->redirect = site_url('etapas/ejecutar/' . $etapa_id . '/' . ($paso + 1));
                }
            } else {
                $respuesta->validacion = FALSE;
                $respuesta->errores = validation_errors();
            }
        } else if ($modo == 'visualizacion') {
            $respuesta->validacion = TRUE;

            if ($etapa->Tarea->Pasos->count() - 1 == $paso) {
                //$etapa->Tramite->avanzarEtapa();
                $respuesta->redirect = site_url('etapas/ejecutar_fin/' . $etapa_id);
            } else {
                $respuesta->redirect = site_url('etapas/ejecutar/' . $etapa_id . '/' . ($paso + 1));
            }
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
        $data['content'] = 'etapas/ejecutar_fin';
        $data['title'] = $etapa->Tarea->nombre;
        $this->load->view('template', $data);
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
        $respuesta->redirect = site_url();



        echo json_encode($respuesta);
    }

}
