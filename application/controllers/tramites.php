<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tramites extends MY_Controller {
    private $domain='';
    private $appkey='';
    private $base_services='';
    private $context='';
    private $records=10;
    public function __construct() {
        parent::__construct();
        include APPPATH . 'third_party/httpful/bootstrap.php';
        $this->base_services=$this->config->item('base_service');
        $this->context=$this->config->item('context_service');
        $this->records=$this->config->item('records');
        $cuenta=UsuarioSesion::usuario()->cuenta_id;
        $cuenta=(isset($cuenta) && is_numeric($cuenta) && $cuenta>0)?$cuenta:1;
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

    public function index() {
        redirect('etapas/inbox');
    }

    /*public function participados() {
        $data['tramites']=Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio());        
        $data['sidebar']='participados';
        $data['content'] = 'tramites/participados';
        $data['title'] = 'Bienvenido';
        $this->load->view('template', $data);
    }*/
	public function participados($offset=0) {
        $this->load->library('pagination');
        $this->load->helper('form');
        $this->load->helper('url');

        $query = $this->input->post('query');
        $matches="";
        $rowtramites="";
        $contador="0";
        $resultotal="false";
        $perpage=50;

        if ($query) { 
            $this->load->library('sphinxclient');
            $this->sphinxclient->setServer ( $this->config->item ( 'sphinx_host' ), $this->config->item ( 'sphinx_port' ) );
            $this->sphinxclient->SetLimits($offset, 10000);
            $result = $this->sphinxclient->query(json_encode($query), 'tramites');                         
           
            if($result['total'] > 0 ){
                $resultotal="true";             
            }else{               
                $resultotal="false";
            }
        }
       
       /*
        $statement = Doctrine_Manager::getInstance()->connection();
        $results = $statement->execute("Select * from dato_seguimiento where nombre='desc_proceso_tramite' limit 1");
        $datos=$results->fetchAll();
        foreach($datos as $d){  echo $d['valor'];  }    
        */
        if($resultotal=='true'){
                $matches = array_keys($result['matches']);
                $contador= Doctrine::getTable('Tramite')->findParticipadosMatched(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),$matches,$query)->count();                               
                $rowtramites= Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(), $perpage,$offset,$matches,$query);    
        }else{
                $rowtramites= Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(), $perpage, $offset,'0',$query);
                $contador= Doctrine::getTable('Tramite')->findParticipadosALL(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio())->count();
        }
        
        $config['base_url'] = site_url('tramites/participados');
        $config['total_rows'] = $contador;  
        $config['per_page'] = $perpage;       
        $config['full_tag_open'] = '<div class="pagination pagination-centered"><ul>';
        $config['full_tag_close'] = '</ul></div>';
        $config['page_query_string']=false;
        $config['query_string_segment']='offset';
        $config['first_link'] = 'Primero';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Último';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '»';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '«';
        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';      

        $this->pagination->initialize($config);
        $data['tramites']=$rowtramites;
        $data['query'] = $query;
        $data['sidebar']='participados';
        $data['content'] = 'tramites/participados';
        $data['title'] = 'Bienvenido';
        
        $data['links'] = $this->pagination->create_links(); 
        $config =Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(1,Cuenta::cuentaSegunDominio()->id); 
        if($config){
           $config =Doctrine::getTable('Config')->findOneByIdAndIdparAndCuentaIdOrCuentaId($config->config_id,$config->idpar,Cuenta::cuentaSegunDominio()->id,0);
           $nombre = $config->nombre;
           if ($nombre=='default'){
                $data['template_path'] = 'uploads/themes/default/';
                $this->load->view('themes/default/template', $data);
           } else {
                $data['template_path'] = 'uploads/themes/'.Cuenta::cuentaSegunDominio()->id.'/'.$nombre.'/';
                $this->load->view('themes/'.Cuenta::cuentaSegunDominio()->id.'/'.$nombre.'/template', $data);
           }
           
        }else{
           $data['template_path'] = 'uploads/themes/default/';
           $this->load->view('themes/default/template', $data);
        }
    }
    private function validar_agenda_grupos($id){
        $usuario= Doctrine::getTable('Usuario')->findByid($id);
        $sw=false;
        foreach($usuario[0]->GruposUsuarios as $g){
            try{
                $uri=$this->base_services.''.$this->context.'calendars/listByOwner/'.$g->id;
                $response = \Httpful\Request::get($uri)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,              // heder de la app key
                        'domain' => $this->domain              // heder de domain
                    ))
                    ->sendIt();
                $code=$response->code;
                if(isset($response->code) && $response->code==200 && isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    if(count($response->body[1]->calendars)>0){
                        $sw=true;
                    }else{
                        $sw=false;
                    }
                }else{
                    $sw=false;
                }
            }catch(Exception $err){
                throw new Exception($err->getMessage());
            }
        }
        return $sw;
    }
    private function validarTipoUsuario(){//con esta funcion se obtiene las agendas (si tiene agenda es funcionario, de lo contrario es un ciudadano)
        try{
            $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
            $uri=$this->base_services.''.$this->context.'calendars/listByOwner/'.$id;
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                if(count($response->body[1]->calendars)>0){
                    return true;
                }else{
                    $sw=$this->validar_agenda_grupos($id);
                    return $sw;
                }
            }else{
                $sw=$this->validar_agenda_grupos($id);
                return $sw;
            }
        }catch(Exception $err){
            return false;
        }
    }
    public function ajax_listar_citas($pagina,$tipousuario){
        $datos=array();
        $total_registros=0;
        $code=0;
        $mensaje='';
        try{
            $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
            //$usuario= Doctrine::getTable('Usuario')->findByid($id);
            if($tipousuario){
                $uri=$this->base_services.''.$this->context.'appointments/listByOwner/'.$id.'?page='.$pagina;
            }else{
                $uri=$this->base_services.''.$this->context.'appointments/listByApplyer/'.$id.'?page='.$pagina;    
            }
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                $total_registros=$response->body[1]->count;
                foreach($response->body[1]->appointments as $items){
                    $class=$newobj = new stdClass();
                    $class->appointment_id=$items->appointment_id;
                    $class->subject=$items->subject;
                    $class->owner_name=$items->owner_name;
                    $class->appointment_time=$items->appointment_time;
                    $class->applyer_attended=$items->applyer_attended;
                    $class->calendar_id=$items->calendar_id;
                    $class->applyer_email=$items->applyer_email;
                    $class->applyer_name=trim($items->applyer_name);
                    $metadata=json_decode($items->metadata);
                    $class->idtramite=(isset($metadata->tramite) && is_numeric($metadata->tramite))?$metadata->tramite:0;
                    $class->etapa=(isset($metadata->etapa) && is_numeric($metadata->etapa))?$metadata->etapa:0;
                    $class->idcampo=(isset($metadata->idcampo) && is_numeric($metadata->idcampo))?$metadata->idcampo:0;
                    $proceso=Doctrine_Query::create()
                    ->select('p.nombre')
                    ->from("Proceso p,Tramite t")
                    ->where('p.id=t.proceso_id AND t.id=?',$class->idtramite)
                    ->execute();
                    $nombre ='';
                    foreach($proceso as $ob){
                        $nombre=$ob->nombre;
                    }
                    $class->tramite=$nombre;

                    $datos[]=$class;
                }
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $array=array('code'=>$code,'message'=>$mensaje,'data'=>$datos,'count'=>$total_registros);
        return $array;
    }
    public function ajax_listar_citas_agenda($pagina,$agenda){
        $datos=array();
        $total_registros=0;
        $code=0;
        $mensaje='';
        try{
            $uri=$this->base_services.''.$this->context.'appointments/listByCalendar/'.$agenda.'?page='.$pagina;
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                //$datos=$response->body->appointments;
                foreach($response->body[1]->appointments as $items){
                    $class=$newobj = new stdClass();
                    $class->appointment_id=$items->appointment_id;
                    $class->subject=$items->subject;
                    $class->owner_name=$items->owner_name;
                    $class->appointment_time=$items->appointment_time;
                    $class->applyer_attended=$items->applyer_attended;
                    $class->calendar_id=$items->calendar_id;
                    $class->applyer_email=$items->applyer_email;
                    $class->applyer_name=trim($items->applyer_name);
                    $metadata=json_decode($items->metadata);
                    $class->idtramite=(isset($metadata->tramite) && is_numeric($metadata->tramite))?$metadata->tramite:0;
                    $class->etapa=(isset($metadata->etapa) && is_numeric($metadata->etapa))?$metadata->etapa:0;
                    $class->idcampo=(isset($metadata->idcampo) && is_numeric($metadata->idcampo))?$metadata->idcampo:0;
                    $proceso=Doctrine_Query::create()
                    ->select('p.nombre')
                    ->from("Proceso p,Tramite t")
                    ->where('p.id=t.proceso_id AND t.id=?',$class->idtramite)
                    ->execute();
                    $nombre ='';
                    foreach($proceso as $ob){
                        $nombre=$ob->nombre;
                    }
                    $class->tramite=$nombre;


                    $datos[]=$class;
                }
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $array=array('code'=>$code,'message'=>$mensaje,'data'=>$datos,'count'=>$total_registros);
        return $array;
    }
    public function miagenda($pagina=1) {
        if (!UsuarioSesion::usuario()->registrado) {
            $this->session->set_flashdata('redirect', current_url());
            redirect('autenticacion/login');
        }
        $tipousuario=$this->validarTipoUsuario();
        $agendas=array();
        $agenda=(isset($_POST['cmbagenda']) && is_numeric($_POST['cmbagenda']) && $_POST['cmbagenda']>0)?$_POST['cmbagenda']:0;
        if($tipousuario){
            try{
                $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
                $agendas=$this->obtenerAgendas($id);//
                $data['agendas']=$agendas;
                if($agenda==0){
                    $agenda=$agendas[0]->id;
                }
            }catch(Exception $err){
                //echo $err->getMessage();
            }
        }        
        $data['pagina']=$pagina;
        $total_registros=0;
        $registros=10; // numero de registro a mostrar por pagina
        $num_paginas=5; // numeros maximo de paginas a mostrar en la lista del paginador
        $inicio = ($pagina-1) * $registros; // se calcula desde que registro se empieza a mostrar
        $finreg=$inicio+$registros; // se calcula hasta que registro se muestra
        $pagina_intervalo=ceil($num_paginas/2)-1;
        $pagina_desde=$pagina-$pagina_intervalo; 
        $pagina_hasta=$pagina+$pagina_intervalo;
        $data['pagina_intervalo']=$pagina_intervalo;
        $data['pagina_desde']=$pagina_desde;
        $data['pagina_hasta']=$pagina_hasta;
        if($agenda>0){
            $arraydata=$this->ajax_listar_citas_agenda($pagina,$agenda);
        }else{
            $arraydata=$this->ajax_listar_citas($pagina,$tipousuario);    
        }
        $datos=array();
        if($arraydata['code']==200){
            $datos=$arraydata['data'];
            $total_registros=$arraydata['count'];
        }
        $data['agenda']=$agenda;
        $total_paginas = ceil($total_registros / $registros); // calculo de total de paginas.
        $total_paginas=($total_paginas!=0)?$total_paginas:1;
        $data['data']=$datos;
        $data['total_paginas']=$total_paginas;
        if($tipousuario){//true si es funcionario false si es un ciudadano
            //$idagenda=(isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
            $data['idagenda'] = $agenda;
            $data['title'] = 'Mi Agenda';
            $data['sidebar']='miagenda';
            $data['content'] = 'tramites/miagenda_funcionario';
            $this->load->view('themes/default/template', $data);

        }else{
            $data['title'] = 'Mi Agenda';
            $data['sidebar']='miagenda';
            $data['content'] = 'tramites/miagenda';
            $this->load->view('themes/default/template', $data);
        }
    }
    private function obtenerAgendas($owner){
        $result=array();
        try{
            $uri=$this->base_services.''.$this->context.'calendars/listByOwner/'.$owner;
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain              // heder de domain
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
                    $result[]=$tmp;
                }
            }
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
        $usuario= Doctrine::getTable('Usuario')->findByid($owner);
        foreach($usuario[0]->GruposUsuarios as $g){
            try{
                $uri=$this->base_services.''.$this->context.'calendars/listByOwner/'.$g->id;
                $response = \Httpful\Request::get($uri)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,              // heder de la app key
                        'domain' => $this->domain              // heder de domain
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
                        $result[]=$tmp;
                    }
                }
            }catch(Exception $err){
                throw new Exception($err->getMessage());
            }
        }
        return $result;
    }
    public function disponibles() {

        //$orderby=$this->input->get('orderby')?$this->input->get('orderby'):'nombre';
        //$direction=$this->input->get('direction')?$this->input->get('direction'):'asc';
        
        $data['procesos']=Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),'nombre','asc');
        
        //$data['orderby']=$orderby;
        //$data['direction']=$direction;
        $data['sidebar']='disponibles';
        $data['content'] = 'tramites/disponibles';
        $data['title'] = 'Trámites disponibles a iniciar';

        $config =Doctrine::getTable('CuentaHasConfig')->findOneByIdparAndCuentaId(1,Cuenta::cuentaSegunDominio()->id); 
        if($config){
           $config =Doctrine::getTable('Config')->findOneByIdAndIdparAndCuentaIdOrCuentaId($config->config_id,$config->idpar,Cuenta::cuentaSegunDominio()->id,0);
           $nombre = $config->nombre;
           if ($nombre=='default'){
                $data['template_path'] = 'uploads/themes/default/';
                $this->load->view('themes/default/template', $data);
           } else {
                $data['template_path'] = 'uploads/themes/'.Cuenta::cuentaSegunDominio()->id.'/'.$nombre.'/';
                $this->load->view('themes/'.Cuenta::cuentaSegunDominio()->id.'/'.$nombre.'/template', $data);
           }
           
        }else{
           $data['template_path'] = 'uploads/themes/default/';
           $this->load->view('themes/default/template', $data);
        }
    }

    public function iniciar($proceso_id) {
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        //echo UsuarioSesion::usuario()->id;
        //exit;
        if(!$proceso->canUsuarioIniciarlo(UsuarioSesion::usuario()->id)){
            echo 'Usuario no puede iniciar este proceso';
            exit;
        }
        
        //Vemos si es que usuario ya tiene un tramite de proceso_id ya iniciado, y que se encuentre en su primera etapa.
        //Si es asi, hacemos que lo continue. Si no, creamos uno nuevo
        $tramite=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso p, t.Etapas e, e.Tramite.Etapas hermanas')
                ->where('t.pendiente=1 AND p.id = ? AND e.usuario_id = ?',array($proceso_id, UsuarioSesion::usuario()->id))
                ->groupBy('t.id')
                ->having('COUNT(hermanas.id) = 1')
                ->fetchOne();
        
        if(!$tramite){
            $tramite=new Tramite();
            $tramite->iniciar($proceso->id);
        }  
        
    
        $qs=$this->input->server('QUERY_STRING');
        redirect('etapas/ejecutar/'.$tramite->getEtapasActuales()->get(0)->id.($qs?'?'.$qs:''));
    }
    
    public function eliminar($tramite_id){
        $tramite=Doctrine::getTable('Tramite')->find($tramite_id);
                
        if($tramite->Etapas->count()>1){
            echo 'Tramite no se puede eliminar, ya ha avanzado mas de una etapa';
            exit;
        }
        
        if(UsuarioSesion::usuario()->id!=$tramite->Etapas[0]->usuario_id){
            echo 'Usuario no tiene permisos para eliminar este tramite';
            exit;
        }
        
        $tramite->delete();
        redirect($this->input->server('HTTP_REFERER'));
    }
    public function ajax_canclar_cita_funcionario($idcita){
        $data['idcita']=$idcita;
        $this->load->view ('tramites/ajax_cancelar_cita_funcionario', $data);
    }
    public function ajax_cancelarCita($appoint_id){
        $code=0;
        $mensaje='';
        try{
            $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
            $nombre=UsuarioSesion::usuario()->nombres.' '.UsuarioSesion::usuario()->apellido_paterno.' '.UsuarioSesion::usuario()->apellido_materno;
            $motivo=(isset($_GET['motivo']))?$_GET['motivo']:'Cancelado por el ciudadano';
            $json='{
                "cancelation_cause": "'.$motivo.'",
                "user_id_cancel": "'.$id.'",
                "user_name_cancel": "'.$nombre.'"
                }';
            $uri=$this->base_services.''.$this->context.'appointments/cancel/'.$appoint_id;//url del servicio con los parametros
            $response = \Httpful\Request::put($uri)
                ->expectsJson()
                ->body($json)
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body->response->code) && $response->body->response->code==200){
                $code=$response->body->response->code;
                $mensaje='Ok';
            }else{
                $code=$response->body->response->code;
                $mensaje=$response->body->response->message;
                switch($code){
                    case "2060":
                        $mensaje='No se puede cancelar la cita porque ya excedio el tiempo minimo para cancelarla';
                    break;
                }
                //$mensaje='No se pudo cancelar la cita intentelo mas tarde.';
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $array=array('code'=>$code,'message'=>$mensaje);
        echo json_encode($array);
    }
    public function diasFeriados(){
        $code=0;
        $mensaje='';
        $data=array();
        if(!isset(UsuarioSesion::usuario()->cuenta_id) || !is_numeric(UsuarioSesion::usuario()->cuenta_id)){
            $idtramite=(isset($_GET['tram']))?intval($_GET['tram']):0;
            try{
                $result = Doctrine_Query::create ()
                ->select('p.cuenta_id')
                ->from ('Proceso p,Tramite t,Etapa e')
                ->where ("t.proceso_id=p.id AND e.tramite_id=tramite.id AND e.id=?",$idtramite)
                ->execute ();
                $cuenta=(isset($result[0]->cuenta_id) && is_numeric($result[0]->cuenta_id))?$result[0]->cuenta_id:1;

                $service=new Connect_services();
                $service->setCuenta($cuenta);
                $service->load_data();
                $this->domain=$service->getDomain();
                $this->appkey=$service->getAppkey();
            }catch(Exception $err){

            }
        }
        try{
            $uri=$this->base_services.''.$this->context.'daysOff';//url del servicio con los parametros
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,             // heder de la app key
                    'domain' => $this->domain                              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                $code=$response->body[0]->response->code;
                $mensaje=$response->body[0]->response->message;
                foreach($response->body[1]->daysoff as $item){
                    $tmp=date('d-m',strtotime($item->date_dayoff));
                    $data[]=array('date_dayoff'=>$tmp,'name'=>$item->name);
                }
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $array=array('code'=>$code,'message'=>$mensaje,'daysoff'=>$data);
        echo json_encode($array);
    }
    public function cargarCitasCalendar(){
        //date_default_timezone_set('America/Bogota');
        $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
        $data=array();
        try{
            $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
            $uri=$this->base_services.''.$this->context.'appointments/listByOwner/'.$id;//url del servicio con los parametros
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                foreach($response->body[1]->appointments as $item){
                    $timetmp=strtotime($item->appointment_time);
                    $start=$timetmp*1000;
                    $end=strtotime('+12 minute',$timetmp)*1000;
                    $title=$item->applyer_name;
                    $data[]=array('id'=>$item->appointment_id,'title'=>$title,'url'=>'#','class'=>'event-info','start'=>$start,'end'=>$end);                  
                }
            }else{
                $mensaje='No se pudo cancelar la cita intentelo mas tarde.';

            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $result=array('success'=>1,'result'=>$data);
        echo json_encode($result);
    }
    /*public function ajax_confirmar_agregar_bloqueo(){
        $data['fechainicio']=$_GET['fecha1'];
        $data['fechafinal']=$_GET['fecha2'];
        $data['calendario']=$_GET['cmbagendabloq'];
        $this->load->view('tramites/ajax_confirmar_agregar_bloqueo', $data);
    }*/
    public function ajax_agregar_bloqueo(){
        $code=0;
        $mensaje='';
        $idagenda=(isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
        $fechainicio=(isset($_GET['fechainicio']))?$_GET['fechainicio']:'';
        $fechafinal=(isset($_GET['fechafinal']))?$_GET['fechafinal']:'';
        $causa=(isset($_GET['razon']))?$_GET['razon']:'';
        $fechainicio=date(DATE_ATOM,strtotime($fechainicio));
        $fechafinal=date(DATE_ATOM,strtotime($fechafinal));
        $id=(isset(UsuarioSesion::usuario()->id))?UsuarioSesion::usuario()->id:0;
        $usuario=(isset(UsuarioSesion::usuario()->usuario))?UsuarioSesion::usuario()->usuario:'';
        $json='{
            "calendar_id":"'.$idagenda.'",
            "cause":"'.$causa.'",
            "end_date":"'.$fechafinal.'",
            "start_date":"'.$fechainicio.'",
            "user_id_block":"'.$id.'",
            "user_name_block":"'.$usuario.'"
        }';
        if($idagenda>0){
            try{
                $uri=$this->base_services.''.$this->context.'blockSchedules';//url del servicio con los parametros
                $response = \Httpful\Request::post($uri)
                    ->body($json)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,              // heder de la app key
                        'domain' => $this->domain              // heder de domain
                    ))
                    ->sendIt();
                $code=$response->code;
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    $code=$response->body[0]->response->code;
                    $mensaje=$response->body[0]->response->message;
                }else{
                    if(isset($response->body->response->code) && is_numeric($response->body->response->code)){
                        $code=$response->body->response->code;
                        switch($response->body->response->code){
                            case '2090':
                                $mensaje='No puede bloquear una fecha/hora menor a la actual';
                            break;
                            default:
                                $mensaje=(isset($response->body->response->message))?$response->body->response->message:'No se pudo bloquear intentelo mas tarde.';
                            break;
                        }
                    }else{
                        $mensaje='No se pudo bloquear vuelvalo a intentar, si el problema persiste contacte con el administrador';
                    }
                }
            }catch(Exception $err){
                $mensaje='No se pudo bloquear vuelvalo a intentar, si el problema persiste contacte con el administrador';
            }
        }
        echo json_encode(array('code'=>$code,'mensaje'=>$mensaje));
    }
    private function convertirFechaFormatoBloqueo($fe){
        $tmp=explode(' ',$fe);
        $tmpf=explode('/',$tmp[0]);
        $fecha=$tmpf[2].'-'.$tmpf[1].$tmpf[0].' '.$tmp[1];
        return $fecha;
    }
    public function disponibilidad($idagenda=0){
        //date_default_timezone_set('America/Bogota');
        $code=0;
        $mensaje='';
        $data=array();
        $tramite='';
        try{
            $tiempofin=$this->obtenerTiempoCita($idagenda);
            $uri=$this->base_services.''.$this->context.'appointments/availability/'.$idagenda;//url del servicio con los parametros
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,             // heder de la app key
                    'domain' => $this->domain                              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                $code=$response->body[0]->response->code;
                $mensaje=$response->body[0]->response->message;
                $concurrency=$response->body[1]->concurrency;
                $usuario=Doctrine_Query::create()
                ->from("Campo")
                ->where('agenda_campo='.$idagenda)
                ->execute();
                foreach($usuario as $ob){
                    $tramite=$ob->etiqueta;
                }
                foreach($response->body[1]->appointmentsavailable as $keyd => $item){
                    $object=get_object_vars($item);
                    if(is_array($object) && count($object)>0){
                        $fecha=$keyd;
                        foreach($object as $keyhora => $dispo){
                            $index=1;
                            foreach($dispo as $appontment){
                                $id=$appontment->applyer_name;
                                $tmp=strtotime($fecha.' '.$keyhora.':00',time());
                                $end=strtotime('+'.$tiempofin.' minute',strtotime ($fecha.' '.$keyhora.':00'))*1000;
                                $start=$tmp*1000;
                                $title=$appontment->applyer_name;
                                $available=$appontment->available;
                                $correo=$appontment->applyer_email;
                                $clsevent='';
                                switch($available){
                                    case 'D':
                                        $clsevent='event-warning';
                                        $title='Disponible';
                                    break;
                                    case 'R':
                                        $clsevent='event-info';
                                        $title='Reservado';
                                    break;
                                    case 'B':
                                        $clsevent='event-success';
                                        $title='Bloqueado';
                                    break;
                                }
                                $title=$title.' '.$fecha.' '.$keyhora.':00 '.$id.' '.$correo;
                                $fechahora=date('d/m/Y',strtotime($fecha)).' '.$keyhora.':00 ';
                                $fechap=date('d/m/Y',strtotime($fecha));
                                $hora=$keyhora.':00 ';
                                $cita=$appontment->appointment_id;
                                //$cita=0;
                                //$data[]=array('id'=>$id,'title'=>$title,'url'=>'#','class'=>$clsevent,'start'=>$start,'end'=>$end,'estado'=>$available,'concurrencia'=>$concurrency,'cuenta'=>$index,'correo'=>$correo,'block_id'=>$appontment->block_id,'tramite'=>$tramite,'fechahora'=>$fechahora);
                                $data[]=array('id'=>$id,'title'=>$title,'url'=>'#','class'=>$clsevent,'start'=>$start,'end'=>$end,'estado'=>$available,'concurrencia'=>$concurrency,'cuenta'=>$index,'correo'=>$correo,'block_id'=>$appontment->block_id,'tramite'=>$tramite,'fecha'=>$fechap,'hora'=>$hora,'cita'=>$cita);
                                $index++;
                            }
                        }
                    }
                    
                }
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
            print $mensaje;
        }
        $result=array('success'=>1,'result'=>$data);
        echo json_encode($result);
    }
    private function obtenerTiempoCita($idagenda){
        $valor=0;
        try{
            $uri=$this->base_services.''.$this->context.'calendars/'.$idagenda;//url del servicio con los parametros
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,             // heder de la app key
                    'domain' => $this->domain             // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                $valor=$response->body[1]->calendars[0]->time_attention;
            }
        }catch(Exception $err){

        }
        return $valor;
    }
    public function bloqueo(){
        $start=(isset($_GET['start']))?$_GET['start']:0;
        $end=(isset($_GET['end']))?$_GET['end']:0;
        $id=(isset($_GET['id']))?$_GET['id']:0;
        $data['start']=$start;
        $data['end']=$end;
        $data['id']=$id;
        $this->load->view('tramites/ajax_confirmar_agregar_bloqueo', $data);
    }
    public function desbloqueo(){
        $data['id']=(isset($_GET['id']) && is_numeric($_GET['id']))?$_GET['id']:0;
        $this->load->view('tramites/ajax_confirmar_eliminar_bloqueo', $data);
    }
    public function ajax_eliminar_bloqueo(){
        $code=0;
        $mensaje=0;
        $idbloqueo=(isset($_GET['id']) && is_numeric($_GET['id']))?$_GET['id']:0;
        try{
            $uri=$this->base_services.''.$this->context.'blockSchedules/'.$idbloqueo;//url del servicio con los parametros
            $response = \Httpful\Request::delete($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,             // heder de la app key
                    'domain' => $this->domain                              // heder de domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && isset($response->body->response->code) && $response->body->response->code==200){
                $code=$response->body->response->code;
                $mensaje=$response->body->response->message;
            }else{
                $code=0;
                $mensaje='No se pudo eliminar el bloqueo por favor vuelva a intentarlo, si el problema persiste informe al administrador.';
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        echo json_encode(array('code'=>$code,'mensaje'=>$mensaje));
    }

}
