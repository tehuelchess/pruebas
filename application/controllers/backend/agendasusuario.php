<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class AgendasUsuario extends MY_BackendController {
    private $domain='';
    private $appkey='';
    private $base_services='';
    private $context='';
    private $records=10;
    public function __construct() {
        parent::__construct();
        include APPPATH . 'third_party/httpful/bootstrap.php';
        UsuarioSesion::force_login();
        $this->base_services=$this->config->item('base_service');
        $this->context=$this->config->item('context_service');
        $this->records=$this->config->item('records');
        if( isset(UsuarioBackendSesion::usuario()->cuenta_id) && is_numeric(UsuarioBackendSesion::usuario()->cuenta_id)){
            $cuenta=UsuarioBackendSesion::usuario()->cuenta_id;
        }else{
            if( isset(UsuarioSesion::usuario()->cuenta_id) && is_numeric(UsuarioSesion::usuario()->cuenta_id)){
                $cuenta=UsuarioSesion::usuario()->cuenta_id;
            }else{
                $cuenta=1;
            }
        }
        try{
            $service=new Connect_services();
            $service->setCuenta($cuenta);
            $service->load_data();
            $this->domain=$service->getDomain();
            $this->appkey=$service->getAppkey();
        }catch(Exception $err){
            //echo 'Error: '.$err->getMessage();
        }

    }
    public function ajax_cancelar_cita(){
        if (!UsuarioSesion::usuario()->registrado) {
            $this->session->set_flashdata('redirect', current_url());
            redirect('autenticacion/login');
        }
        $data['id']=(isset($_GET['id']))?$_GET['id']:0;
        $data['fecha']=(isset($_GET['fecha']))?$_GET['fecha']:'';
        $funcionario=(isset($_GET['func']))?$_GET['func']:0;
        if($funcionario==1){
            $data['funcionario']=true;//carga cancelar agenda como funcionario
            
        }else{
            $data['funcionario']=false;//carga cancelar agenda como ciudadano
        }
        $this->load->view ( 'backend/agendas/ajax_front_cancelar_cita', $data );
    }
    public function ajax_asistencia($asistencia,$idcita,$idtramite,$calendario){
        $data['asistencia']=$asistencia;
        $data['idcita']=$idcita;
        $data['idtramite']=$idtramite;
        $data['calendario']=$calendario;

        $this->load->view ( 'backend/agendas/ajax_front_asistencia', $data );
    }
    public function ajax_confirmo_asistencia(){
        $idcita=(isset($_GET['idcita']) && is_numeric($_GET['idcita']))?$_GET['idcita']:0;
        $asistencia=(isset($_GET['asistencia']) && is_numeric($_GET['asistencia']))?$_GET['asistencia']:0;
        $idtramite=(isset($_GET['idtramite']) && is_numeric($_GET['idtramite']))?$_GET['idtramite']:0;
        $calendario=(isset($_GET['calendario']) && is_numeric($_GET['calendario']))?$_GET['calendario']:0;
        $code=0;
        $mensaje='';
        $idetapa=0;
        try{
            $conset=Doctrine_Query::create()
                        ->from("Etapa")
                        ->where('tramite_id = ? AND pendiente = ?',array($idtramite,1))
                        ->execute();
            foreach($conset as $ob){
                $idetapa=$ob->id;
            }
            $usuario=Doctrine_Query::create()
                        ->from("Campo")
                        ->where('agenda_campo='.$calendario)
                        ->execute();
            $nombrecampo ='';
            foreach($usuario as $ob){
                $nombrecampo=$ob->nombre.'_asistio';
            }
            if($idetapa>0){

                $valueasis=($asistencia==1)?'si':'no';
                $result = Doctrine_Query::create ()
                ->select('COUNT(*) AS cuenta')
                ->from ('DatoSeguimiento')
                ->where ("nombre = ? AND etapa_id = ?",array($nombrecampo,$idetapa))
                ->execute ();
                if($result[0]->cuenta>=1){
                    $q=Doctrine_Query::create()
                    ->update('DatoSeguimiento')
                    ->set('valor','?','"'.$valueasis.'"')
                    ->where ("nombre = ? AND etapa_id = ?",array($nombrecampo,$idetapa));
                    $q->execute(); 
                }else{
                    $datosseg=new DatoSeguimiento();
                    $datosseg->nombre=$nombrecampo;
                    $datosseg->valor=$valueasis;
                    $datosseg->etapa_id=$idetapa;
                    $datosseg->save();
                }
            }
            $json='{
                "applyer_attended": "'.$asistencia.'"
            }';
            $uri=$this->base_services.''.$this->context.'appointments/assists/'.$idcita;
            $response = \Httpful\Request::put($uri)
                ->expectsJson()
                ->body($json)
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain,              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body->response->code)){
                $code=$response->body->response->code;
                $mensaje=$response->body->response->message;
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $array=array('code'=>$code,'message'=>$mensaje);
        echo json_encode($array);
    }
    function ajax_modal_editar_cita(){
        $idagenda=(isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
        $idobject=(isset($_GET['object']) && is_numeric($_GET['object']))?$_GET['object']:0;
        $data['idagenda']=$idagenda;
        $data['idobject']=$idobject;
        $this->load->view ( 'tramites/ajax_editar_cita', $data );
    }
    function ajax_modal_editar_cita_funcionario(){
        $idagenda=(isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
        $data['idagenda']=$idagenda;
        $data['idobject']=0;
        $this->load->view ( 'tramites/ajax_editar_cita_funcionario', $data );
    }
    function ajax_modal_ver_cita_funcionario($idcita){
        $solicitante=(isset($_GET['soli']))?$_GET['soli']:'';
        $dia=(isset($_GET['dia']))?$_GET['dia']:'';
        $hora=(isset($_GET['hora']))?$_GET['hora']:'';
        $tramite=(isset($_GET['tramite']))?$_GET['tramite']:'';
        $correo=(isset($_GET['email']))?$_GET['email']:'';
        $data['solicitante']=$solicitante;
        $data['dia']=$dia;
        $data['hora']=$hora;
        $data['tramite']=$tramite;
        $data['correo']=$correo;
        $data['idcita']=$idcita;
        $this->load->view ('tramites/ajax_vercancelar_cita_funcionario', $data);
    }
    function ajax_vercancelar_cita_funcionario($idcita){
        $data['idcita']=$idcita;
        $this->load->view ('tramites/ajax_cancelar_cita_funcionario', $data);
    }


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */