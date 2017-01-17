<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Etapas extends MY_Controller {

    public function __construct() {
        parent::__construct();        
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
                        $oldFilename = '';
                        $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($c->nombre, $etapa->id);
                        if (!$dato) {
                            $dato = new DatoSeguimiento();
                        } else {
                            $oldFilename = $dato->valor;
                        }
                        
                        $dato->nombre = $c->nombre;
                        $dato->valor = $this->input->post($c->nombre)=== false?'' :  $this->input->post($c->nombre);
                        
                        if(!is_object($dato->valor) && !is_array($dato->valor)){                            
                                if(preg_match('/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', $dato->valor)){
                                    $dato->valor=preg_replace("/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})/i","$3-$2-$1",$dato->valor);
                                }
                            }
                        
                        // Subir el archivo a Alfresco
                        if ($c->tipo == 'file' && $this->input->post($c->nombre) != '') {
                            $DS = DIRECTORY_SEPARATOR;
                            $upload_path = FCPATH . 'uploads' . $DS . 'datos' . $DS . $this->input->post($c->nombre);
                            if (file_exists($upload_path)) {                                
                                $cms = new Config_cms_alfresco();                                
                                $cms->setAccount($etapa->Tramite->Proceso->cuenta_id);
                                $cms->loadData();
                                
                                if ($cms->getCheck() && $cms->getUserName() != '' && $cms->getPassword() != '') {
                                    $alfresco = new Alfresco();
                                    $folderRoot = strtoupper(Alfresco::sanitizeFolderTitle($cms->getRootFolder()));
                                    $folderProceso = strtoupper($etapa->Tramite->Proceso->id . '-' . Alfresco::sanitizeFolderTitle($etapa->Tramite->Proceso->nombre));
                                    $folderTramite = $etapa->Tramite->id;
                                    $resp = false;

                                    if (!empty($folderRoot)) {
                                        // Verifico si la carpeta con el nombre del proceso existe
                                        $pathFolder = $folderRoot . '/' . $folderProceso;
                                        $resp = $alfresco->searchFolder($cms, $pathFolder);

                                        if ($alfresco->error === null) {

                                            // No existe la carpeta, se debe crear
                                            if (!$resp) {

                                                // Se crea la carpeta del proceso
                                                $resp = $alfresco->createFolder(
                                                    $cms, 
                                                    $folderProceso, 
                                                    $etapa->Tramite->Proceso->nombre, 
                                                    $etapa->Tramite->Proceso->nombre, 
                                                    $folderRoot
                                                );

                                                if ($alfresco->error === null && $resp) {

                                                    // Se crea la carpeta del tramite
                                                    $resp = $alfresco->createFolder(
                                                        $cms, 
                                                        $folderTramite, 
                                                        'Trámite con identificador ' . $folderTramite,
                                                        'Trámite con identificador ' . $folderTramite,
                                                        $folderRoot . '/' . $folderProceso
                                                    );
                                                }
                                            } else {
                                                $pathFolder = $folderRoot . '/' . $folderProceso . '/' . $folderTramite;
                                                $resp = $alfresco->searchFolder($cms, $pathFolder);

                                                if ($alfresco->error === null) {

                                                    // No existe la carpeta, se debe crear
                                                    if (!$resp) {

                                                        // Se crea la carpeta del tramite
                                                        $resp = $alfresco->createFolder(
                                                            $cms, 
                                                            $folderTramite, 
                                                            'Trámite con identificador ' . $folderTramite,
                                                            'Trámite con identificador ' . $folderTramite,
                                                            $folderRoot . '/' . $folderProceso
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $alfresco->error = 'El folder root no existe';
                                    }                                

                                    if ($resp) {                                    
                                        $path = $folderRoot . '/' . $folderProceso . '/' . $folderTramite;
                                        $resp = $alfresco->uploadFile($cms, $path, $this->input->post($c->nombre), $c->etiqueta, $etapa, $oldFilename);
                                    }

                                    //Hubo error en algun punto al subir el archivo
                                    if (!$resp || $alfresco->error !== null) {
                                        $respuesta->validacion = FALSE;
                                        $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' . $alfresco->error . '</div>' ? $alfresco->error : '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Ocurri&oacute; un error al subir el archivo</div>';
                                        echo json_encode($respuesta);
                                        exit;
                                    }
                                }
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
        $respuesta->validacion = TRUE;
        try{
            require_once 'agenda.php';
            
            $agenda = new agenda();
            
            $appointments=$agenda->obtener_citas_de_tramite($etapa_id);
            if(isset($appointments) && is_array($appointments) && (count($appointments)>=1) ){
                $json='{"ids":[';
                $i=0;
                foreach($appointments as $item){
                    if($i==0){
                        $json=$json.'"'.$item.'"';
                    }else{
                        $json=$json.',"'.$item.'"';
                    }
                    $i++;
                }
                $json=$json.']}';
                $agenda->confirmar_citas_grupo($json);
            }
            $etapa->avanzar($this->input->post('usuarios_a_asignar'));
        }catch(Exception $err){
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'.$err->getMessage().'</div>';
            log_message('error',$err->getMessage());
        }
        
        if ($this->input->get('iframe')){
            $respuesta->redirect = site_url('etapas/ejecutar_exito');
        }
        else {
            $respuesta->redirect = site_url();
        }
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
                $countfile=1;
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
                        $swrepo=false;
                        $cms=null;
                        try{
                            $cms=new Config_cms_alfresco();
                            $cms->setAccount(UsuarioSesion::usuario()->cuenta_id);
                            $cms->loadData();    
                            if($cms->getCheck()==1){
                                $swrepo=true;
                            }
                        }catch(Exception $err){
                            echo $err->getMessage();
                        }
                        if($swrepo){
                            try{
                                $noderef = str_replace('://', '/', $f->alfresco_noderef);
                                $alfresco = new Alfresco();
                                $file_data=$alfresco->getFile($cms, $noderef);
                                if($cms!=null){
                                    $df = finfo_open();
                                    $mime_type = finfo_buffer($df, $file_data, FILEINFO_MIME_TYPE);
                                    $pathfile=$ruta_tmp.''.$f->filename;
                                    file_put_contents($pathfile, $file_data);
                                    $this->zip->read_file($pathfile);
                                    unlink($pathfile);
                                    $countfile++;
                                }
                            }catch(Exception $err){
                                echo $err->getMessage();
                            }
                        }else{
                        $path = $ruta_generados.$f->filename;
                            $this->zip->read_file($path);    
                        }
                    }
                }
                if(count($tramites) > 1){
                    log_message('debug', 'tramites: '.count($tramites));
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
}
