<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Formularios extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
        
        if(UsuarioBackendSesion::usuario()->rol!='super' && UsuarioBackendSesion::usuario()->rol!='modelamiento'){
            echo 'No tiene permisos para acceder a esta seccion.';
            exit;
        }
    }

    public function listar($proceso_id){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        
        $data['proceso']=$proceso;
        $data['formularios']=$data['proceso']->Formularios;
        
        $data['title']='Formularios';
        $data['content']='backend/formularios/index';
        
        $this->load->view('backend/template',$data);
    }
    
    public function crear($proceso_id){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para crear un formulario dentro de este proceso.';
            exit;
        }
        
        $formulario=new Formulario();
        $formulario->proceso_id=$proceso->id;
        $formulario->nombre='Formulario';
        $formulario->save();
        
        redirect('backend/formularios/editar/'.$formulario->id);
    }
    
    public function eliminar($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar este formulario.';
            exit;
        }
        
        $proceso=$formulario->Proceso;
        $formulario->delete();
        
        redirect('backend/formularios/listar/'.$proceso->id);
    }


    public function editar($formulario_id) {
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }
        
        $data['formulario']=$formulario;
        $data['proceso']=$formulario->Proceso;
        
        $data['title']=$formulario->nombre;
        $data['content']='backend/formularios/editar';
        
        $this->load->view('backend/template',$data);
    }
    
    public function ajax_editar($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }
        
        $data['formulario']=$formulario;
        
        $this->load->view('backend/formularios/ajax_editar',$data);
    }
    
    public function editar_form($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }
        
        $this->form_validation->set_rules('nombre','Nombre','required');
        
        $respuesta=new stdClass();
        if($this->form_validation->run()==TRUE){
            $formulario->nombre=$this->input->post('nombre');
            $formulario->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/formularios/editar/'.$formulario->id);
            
        } else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
            
        }
        
        echo json_encode($respuesta);
    }
    
    public function ajax_editar_campo($campo_id){
        $campo=Doctrine::getTable('Campo')->find($campo_id);
        
        if($campo->Formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este campo.';
            exit;
        }
        
        $data['edit']=TRUE;
        $data['campo']=$campo;
        $data['formulario']=$campo->Formulario;
        
        $this->load->view('backend/formularios/ajax_editar_campo',$data);
    }
    
    public function editar_campo_form($campo_id=NULL){
        $campo=NULL;
        if($campo_id){
            $campo=Doctrine::getTable('Campo')->find($campo_id);

            
        }else{
            $formulario=Doctrine::getTable('Formulario')->find($this->input->post('formulario_id'));
                $campo=Campo::factory($this->input->post('tipo'));
                $campo->formulario_id=$formulario->id;
                $campo->posicion=1+$formulario->getUltimaPosicionCampo();
        }
        
        if($campo->Formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
                echo 'Usuario no tiene permisos para editar este campo.';
                exit;
            }
        
        $this->form_validation->set_rules('nombre','Nombre','required');
        $this->form_validation->set_rules('etiqueta','Etiqueta','required');
        $this->form_validation->set_rules('validacion','Validación','callback_clean_validacion');
        if(!$campo_id){
            $this->form_validation->set_rules('formulario_id','Formulario','required|callback_check_permiso_formulario');
            $this->form_validation->set_rules('tipo','Tipo de Campo','required');
        }
        $campo->backendExtraValidate();
        
        $respuesta=new stdClass();
        if($this->form_validation->run()==TRUE){
            if(!$campo){
                
            }
            $campo->nombre=$this->input->post('nombre');
            $campo->etiqueta=$this->input->post('etiqueta');
            $campo->readonly=$this->input->post('readonly');
            $campo->valor_default=$this->input->post('valor_default');
            $campo->validacion=explode('|',$this->input->post('validacion'));
            $campo->dependiente_tipo=$this->input->post('dependiente_tipo');
            $campo->dependiente_campo=$this->input->post('dependiente_campo');
            $campo->dependiente_valor=$this->input->post('dependiente_valor');
            $campo->datos=$this->input->post('datos');
            $campo->documento_id=$this->input->post('documento_id');
            $campo->extra=$this->input->post('extra');
            $campo->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/formularios/editar/'.$campo->Formulario->id);
            
        } else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
            
        }
        
        echo json_encode($respuesta);
    }
    
    public function ajax_agregar_campo($formulario_id, $tipo){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para agregar campos a este formulario.';
            exit;
        }
        
        $campo=Campo::factory($tipo);
        $campo->formulario_id=$formulario_id;
        
        
        $data['edit']=false;
        $data['formulario']=$formulario;
        $data['campo']=$campo;
        $this->load->view('backend/formularios/ajax_editar_campo',$data);
    }
    
    public function eliminar_campo($campo_id){
        $campo=Doctrine::getTable('Campo')->find($campo_id);
                
        if($campo->Formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar este campo.';
            exit;
        }
        
        $formulario=$campo->Formulario;
        $campo->delete();
        
        redirect('backend/formularios/editar/'.$formulario->id);
    }
    
    public function editar_posicion_campos($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }
        
        $json=$this->input->post('posiciones');
        $formulario->updatePosicionesCamposFromJSON($json);
        
        
    }
    
    public function check_permiso_formulario($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        if($formulario->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            $this->form_validation->set_message('check_permiso_formulario' ,'Usuario no tiene permisos para agregar campos a este formulario.');
            return FALSE;
        }
        
        return TRUE;
    }
    
    function clean_validacion($validacion){
        return preg_replace('/\|\s*$/','',$validacion);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */