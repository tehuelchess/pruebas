<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Reportes extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function listar($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['proceso'] = $proceso;
        $data['reportes'] = $data['proceso']->Reportes;

        $data['title'] = 'Documentos';
        $data['content'] = 'backend/reportes/index';

        $this->load->view('backend/template', $data);
    }

    public function ver($reporte_id) {
        $reporte = Doctrine::getTable('Reporte')->find($reporte_id);

        if ($reporte->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        $reporte->generar();
    }

    public function crear($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'No tiene permisos para crear este documento';
            exit;
        }

        $data['edit'] = FALSE;
        $data['proceso'] = $proceso;
        $data['title'] = 'Edición de Documento';
        $data['content'] = 'backend/reportes/editar';

        $this->load->view('backend/template', $data);
    }

    public function editar($reporte_id) {
        $reporte = Doctrine::getTable('Reporte')->find($reporte_id);

        if ($reporte->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'No tiene permisos para editar este documento';
            exit;
        }

        $data['reporte'] = $reporte;
        $data['edit'] = TRUE;
        $data['proceso'] = $reporte->Proceso;
        $data['title'] = 'Edición de Reporte';
        $data['content'] = 'backend/reportes/editar';

        $this->load->view('backend/template', $data);
    }

    public function editar_form($reporte_id = NULL) {
        $reporte = NULL;
        if ($reporte_id) {
            $reporte = Doctrine::getTable('Reporte')->find($reporte_id);
        } else {
            $reporte = new Reporte();
            $reporte->proceso_id = $this->input->post('proceso_id');
        }

        if ($reporte->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este documento.';
            exit;
        }

        $this->form_validation->set_rules('nombre', 'Nombre', 'required');
        $this->form_validation->set_rules('campos', 'Campos', 'required');

        if ($this->form_validation->run() == TRUE) {
            $reporte->nombre = $this->input->post('nombre');
            $reporte->campos = $this->input->post('campos');
            $reporte->save();

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('backend/reportes/listar/' . $reporte->Proceso->id);
        } else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function eliminar($reporte_id) {
        $reporte = Doctrine::getTable('Reporte')->find($reporte_id);

        if ($reporte->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este documento.';
            exit;
        }

        $proceso = $reporte->Proceso;
        $reporte->delete();

        redirect('backend/reportes/listar/' . $proceso->id);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */