<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Etapas extends MY_Controller {
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
        $cuenta=(isset(UsuarioSesion::usuario()->cuenta_id) && is_numeric(UsuarioSesion::usuario()->cuenta_id))?UsuarioSesion::usuario()->cuenta_id:1;
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

    public function inbox() {
        $buscar = $this->input->get('buscar');
        $orderby=$this->input->get('orderby')?$this->input->get('orderby'):'updated_at';
        $direction=$this->input->get('direction')?$this->input->get('direction'):'desc';
                        
        $matches="";
        $rowetapas="";
        $resultotal="";
        
        if ($buscar) { 
            $this->load->library('sphinxclient');
            $this->sphinxclient->setServer ( $this->config->item ( 'sphinx_host' ), $this->config->item ( 'sphinx_port' ) );
            $this->sphinxclient->SetLimits(0, 10000);
            $result = $this->sphinxclient->query(json_encode($buscar), 'tramites');             
            if($result['total'] > 0 ){            
                $resultotal="true";          
            }else{               
                $resultotal="false";
            }
        }

        if($resultotal=="true"){
            $matches = array_keys($result['matches']); 
            $rowetapas=Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),$orderby,$direction, $matches, $buscar);
        }else{
            $rowetapas=Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),$orderby,$direction, "0", $buscar);
        }
        
        $data['etapas'] =$rowetapas;
        $data['buscar']= $buscar;
        $data['orderby']=$orderby;
        $data['direction']=$direction;
        $data['sidebar'] = 'inbox';
        $data['content'] = 'etapas/inbox';
        $data['title'] = 'Bandeja de Entrada';
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
    
    public function sinasignar($offset=0) {                
        if (!UsuarioSesion::usuario()->registrado) {
            $this->session->set_flashdata('redirect', current_url());
            redirect('autenticacion/login');
        }
        
        $this->load->library('pagination');        
        $buscar = $this->input->get('query');        
        
        $matches="";
        $rowetapas="";
        $resultotal=false;
        $contador="0";        
        $perpage=50;
        
        if ($buscar) { 
            $this->load->library('sphinxclient');
            $this->sphinxclient->setServer ( $this->config->item ( 'sphinx_host' ), $this->config->item ( 'sphinx_port' ) );
            $this->sphinxclient->SetLimits($offset, 10000);
            $result = $this->sphinxclient->query(json_encode($buscar), 'tramites');             
            if($result['total'] > 0 ){            
                $resultotal=true;
            }else{               
                $resultotal=false;
            }
        }

        if($resultotal==true){
            $matches = array_keys($result['matches']); 
            $contador = Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),$matches,$buscar,0,$perpage)->count();
            $rowetapas=Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),$matches,$buscar,0,$perpage);
            error_log("true" . " cantidad " .$contador);
            
        }else{            
            $contador = Doctrine::getTable('Etapa')->findAllSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio())->count();
            $rowetapas= Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio(),"0",$buscar,$offset,$perpage);
            error_log("false" . " cantidad " .$contador);
        }
        
        $config['base_url'] = site_url('etapas/sinasignar');
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
        //$data['etapas'] = Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio());
        $data['links'] = $this->pagination->create_links(); 
        $data['etapas'] =$rowetapas;        
        $data['query'] = $buscar;
        $data['sidebar'] = 'sinasignar';
        $data['content'] = 'etapas/sinasignar';
        $data['title'] = 'Sin Asignar';        
        $this->load->view('template', $data);
    }

    public function ejecutar($etapa_id, $secuencia = 0) {
        $iframe = $this->input->get('iframe');
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if(!$etapa){
            show_404();
        }
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
        } else if (($etapa->Tarea->final || !$etapa->Tarea->paso_confirmacion) && $paso->getReadonly() && end($etapa->getPasosEjecutables()) == $paso) { //No se requiere mas input
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
    }

    private function confirmar_cita($id){
        try{
            $uri=$this->base_services.''.$this->context.'appointments/confirm/'.$id;
            $response = \Httpful\Request::put($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,
                    'domain' => $this->domain
                ))
                ->sendIt();
            $code=$response->code;
            if($code==200){
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    $code=$response->body[0]->response->code;
                    $appointment=$response->body[1]->id;
                }
            }else{
                throw new Exception('La cita reservada ya no esta disponible, reserve una nueva hora.');
            }
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
    }
    private function confirmar_citas_grupo($ids){
        try{
            $uri=$this->base_services.''.$this->context.'appointments/bulkConfirm/';
            $response = \Httpful\Request::post($uri)
                ->body($ids)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,
                    'domain' => $this->domain
                ))
                ->sendIt();
            $code=$response->code;
            if($code==200){
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    $code=$response->body[0]->response->code;
                    $appointment=$response->body[1]->id;
                }
            }else{
                throw new Exception('La cita reservada ya no esta disponible. Por favor, reserve una nueva hora.');
            }
        }catch(Exception $err){
            throw new Exception($err->getMessage());
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
                if ($c->isEditableWithCurrentPOST($etapa_id)) {
                    $c->formValidate($etapa->id);
                    $validar_formulario = TRUE;                    
                }
            }
            if (!$validar_formulario || $this->form_validation->run() == TRUE) {
                //Almacenamos los campos
                foreach ($formulario->Campos as $c) {
                    //Almacenamos los campos que no sean readonly y que esten disponibles (que su campo dependiente se cumpla)

                    if ($c->isEditableWithCurrentPOST($etapa_id)) {
                        $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($c->nombre, $etapa->id);
                        if (!$dato)
                            $dato = new DatoSeguimiento();
                        $dato->nombre = $c->nombre;
                        $dato->valor = $this->input->post($c->nombre)=== false?'' :  $this->input->post($c->nombre);
                        
                        if(!is_object($dato->valor) && !is_array($dato->valor)){                            
                                if(preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)){
                                    $dato->valor=preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i","$3-$2-$1",$dato->valor);
                                }
                            }
                        
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

        $respuesta = new stdClass();
        //$etapa->avanzar($this->input->post('usuarios_a_asignar'));
        $respuesta->validacion = TRUE;
        try{
            $appointments=$this->obtener_citas_de_tramite($etapa_id);
            if(isset($appointments) && is_array($appointments) && (count($appointments)>=1) ){
                $json='{"ids":[';
                $i=0;

                $rstramite = Doctrine_Query::create ()
                        ->select('tramite_id')
                        ->from ('Etapa')
                        ->where ("id=?",$etapa_id)
                        ->execute ();
                foreach($rstramite as $obj){
                    $idtramite=$obj->tramite_id;
                }
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

                foreach($appointments as $item){
                    if($i==0){
                        $json=$json.'"'.$item.'"';
                    }else{
                        $json=$json.',"'.$item.'"';
                    }
                    $i++;
                }
                
                $json=$json.']}';
                $this->confirmar_citas_grupo($json);
                $etapa->avanzar($this->input->post('usuarios_a_asignar'));
            }else{
                $etapa->avanzar($this->input->post('usuarios_a_asignar'));    
            }
        }catch(Exception $err){
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'.$err->getMessage().'</div>';
            log_message('error',$err->getMessage());
        }

        
        //$respuesta->validacion = TRUE;
        //$respuesta->validacion = false;
        
        if ($this->input->get('iframe')){
            $respuesta->redirect = site_url('etapas/ejecutar_exito');
        }
        else
            $respuesta->redirect = site_url();

        echo json_encode($respuesta);
    }
    private function obtener_citas_de_tramite($etapa){
        //SELECT tramite_id FROM etapa WHERE id=104
        $result=array();
        try{
            $rstramite = Doctrine_Query::create ()
                    ->select('tramite_id')
                    ->from ('Etapa')
                    ->where ("id=?",$etapa)
                    ->execute ();
            $idtramite=0;
            foreach($rstramite as $obj){
                $idtramite=$obj->tramite_id;
            }
            $rsvalores = Doctrine_Query::create ()
                    ->select('ds.valor')
                    ->from ('DatoSeguimiento ds,Etapa e')
                    ->where ("ds.etapa_id=e.id AND e.tramite_id=?",$idtramite)
                    ->execute ();
            foreach($rsvalores as $obj2){
                if(isset($obj2->valor)){
                    $val=str_replace('"','', $obj2->valor);
                    $val=trim($val);
                    if(is_string($val) && !empty($val)){
                        $arrval=explode('_',$val);
                        if(isset($arrval[1])){
                            if($this->validateDate($arrval[1])){
                                $result[]=$arrval[0];
                            }
                        }
                    }
                }
            }
            return $result;
        }catch(Exception $err){
            throw new Exception('No se pudo confirmar si en su proceso existen citas, vuelva a intentarlo');
        }
    }
    private function validateDate($date, $format = 'Y-m-d'){
        try{
            $val=explode("-",$date);
            if(isset($val[0]) && isset($val[1]) && isset($val[2])){
                if(checkdate($val[1],$val[2],$val[0])){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }catch(Exception $err){
            return false;
        }
        //return true;
        //return is_date($date, $format);
        /*$d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;*/
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

    public function descargar($tramites){
        $data['tramites'] = $tramites;
        $this->load->view ('etapas/descargar',$data);
    }
    
    public function descargar_form(){
        if(!Cuenta::cuentaSegunDominio()->descarga_masiva){
            echo 'Servicio no tiene permisos para descargar.';
            exit;
        }

        if(!UsuarioSesion::usuario()->registrado){
            echo 'Usuario no tiene permisos para descargar.';
            exit;
        }
        $tramites = $this->input->post('tramites');
        $opcionesDescarga = $this->input->post('opcionesDescarga');
        $tramites = explode(",",$tramites);
        $ruta_documentos = 'uploads/documentos/';
        $ruta_generados = 'uploads/datos/';
        $ruta_tmp = 'uploads/tmp/';
        $fecha = new DateTime ();
        $fecha = date_format($fecha,"Y-m-d");

        $tipoDocumento = "";
        switch ($opcionesDescarga) {
            case 'documento':
                $tipoDocumento = 'documento';
                break;
            case 'dato':
                $tipoDocumento = 'dato';
                break;
        }

        //Recorriendo los trámites
        $this->load->library('zip');
        foreach ($tramites as $t){

            if(empty($tipoDocumento)){
                $files =Doctrine::getTable('File')->findByTramiteId($t);
            }else{
                $files =Doctrine::getTable('File')->findByTramiteIdAndTipo($t,$tipoDocumento);
            }
            if(count($files) > 0){
                //Recorriendo los archivos
                foreach ($files as $f) {
                    $tr = Doctrine::getTable('Tramite')->find($t);
                    $participado = $tr->usuarioHaParticipado(UsuarioSesion::usuario()->id);
                    if(!$participado){
                        echo 'Usuario no ha participado en el trámite.';
                        exit;
                    }
                    $nombre_documento = $tr->id;
                    $tramite_nro ='';
                    foreach ($tr->getValorDatoSeguimiento() as $tra_nro){
                        if($tra_nro->valor == $f->filename){
                            $nombre_documento = $tra_nro->nombre;
                        }
                        if($tra_nro->nombre == 'tramite_ref'){
                            $tramite_nro = $tra_nro->valor;
                        }
                    }
                    if($f->tipo == 'documento' && !empty($nombre_documento)){
                        $path = $ruta_documentos.$f->filename;
                        $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                        $tramite_nro = str_replace(" ","",$tramite_nro);
                        $nombre_archivo = pathinfo($path, PATHINFO_FILENAME);
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $new_file = $ruta_tmp.$nombre_documento.".".$nombre_archivo.".".$tramite_nro.".".$ext;
                        copy($path,$new_file);
                        $this->zip->read_file($new_file);
                        //Eliminación del archivo para no ocupar espacio en disco
                        unlink($new_file);
                    }elseif ($f->tipo == 'dato' && !empty($nombre_documento)){
                        $path = $ruta_generados.$f->filename;
                        $this->zip->read_file($path);
                    }
                }
                if(count($tramites) > 1){
                    $tr = Doctrine::getTable('Tramite')->find($t);
                    $tramite_nro ='';
                    foreach ($tr->getValorDatoSeguimiento() as $tra_nro){
                        if($tra_nro->nombre == 'tramite_ref'){
                            $tramite_nro = $tra_nro->valor;
                        }
                    }
                    $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                    $nombre = $fecha."_".$t."_".$tramite_nro;
                    //creando un zip por cada trámite
                    $this->zip->archive($ruta_tmp.$nombre.'.zip');
                    $this->zip->clear_data();
                }
            }
        }
        if(count($tramites) > 1){
            foreach ($tramites as $t){
                $tr = Doctrine::getTable('Tramite')->find($t);
                $tramite_nro ='';
                foreach ($tr->getValorDatoSeguimiento() as $tra_nro){
                   if($tra_nro->nombre == 'tramite_ref'){
                        $tramite_nro = $tra_nro->valor;
                    }                              
                }                         
                $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                $nombre = $fecha."_".$t."_".$tramite_nro;
                $this->zip->read_file($ruta_tmp.$nombre.'.zip');
            }
            
            //Eliminando los archivos antes de descargar
            foreach ($tramites as $t){;
                $tr = Doctrine::getTable('Tramite')->find($t);
                $tramite_nro ='';
                foreach ($tr->getValorDatoSeguimiento() as $tra_nro){
                   if($tra_nro->nombre == 'tramite_ref'){
                        $tramite_nro = $tra_nro->valor;
                    }                              
                }                         
                $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
                $nombre = $fecha."_".$t."_".$tramite_nro;
                unlink($ruta_tmp.$nombre.'.zip');
            }
            $this->zip->download('tramites.zip');
        }else{
            $tr = Doctrine::getTable('Tramite')->find($tramites);
            $tramite_nro ='';
            foreach ($tr->getValorDatoSeguimiento() as $tra_nro){
               if($tra_nro->nombre == 'tramite_ref'){
                    $tramite_nro = $tra_nro->valor;
                }                              
            }                         
            $tramite_nro = $tramite_nro != '' ? $tramite_nro : $tr->Proceso->nombre;
            $nombre = $fecha."_".$t."_".$tramite_nro;
            $this->zip->download($nombre.'.zip');
        }
    }
    public function ajax_modal_calendar(){
        $idagenda=(isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
        $idobject=(isset($_GET['object']) && is_numeric($_GET['object']))?$_GET['object']:0;
        $idcita=(isset($_GET['idcita']) && is_numeric($_GET['idcita']))?$_GET['idcita']:0;
        $idtramite=(isset($_GET['idtramite']) && is_numeric($_GET['idtramite']))?$_GET['idtramite']:0;
        $etapa=(isset($_GET['etapa']) && is_numeric($_GET['etapa']))?$_GET['etapa']:0;
        $data['idagenda']=$idagenda;
        $data['idobject']=$idobject;
        $data['idcita']=$idcita;
        $data['idtramite']=$idtramite;
        $data['etapa']=$etapa;
        $this->load->view ('etapas/calendario_ciudadano',$data);
    }
    private function obtenerTiempoCita($idagenda){
        $valor=0;
        try{
            $uri=$this->base_services.''.$this->context.'calendars/'.$idagenda; //url del servicio con los parametros
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,
                    'domain' => $this->domain
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
    public function disponibilidad($idagenda,$idtramite){
        $code=0;
        $mensaje='';
        $data=array();
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

            $tiempofin=$this->obtenerTiempoCita($idagenda);
            //$uri=$this->base_services.''.$this->context.'appointments/availability/'.$idagenda;//url del servicio con los parametros
            $date=(isset($_GET['date']))?$_GET['date']:'';
            if(!empty($date)){
                //echo $date;
                $uri=$this->base_services.''.$this->context.'appointments/availability/'.$idagenda.'?date='.$date;//url del servicio con los parametros
            }else{
                $uri=$this->base_services.''.$this->context.'appointments/availability/'.$idagenda;//url del servicio con los parametros
            }
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,
                    'domain' => $this->domain
                ))
                ->sendIt();
            $code=$response->code;
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                $code=$response->body[0]->response->code;
                $mensaje=$response->body[0]->response->message;
                $concurrency=$response->body[1]->concurrency;
                $fechasagregadas=array();
                foreach($response->body[1]->appointmentsavailable as $keyd => $item){
                    $object=get_object_vars($item);
                    if(is_array($object) && count($object)>0){
                        $fecha=$keyd;
                        foreach($object as $keyhora => $dispo){
                            $index=1;
                            if($concurrency>1){// se verifica si la concurrencia es mayor a 1
                                foreach($dispo as $appontment){
                                    //$id=$appontment->applyer_name;
                                    $tmp=strtotime($fecha.' '.$keyhora.':00',time());
                                    $end=strtotime('+'.$tiempofin.' minute',strtotime ($fecha.' '.$keyhora.':00'))*1000;
                                    $start=$tmp*1000;
                                    $id=$start;
                                    $title=$appontment->applyer_name;
                                    $available=$appontment->available;
                                    $clsevent='';
                                    switch($available){
                                        case 'D':
                                            $clsevent='event-warning';
                                            $title='Disponible';
                                        break;
                                        case 'R':
                                            //$clsevent='event-info';
                                            $clsevent='event-success';
                                            $title='Reservado';
                                        break;
                                        case 'B':
                                            $clsevent='event-success';
                                            $title='Bloqueado';
                                        break;
                                    }
                                    $title=$title.' '.$fecha.' '.$keyhora.':00';
                                    if(!in_array($start,$fechasagregadas)){
                                        $fechasagregadas[]=$start;
                                        $data[]=array('id'=>$id,'title'=>$title,'url'=>'#','class'=>$clsevent,'start'=>$start,'end'=>$end,'estado'=>$available,'concurrencia'=>$concurrency,'cuenta'=>$index);
                                        $index++;
                                    }else{

                                        if($available=='D'){
                                            $clave = array_search($start, $fechasagregadas);
                                            $data[$clave]=array('id'=>$id,'title'=>$title,'url'=>'#','class'=>$clsevent,'start'=>$start,'end'=>$end,'estado'=>$available,'concurrencia'=>$concurrency,'cuenta'=>$index);
                                        }
                                    }
                                }
                            }else{
                                foreach($dispo as $appontment){
                                    $id=$appontment->applyer_name;
                                    $tmp=strtotime($fecha.' '.$keyhora.':00',time());
                                    $end=strtotime('+'.$tiempofin.' minute',strtotime ($fecha.' '.$keyhora.':00'))*1000;
                                    $start=$tmp*1000;
                                    $title=$appontment->applyer_name;
                                    $available=$appontment->available;
                                    $clsevent='';
                                    switch($available){
                                        case 'D':
                                            $clsevent='event-warning';
                                            $title='Disponible';
                                        break;
                                        case 'R':
                                            //$clsevent='event-info';
                                            $clsevent='event-success';
                                            $title='Reservado';
                                        break;
                                        case 'B':
                                            $clsevent='event-success';
                                            $title='Bloqueado';
                                        break;
                                    }
                                    $title=$title.' '.$fecha.' '.$keyhora.':00';
                                    $data[]=array('id'=>$id,'title'=>$title,'url'=>'#','class'=>$clsevent,'start'=>$start,'end'=>$end,'estado'=>$available,'concurrencia'=>$concurrency,'cuenta'=>$index);
                                    $index++;
                                }
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
    public function ajax_confirmar_agregar_dia(){
        $idagenda=(isset($_GET['idagenda']))?$_GET['idagenda']:0;
        $idtramite=(isset($_GET['idtramite']))?$_GET['idtramite']:0;
        $etapa=(isset($_GET['etapa']))?$_GET['etapa']:0;
        $fecha=(isset($_GET['fecha']))?$_GET['fecha']:'';
        $hora=(isset($_GET['hora']))?$_GET['hora']:'';
        $idcita=(isset($_GET['idcita']))?$_GET['idcita']:'';
        $fechaf=(isset($_GET['fechaf']))?$_GET['fechaf']:'';
        $horaf=(isset($_GET['horaf']))?$_GET['horaf']:'';
        $object=(isset($_GET['obj']))?$_GET['obj']:0;
        $tmp=explode('-',$fecha);
        $data['dia']=$tmp[2];
        $data['mes']=$tmp[1];
        $data['ano']=$tmp[0];
        $data['hora']=$hora;
        $data['idagenda']=$idagenda;
        $data['idcita']=$idcita;
        $data['object']=$object;
        $data['idtramite']=$idtramite;
        $data['etapa']=$etapa;
        $data['fechafinal']=$fechaf.' '.$horaf;
        $this->load->view ('etapas/ajax_confirmar_agregar_dia',$data);
    }
    public function ajax_agregar_cita(){
        //date_default_timezone_set('America/Bogota');
        $id=UsuarioSesion::usuario()->id;
        $nombre=UsuarioSesion::usuario()->nombres.' '.UsuarioSesion::usuario()->apellido_paterno.' '.UsuarioSesion::usuario()->apellido_materno;
        $trimNombre = trim($nombre);
        $nombre=(!empty($trimNombre))?$nombre:'Cliente';
        $idagenda=(isset($_GET['idagenda']))?$_GET['idagenda']:'';
        $tzz=(isset($_GET['tzz']))?$_GET['tzz']:'';
        $fecha=(isset($_GET['fecha']))?$_GET['fecha']:'';
        $fechafinal=(isset($_GET['fechafinal']))?$_GET['fechafinal']:'';
        $desc=(isset($_GET['desc']))?$_GET['desc']:'';
        $email=(isset($_GET['email']) && !empty($_GET['email']))?$_GET['email']:UsuarioSesion::usuario()->email;
        $appointment=(isset($_GET['idcita']))?$_GET['idcita']:0;
        $idetapa=(isset($_GET['idtramite']))?$_GET['idtramite']:0;
        $obj=(isset($_GET['obj']))?$_GET['obj']:0;
        $code=0;
        /*$tmp=explode(' ',$fecha);
        $fe=explode('-',$tmp[0]);
        $ho=explode(':',$tmp[1]);
        $fechaformat=date(DATE_ATOM, mktime($ho[0],$ho[1],0,$fe[1],$fe[2],$fe[0]));*/
        $defaulTimeZone  = new DateTimeZone(date_default_timezone_get());//'America/Santiago'
        $browserTimeZone = empty($tzz)? $defaulTimeZone: new DateTimeZone($tzz);
        $fechaCal = new DateTime($fecha, $browserTimeZone);
        $fechaCal->setTimezone($defaulTimeZone);
        $fechaformat=$fechaCal->format('Y-m-d H:i:sP');

        $mensaje='';
        $nomproceso='';
        //echo 'checkpoint a: '.$idetapa;
        $et=Doctrine_Query::create()
                    ->from("Etapa")
                    ->where('id='.$idetapa)
                    ->execute();
        foreach($et as $ob){
            $idtramite=$ob->tramite_id;
        }
        $ttram= Doctrine::getTable('Tramite')->findByid($idtramite);
        $nomproceso=$ttram[0]->Proceso->nombre;
        $metavalue='{"tramite":"'.$idtramite.'","etapa":"'.$idetapa.'","nombre_tramite":"'.$nomproceso.'","calendario_id":"'.$idagenda.'","idcampo":"'.$obj.'" }';
        try{
            $result = Doctrine_Query::create ()
            ->select('cuenta_id')
            ->from ('proceso,tramite')
            ->where ("proceso.id=tramite.proceso_id AND tramite.id=?",array($idtramite))
            ->execute ();
            $cuenta=(isset($result[0]->cuenta_id) && is_numeric($result[0]->cuenta_id))?$result[0]->cuenta_id:1;

            $service=new Connect_services();
            $service->setCuenta($cuenta);
            $service->load_data();
            $this->domain=$service->getDomain();
            $this->appkey=$service->getAppkey();
        }catch(Exception $err){

        }
        $json='{
                "applyer_email": "'.$email.'",
                "applyer_id": "'.$id.'",
                "applyer_name": "'.$nombre.'",
                "appointment_start_time": "'.$fechaformat.'",
                "calendar_id": "'.$idagenda.'",
                "subject": "'.$desc.'"
                }';
        if($appointment>0){//0 reserva una cita y 1 edita una cita
            try{
                $uri=$this->base_services.''.$this->context.'appointments/'.$appointment;
                $responsever = \Httpful\Request::get($uri)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,
                        'domain' => $this->domain
                    ))
                    ->sendIt();
                $code=$responsever->code;
                if(isset($responsever->body) && is_array($responsever->body) && isset($responsever->body[0]->response->code) && ($responsever->body[0]->response->code==200) ){//Se verifica si existe la cita.
                    //Si la cita existe se procede a consumir el servicio de actualizar.
                    try{
                        $uri=$this->base_services.''.$this->context.'appointments/'.$appointment;
                        $response = \Httpful\Request::put($uri)
                            ->expectsJson()
                            ->body($json)
                            ->addHeaders(array(
                                'appkey' => $this->appkey,
                                'domain' => $this->domain
                            ))
                            ->sendIt();
                        $code=$response->code;
                        if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                            $code=$response->body[0]->response->code;
                            $mensaje=$response->body[0]->response->message;
                            $appointment=$response->body[1]->id;
                        }else{
                            $code=$response->body->response->code;
                            switch($code){
                                case "2040":
                                    $mensaje='El tiempo seleccionado no se encuentra disponible en el calendario.';
                                break;
                                case "1020":
                                    $mensaje='El email de la persona es requerido.';
                                break;
                            }
                        }
                    }catch(Exception $err){
                        $mensaje=$response->body[0]->response->message;
                    }
                }else{
                    //echo 'no existe';
                    //Si la cita no existe se procede a consumir el servicio de reservar
                    $json='{
                        "applyer_email": "'.$email.'",
                        "applyer_id": "'.$id.'",
                        "applyer_name": "'.$nombre.'",
                        "appointment_start_time": "'.$fechaformat.'",
                        "calendar_id": "'.$idagenda.'",
                        "subject": "'.$desc.'",
                        "metadata":'.$metavalue.'
                        }';
                    try{
                        $uri=$this->base_services.''.$this->context.'appointments/reserve';//url del servicio con los parametros
                        $response = \Httpful\Request::post($uri)
                            ->expectsJson()
                            ->body($json)
                            ->addHeaders(array(
                                'appkey' => $this->appkey,
                                'domain' => $this->domain
                            ))
                            ->sendIt();
                        $code=$response->code;
                        if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                            $code=$response->body[0]->response->code;
                            $mensaje=$response->body[0]->response->message;
                            $appointment=$response->body[1]->id;
                        }else{
                            $code=(isset($response->body->response->code))?$response->body->response->code:0;
                            $mensaje=(isset($response->body->response->message))?$response->body->response->message:'Error General';
                        }
                    }catch(Exception $err){
                        $mensaje='No se pudo reservar la cita, volverlo a intentar.';
                        //$mensaje=$response->body[0]->response->message;
                    }
                }
            }catch(Exception $err){

            }
        }else{
            $json='{
                "applyer_email": "'.$email.'",
                "applyer_id": "'.$id.'",
                "applyer_name": "'.$nombre.'",
                "appointment_start_time": "'.$fechaformat.'",
                "calendar_id": "'.$idagenda.'",
                "subject": "'.$desc.'",
                "metadata":'.$metavalue.'
                }';
            try{
                $uri=$this->base_services.''.$this->context.'appointments/reserve';//url del servicio con los parametros
                $response = \Httpful\Request::post($uri)
                    ->expectsJson()
                    ->body($json)
                    ->addHeaders(array(
                        'appkey' => $this->appkey,
                        'domain' => $this->domain
                    ))
                    ->sendIt();
                $code=$response->code;
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    $code=$response->body[0]->response->code;
                    $mensaje=$response->body[0]->response->message;
                    $appointment=$response->body[1]->id;
                }else{
                    $code=(isset($response->body->response->code))?$response->body->response->code:0;
                    $mensaje=(isset($response->body->response->message))?$response->body->response->message:'Error General';
                }
            }catch(Exception $err){
                $mensaje='No se pudo reservar la cita. Por favor, inténtelo de nuevo.';
            }
        }
        $tmpfecha=explode(' ',$fecha);
        $tmp=explode('-',$tmpfecha[0]);
        $fres=$tmp[2].'/'.$tmp[1].'/'.$tmp[0].' '.$tmpfecha[1];
        echo json_encode(array('code'=>$code,'mensaje'=>$mensaje,'appointment'=>$appointment,'fecha'=>$fres));
    }
}
