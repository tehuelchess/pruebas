<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Formularios extends MY_BackendController {
    private $domain='';
    private $appkey='';
    private $base_services='';
    private $context='';
    private $records=10;
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
        include APPPATH . 'third_party/httpful/bootstrap.php';
        $this->base_services=$this->config->item('base_service');
        $this->context=$this->config->item('context_service');
        $this->records=$this->config->item('records');
        try{
            $service=new Connect_services();
            $service->setCuenta(UsuarioBackendSesion::usuario()->cuenta_id);
            $service->load_data();
            $this->domain=$service->getDomain();
            $this->appkey=$service->getAppkey();
        }catch(Exception $err){
            //echo 'Error: '.$err->getMessage();
        }
        
//        if(UsuarioBackendSesion::usuario()->rol!='super' && UsuarioBackendSesion::usuario()->rol!='modelamiento'){
        if(!in_array('super', explode(',',UsuarioBackendSesion::usuario()->rol) ) && !in_array( 'modelamiento',explode(',',UsuarioBackendSesion::usuario()->rol))){
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
        
        $fecha = new DateTime ();
         
        // Auditar
        $registro_auditoria = new AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format ( "Y-m-d H:i:s" );
        $registro_auditoria->operacion = 'Eliminación de Formulario';
        $usuario = UsuarioBackendSesion::usuario ();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = UsuarioBackendSesion::usuario()->cuenta_id;
        
        
        // Detalles
        $formulario_array['proceso'] = $proceso ->toArray(false);
        $formulario_array['formulario'] = $formulario->toArray(false);
        unset($formulario_array['formulario']['proceso_id']);

        foreach($formulario->Campos as $campo){
        	
        	$campo_array = $campo->toArray(false);
        	if ($campo->documento_id != null)
        		$campo_array['documento'] = $campo->Documento->nombre;
        	unset($campo_array['documento_id']);
        	$formulario_array['campos'][] = $campo_array;
        	
        	
        }
        
        $registro_auditoria->detalles = json_encode($formulario_array);
        $registro_auditoria->save();
         
        
        
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
    public function obtener_agenda(){
        $code=0;
        $mensaje='';
        $data=array();
        $idagenda=(isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
        try{
            $uri=$this->base_services.''.$this->context.'calendars/'.$idagenda;//url del servicio con los parametros
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,             // heder de la app key
                    'domain' => $this->domain,                              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                $code=$response->body[0]->response->code;
                $data=$response->body[1]->calendars[0]->owner_name;

            }
        }catch(Exception $err){

        }
        echo json_encode(array('code'=>$code,'mensaje'=>$mensaje,'calendario_owner'=>$data));
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
        
        $this->form_validation->set_rules('nombre','Nombre','trim|required');
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
            $campo->nombre=trim($this->input->post('nombre'));
            $campo->etiqueta=$this->input->post('etiqueta',false);
            $campo->readonly=$this->input->post('readonly');
            $campo->valor_default=$this->input->post('valor_default',false);
            $campo->ayuda=$this->input->post('ayuda');
            $campo->validacion=explode('|',$this->input->post('validacion'));
            $campo->dependiente_tipo=$this->input->post('dependiente_tipo');
            
            // Si es checkbox, agregar corchetes al final
            $campo_dependiente = Doctrine_Query::create()
            	->from("Campo c, c.Formulario f")
            	->where("c.nombre = ?", $this->input->post('dependiente_campo'))
            	->andWhere("f.proceso_id = ?", $campo->Formulario->Proceso->id)
            	->fetchOne();
            $campo->dependiente_campo = $campo_dependiente && $campo_dependiente->tipo == 'checkbox' ? $this->input->post('dependiente_campo') .'[]':$this->input->post('dependiente_campo');
            
            $campo->dependiente_valor=$this->input->post('dependiente_valor');
            $campo->dependiente_relacion=$this->input->post('dependiente_relacion');
            $campo->datos=$this->input->post('datos');
            $campo->documento_id=$this->input->post('documento_id');
            $campo->extra=$this->input->post('extra');
            $campo->agenda_campo=$this->input->post('agenda_campo');
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
    
    public function exportar($formulario_id)
    {

        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        $json = $formulario->exportComplete();

        header("Content-Disposition: attachment; filename=\"".mb_convert_case(str_replace(' ','-',$formulario->nombre),MB_CASE_LOWER).".simple\"");
        header('Content-Type: application/json');
        echo $json;

    }
    
    public function importar()
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            $proceso_id = $this->input->post('proceso_id');

            if ($file_path && $proceso_id) {
                $input = file_get_contents($_FILES['archivo']['tmp_name']);
                $formulario = Formulario::importComplete($input, $proceso_id);
                $formulario->proceso_id = $proceso_id;            
                $formulario->save();            
            } else {
                die('No se especificó archivo o ID proceso');
            }
        } catch (Exception $ex) {
            die('Código: '.$ex->getCode().' Mensaje: '.$ex->getMessage());
        }
        
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function listarPertenece(){
        $data=array();
        ///////////////////////////////////extrae los usuarios///////////////////////////
        $q = Doctrine_Query::create()
                ->select("id,nombres, apellido_paterno, apellido_materno,email")
                ->from("Usuario")
                ->where("registrado = ? AND cuenta_id <> ? AND open_id = ? AND cuenta_id=?",array(1,'',0,UsuarioBackendSesion::usuario()->cuenta_id));
        $usuarios = $q->execute();
        $data[]=array('id'=>0,'nombre'=>'Seleccione');
        foreach($usuarios as $usuario){
            $nombre_completo=$usuario->nombres;
            $trimAP = trim($usuario->apellido_paterno);
            $trimAM = trim($usuario->apellido_materno);
            $nombre_completo=(!empty($trimAP))?$nombre_completo.' '.$usuario->apellido_paterno:$nombre_completo;
            $nombre_completo=(!empty($trimAM))?$nombre_completo.' '.$usuario->apellido_materno:$nombre_completo;
            $data[]=array('id'=>$usuario->id,'nombre'=>$nombre_completo,'tipo'=>0,'email'=>$usuario->email);
        }
        ///////////////////////////////////extrae los usuarios///////////////////////////
        ///////////////////////////////////extrae los grupos///////////////////////////
        $q = Doctrine_Query::create()
                ->select("id,nombre")
                ->from("GrupoUsuarios");
        $grupo_usuarios = $q->execute();
        foreach($grupo_usuarios as $grupo){
            $data[]=array('id'=>$grupo->id,'nombre'=>$grupo->nombre,'tipo'=>1,'email'=>'grupo@grupo.com');
        }
        ///////////////////////////////////extrae los grupos///////////////////////////

        $items=array('items'=>$data);
        $arr=array('code'=>200,'mensaje'=>'Ok','resultado'=>$items);
        echo json_encode($arr);
    }
    public function ajax_mi_calendario(){
        $code=0;
        $mensaje='';
        $data=array();
        $idagenda=(isset($_GET['pertenece']) && is_numeric($_GET['pertenece']))?$_GET['pertenece']:0;
        if($idagenda>0){
            try{
                $uri=$this->base_services.''.$this->context.'calendars/listByOwner/'.$idagenda;//url del servicio con los parametros
                $response = \Httpful\Request::get($uri)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,              // heder de la app key
                        'domain' => $this->domain,              // heder de domain
                    ))
                    ->sendIt();
                $code=$response->code;
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                    $code=$response->body[0]->response->code;
                    $mensaje=$response->body[0]->response->message;
                    foreach($response->body[1]->calendars as $items){
                        $tmp=new stdClass();
                        $tmp->id=$items->id;
                        $tmp->name=$items->name;
                        $tmp->owner_id=$items->owner_id;
                        $tmp->owner_name=$items->owner_name;
                        $tmp->owner_email=$items->owner_email;
                        $tmp->is_group=$items->is_group;
                        $tmp->schedule=$items->schedule;
                        $tmp->time_attention=$items->time_attention;
                        $tmp->concurrency=$items->concurrency;
                        $tmp->ignore_non_working_days=$items->ignore_non_working_days;
                        $tmp->time_cancel_appointment=$items->time_cancel_appointment;
                        $tmp->time_confirm_appointment=$items->time_confirm_appointment;
                        $data[]=$tmp;
                    }
                }else{
                    $mensaje=$response->body->response->message;
                }
            }catch(Exception $err){
                $mensaje=$err->getMessage();
            }
            $usuario= Doctrine::getTable('Usuario')->findByid($idagenda);
            foreach($usuario[0]->GruposUsuarios as $g){
                try{
                    $uri=$this->base_services.''.$this->context.'calendars/listByOwner/'.$g->id;
                    $response = \Httpful\Request::get($uri)
                        ->expectsJson()
                        ->addHeaders(array(
                            'appkey' => $this->appkey,              // heder de la app key
                            'domain' => $this->domain,              // heder de domain
                        ))
                        ->sendIt();
                    $code=$response->code;
                    if(isset($response->code) && $response->code==200 && isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                        foreach($response->body[1]->calendars as $item){
                            $tmp=new stdClass();
                            $tmp->id=$item->id;
                            $tmp->name=$item->name;
                            $tmp->owner_id=$item->owner_id;
                            $tmp->owner_name=$item->owner_name;
                            $tmp->owner_email=$item->owner_email;
                            $tmp->is_group=$item->is_group;
                            $tmp->schedule=$item->schedule;
                            $tmp->time_attention=$item->time_attention;
                            $tmp->concurrency=$item->concurrency;
                            $tmp->ignore_non_working_days=$item->ignore_non_working_days;
                            $tmp->time_cancel_appointment=$item->time_cancel_appointment;
                            $tmp->time_confirm_appointment=$item->time_confirm_appointment;
                            $data[]=$tmp;
                        }
                    }
                }catch(Exception $err){
                    throw new Exception($err->getMessage());
                }
            }
        }
        echo json_encode(array('code'=>$code,'message'=>$mensaje,'calendars'=>$data));
    }
}
?>
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */