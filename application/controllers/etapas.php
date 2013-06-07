<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Etapas extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function inbox() {
        $data['etapas'] = Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio());

        $data['sidebar'] = 'inbox';
        $data['content'] = 'etapas/inbox';
        $data['title'] = 'Bandeja de Entrada';
        $this->load->view('template', $data);
    }

    public function sinasignar() {
        $data['etapas'] = Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio());

        $data['sidebar'] = 'sinasignar';
        $data['content'] = 'etapas/sinasignar';
        $data['title'] = 'Sin Asignar';
        $this->load->view('template', $data);
    }

    public function ejecutar($etapa_id, $secuencia = 0) {
        $iframe = $this->input->get('iframe');

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if ($etapa->usuario_id != UsuarioSesion::usuario()->id) {
            if (!UsuarioSesion::usuario()->registrado) {
                $this->session->set_flashdata('redirect', current_url());
                redirect('autenticacion/login');
            }
            echo 'Usuario no tiene permisos para ejecutar esta etapa.';
            exit;
        }
        if (!$etapa->pendiente) {
            echo 'Esta etapa ya fue completada';
            exit;
        }
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }
        if ($etapa->vencida()) {
            echo 'Esta etapa se encuentra vencida';
            exit;
        }

        $qs = $this->input->server('QUERY_STRING');
        $paso = $etapa->getPasoEjecutable($secuencia);
        if (!$paso) {
            redirect('etapas/ejecutar_fin/' . $etapa->id . ($qs ? '?' . $qs : ''));
        } else if ($etapa->Tarea->final && $paso->getReadonly() && end($etapa->getPasosEjecutables()) == $paso) { //Cerrado automatico
            $etapa->iniciarPaso($paso);
            $etapa->finalizarPaso($paso);
            $etapa->avanzar();
            redirect('etapas/ver/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables())-1));
        }else{
            $etapa->iniciarPaso($paso);

            $data['secuencia'] = $secuencia;
            $data['etapa'] = $etapa;
            $data['paso'] = $paso;
            $data['qs'] = $this->input->server('QUERY_STRING');

            $data['sidebar'] = UsuarioSesion::usuario()->registrado ? 'inbox' : 'disponibles';
            $data['content'] = 'etapas/ejecutar';
            $data['title'] = $etapa->Tarea->nombre;
            $template = $this->input->get('iframe') ? 'template_iframe' : 'template';

            $this->load->view($template, $data);
        }
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
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }
        if ($etapa->vencida()) {
            echo 'Esta etapa se encuentra vencida';
            exit;
        }

        $paso = $etapa->getPasoEjecutable($secuencia);
        $formulario = $paso->Formulario;
        $modo = $paso->modo;

        $respuesta = new stdClass();

        if ($modo == 'edicion') {
            $validar_formulario = FALSE;
            foreach ($formulario->Campos as $c) {
                //Validamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                if ($c->isEditableWithCurrentPOST()) {
                    $c->formValidate();
                    $validar_formulario = TRUE;
                }
            }
            if (!$validar_formulario || $this->form_validation->run() == TRUE) {
                //Almacenamos los campos
                foreach ($formulario->Campos as $c) {
                    //Almacenamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)
                    if ($c->isEditableWithCurrentPOST()) {
                        $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($c->nombre, $etapa->id);
                        if (!$dato)
                            $dato = new DatoSeguimiento();
                        $dato->nombre = $c->nombre;
                        $dato->valor = $this->input->post($c->nombre);
                        $dato->etapa_id = $etapa->id;
                        $dato->save();
                    }
                }
                $etapa->save();

                $etapa->finalizarPaso($paso);

                $respuesta->validacion = TRUE;

                $qs = $this->input->server('QUERY_STRING');
                $prox_paso = $etapa->getPasoEjecutable($secuencia + 1);
                if (!$prox_paso) {
                    $respuesta->redirect = site_url('etapas/ejecutar_fin/' . $etapa_id) . ($qs ? '?' . $qs : '');
                } else if ($etapa->Tarea->final && $prox_paso->getReadonly() && end($etapa->getPasosEjecutables()) == $prox_paso) { //Cerrado automatico    
                    $etapa->iniciarPaso($prox_paso);
                    $etapa->finalizarPaso($prox_paso);
                    $etapa->avanzar();
                    $respuesta->redirect = site_url('etapas/ver/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables())-1));
                } else {
                    $respuesta->redirect = site_url('etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1)) . ($qs ? '?' . $qs : '');
                }
            } else {
                $respuesta->validacion = FALSE;
                $respuesta->errores = validation_errors();
            }
        } else if ($modo == 'visualizacion') {
            $respuesta->validacion = TRUE;

            $qs = $this->input->server('QUERY_STRING');
            $prox_paso = $etapa->getPasoEjecutable($secuencia + 1);
            if (!$prox_paso) {
                $respuesta->redirect = site_url('etapas/ejecutar_fin/' . $etapa_id) . ($qs ? '?' . $qs : '');
            } else if ($etapa->Tarea->final && $prox_paso->getReadonly() && end($etapa->getPasosEjecutables()) == $prox_paso) { //Cerrado automatico
                $etapa->iniciarPaso($prox_paso);
                $etapa->finalizarPaso($prox_paso);
                $etapa->avanzar();
                $respuesta->redirect = site_url('etapas/ver/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables())-1));
            } else {
                $respuesta->redirect = site_url('etapas/ejecutar/' . $etapa_id . '/' . ($secuencia + 1)) . ($qs ? '?' . $qs : '');
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
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }

        //if($etapa->Tarea->asignacion!='manual'){
        //    $etapa->Tramite->avanzarEtapa();
        //    redirect();
        //    exit;
        //}

        $data['etapa'] = $etapa;
        $data['tareas_proximas'] = $etapa->getTareasProximas();
        $data['qs'] = $this->input->server('QUERY_STRING');

        $data['sidebar'] = UsuarioSesion::usuario()->registrado ? 'inbox' : 'disponibles';
        $data['content'] = 'etapas/ejecutar_fin';
        $data['title'] = $etapa->Tarea->nombre;
        $template = $this->input->get('iframe') ? 'template_iframe' : 'template';

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
        if (!$etapa->Tarea->activa()) {
            echo 'Esta etapa no se encuentra activa';
            exit;
        }


        $etapa->avanzar($this->input->post('usuarios_a_asignar'));

        $respuesta = new stdClass();
        $respuesta->validacion = TRUE;

        if ($this->input->get('iframe'))
            $respuesta->redirect = site_url('etapas/ejecutar_exito');
        else
            $respuesta->redirect = site_url();

        echo json_encode($respuesta);
    }

    //Pagina que indica que la etapa se completo con exito. Solamente la ven los que acceden mediante iframe.
    public function ejecutar_exito() {
        $data['content'] = 'etapas/ejecutar_exito';
        $data['title'] = 'Etapa completada con éxito';

        $this->load->view('template_iframe', $data);
    }

    public function ver($etapa_id, $secuencia = 0) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if (UsuarioSesion::usuario()->id != $etapa->usuario_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $paso = $etapa->getPasoEjecutable($secuencia);

        $data['etapa'] = $etapa;
        $data['paso'] = $paso;
        $data['secuencia'] = $secuencia;

        $data['sidebar'] = 'participados';
        $data['title'] = 'Historial - ' . $etapa->Tarea->nombre;
        $data['content'] = 'etapas/ver';
        $this->load->view('template', $data);
    }

}
