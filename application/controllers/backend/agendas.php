<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Agendas extends MY_BackendController {
    private $domain='';
    private $appkey='';
    private $base_services='';
    private $context='';
    private $records=10;
    public function __construct() {
        parent::__construct();
        include APPPATH . 'third_party/httpful/bootstrap.php';
        UsuarioBackendSesion::force_login();
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
        if(!in_array('super', explode(',',UsuarioBackendSesion::usuario()->rol) ) && !in_array( 'agenda',explode(',',UsuarioBackendSesion::usuario()->rol))){
            echo 'No tiene permisos para acceder a esta seccion.';
            exit;
        }
    }

    private function listarAgendas($pagina){
        $total_registros=0;
        $datos=array();
        $pagina_hasta=0;
        $pagina=(isset($pagina) && is_numeric($pagina))?$pagina:1;//se establece la pagina a mostrar
        $registros=$this->records; // numero de registro a mostrar por pagina
        try{
            $uri=$this->base_services.''.$this->context.'calendars?page='.$pagina.'&records='.$registros;
            $response = \Httpful\Request::get($uri)
                ->expectsJson()
                ->addHeaders(array(
                    'appkey' => $this->appkey,              // heder de la app key
                    'domain' => $this->domain,              // heder de domain
                ))
                ->sendIt();
            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                $total_registros=$response->body[1]->count;
                foreach($response->body[1]->calendars as $item){
                    $datos[]=array('id'=>$item->id,'nombre'=>$item->name,'pertenece'=>$item->owner_name);
                    $pagina_hasta++;
                }
            }
        }catch(Exception $err){
            //echo $err->getMessage();
        }
        
        $num_paginas=5; // numeros maximo de paginas a mostrar en la lista del paginador
        $inicio = ($pagina-1) * $registros; // se calcula desde que registro se empieza a mostrar
        $finreg=$inicio+$registros; // se calcula hasta que registro se muestra
        
        $total_paginas = ceil($total_registros / $registros); // calculo de total de paginas.

        $data['agendas']=$datos;
        $data['title'] = 'Listado de Agendas';
        $data['content'] = 'backend/agendas/index';
        
        $paginador['total_paginas']=$total_paginas;
        $paginador['pagina']=$pagina;
        $paginador['total_registros']=$total_registros;
        $paginador['registros']=$registros;
        $paginador['inicio']=$inicio;
        $paginador['inicio']=0;
        
        $pagina_intervalo=ceil($num_paginas/2)-1;

        $pagina_desde=$pagina-$pagina_intervalo; 
        $pagina_hasta=$pagina+$pagina_intervalo;
        $paginador['pagina_desde']=$pagina_desde;
        $paginador['pagina_hasta']=$pagina_hasta;


        $data['paginador']=$paginador;


        $this->load->view('backend/template', $data);
    }
    public function config_global(){
        $data['title'] = 'Configuración Global';
        $data['content'] = 'backend/agendas/config_global';
        $this->load->view('backend/template', $data);
    }
    public function index() {
        $this->listarAgendas(1);
    }
    public function pagina($pagina=1) {
        $this->listarAgendas($pagina);
    }
    
    public function crear(){
        $data['title']='Nueva Agenda';
        $data['nuevo']=true;

        $data['content'] = 'backend/agendas/template';
        $this->load->view('backend/template', $data);
    }
    public function buscar($pagina=1){// function de busqueda de agendas por nombre y pertenece
        $search=(isset($_POST['pertenece']))?$_POST['pertenece']:'';
        if(!empty($search)){
            $total_registros=0;
            $datos=array();
            $pagina=(isset($pagina) && is_numeric($pagina))?$pagina:1;//se establece la pagina a mostrar
            $registros=$this->records; // numero de registro a mostrar por pagina
            $num_paginas=5; // numeros maximo de paginas a mostrar en la lista del paginador
            $inicio = ($pagina-1) * $registros; // se calcula desde que registro se empieza a mostrar
            $finreg=$inicio+$registros; // se calcula hasta que registro se muestra
            try{
                $uri=$this->base_services.''.$this->context.'calendars/searchByName?text='.$search.'&page='.$pagina.'&records='.$registros;
                $response = \Httpful\Request::get($uri)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,              // heder de la app key
                        'domain' => $this->domain,              // heder de domain
                    ))
                    ->sendIt();
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code==200){
                    $total_registros=$response->body[1]->count;
                    foreach($response->body[1]->calendars as $item){
                        $datos[]=array('id'=>$item->id,'nombre'=>$item->name,'pertenece'=>$item->owner_name);
                    }
                }
            }catch(Exception $err){
                //echo $err->getMessage();
            }
            $total_paginas = ceil($total_registros / $registros); // calculo de total de paginas.

            $data['agendas']=$datos;
            $data['title'] = 'Buscar Agendas';
            $data['content'] = 'backend/agendas/search';
            $data['buscar']=$search;
            $paginador['total_paginas']=$total_paginas;
            $paginador['pagina']=$pagina;
            $paginador['total_registros']=$total_registros;
            $paginador['registros']=$registros;
            $paginador['inicio']=$inicio;
            $paginador['inicio']=0;
            
            $pagina_intervalo=ceil($num_paginas/2)-1;

            $pagina_desde=$pagina-$pagina_intervalo; 
            $pagina_hasta=$pagina+$pagina_intervalo;
            $paginador['pagina_desde']=$pagina_desde;
            $paginador['pagina_hasta']=$pagina_hasta;


            $data['paginador']=$paginador;


            $this->load->view('backend/template', $data);
        }else{
            $this->listarAgendas(1);
        }
    }
    public function ajax_back_nueva_agenda(){
        $data['title_form']='Nueva Agenda';
        $data['editar']=false;
        $this->load->view ( 'backend/agendas/ajax_back_nueva_agenda', $data );        
    }
    public function ajax_back_editar_agenda($id){
        $data['title_form']='Editar Agenda';
        $data['editar']=true;
        $data['id']=$id;
        $this->load->view ( 'backend/agendas/ajax_back_nueva_agenda', $data );        
    }
    public function ajax_back_eliminar_agenda(){
        if (! in_array ( 'super', explode ( ",", UsuarioBackendSesion::usuario ()->rol ) ))
            show_error ( 'No tiene permisos', 401 );
        
        $data['id'] = (isset($_GET['id']))?$_GET['id']:0;
        $data['nombre']=(isset($_GET['nombre']))?$_GET['nombre']:'';
        $data['pertenece']=(isset($_GET['pertenece']))?$_GET['pertenece']:'';

        $this->load->view ( 'backend/agendas/ajax_back_eliminar_agenda', $data );
    }
    public function ajax_eliminar_agenda(){
        $id=(isset($_GET['id']) && is_numeric($_GET['id']))?$_GET['id']:0;
        $motivo=(isset($_GET['motivo']) && !empty(trim($_GET['motivo'])))?$_GET['motivo']:'';
        $nombre=(isset($_GET['nombre']))?$_GET['nombre']:'';
        $pertenece=(isset($_GET['pertenece']))?$_GET['pertenece']:'';
        $code=0;
        $mensaje='';
        if($id>0){
            if($motivo!=''){
                try{
                    $uri=$this->base_services.''.$this->context.'calendars/disable/'.$id;//url del servicio con los parametros
                    $response = \Httpful\Request::put($uri)
                        ->expectsJson()
                        ->addHeaders(array(
                            'appkey' => $this->appkey,              // heder de la app key
                            'domain' => $this->domain,              // heder de domain
                        ))
                        ->sendIt();
                    $code=$response->code;
                    if(isset($response->body) && isset($response->body->response->code) && $response->body->response->code==200){
                        $code=$response->body->response->code;
                        $mensaje=$response->body->response->message;
                        $audit=new AuditoriaOperaciones();
                        $audit->fecha=date('Y-m-d H:i:s');
                        $audit->motivo=$motivo;
                        $da=array('Agenda'=>array('Nombre Agenda'=>$nombre,'Pertenece'=>$pertenece,'Cuenta'=>UsuarioBackendSesion::usuario ()->cuenta_id));
                        $detalle=json_encode($da);
                        $audit->detalles=$detalle;
                        $audit->operacion='Eliminar Agenda';
                        $audit->usuario=UsuarioBackendSesion::usuario()->nombre.' '.UsuarioBackendSesion::usuario()->apellidos.' <'.UsuarioBackendSesion::usuario()->email.'>';
                        $audit->proceso='Agenda';
                        $audit->cuenta_id=UsuarioBackendSesion::usuario ()->cuenta_id;
                        $audit->save();
                    }else{
                        $code=$response->body->response->code;
                        switch($code){
                            case '1060':
                                $mensaje='No se puede eliminar la agenda porque tiene citas asignadas';
                            break;
                            default:
                                $mensaje=$response->body->response->message;    
                            break;
                        }
                    }
                }catch(Exception $err){
                    $mensaje=$err->getMessage();
                }
            }else{
                $mensaje='Debe ingresar un motivo por el cual elimina la agenda.';
            }
        }
        echo json_encode(array('code'=>$code,'mensaje'=>$mensaje));
    }
    public function ajax_dia_conf_global($fecha){
        $data['fecha'] = $fecha;
        $this->load->view ( 'backend/agendas/ajax_dia_calendario', $data );
    }
    public function ajax_confirmar_eliminar_dia(){
        $data['selecciono'] =(isset($_GET['select']) && isset($_GET['fecha']) && !empty($_GET['fecha']))?$_GET['select']:0;
        $data['fecha']=(isset($_GET['fecha']))?$_GET['fecha']:'';
        $data['id'] =(isset($_GET['id']))?$_GET['id']:'';
        $this->load->view ( 'backend/agendas/ajax_confirmar_eliminar_dia', $data );
    }
    public function ajax_cargarDatosAgenda($id){
        $data='';
        $code=0;
        $mensaje='';
        if(isset($id) && is_numeric($id)){
            try{
                $uri=$this->base_services.''.$this->context.'calendars/'.$id;//url del servicio con los parametros
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
                    $data=$response->body[1]->calendars[0];
                    $arrshedule=(isset($response->body[1]->calendars[0]->schedule) && !empty($response->body[1]->calendars[0]->schedule))?get_object_vars($response->body[1]->calendars[0]->schedule):array();
                    $arrdatsh=array();
                    foreach($arrshedule as $key => $value){
                        foreach($value as $item){
                            $arrdatsh[$item][]=$key;
                        }
                    }
                    $franjas=array();
                    foreach($arrdatsh as $key => $value){
                        $i=0;
                        $tmp='';
                        $arh=explode('-',$key);
                        foreach($value as $item){
                            if($i=0){
                                $tmp=$item;
                            }else{
                                $tmp=$tmp.':'.$item;
                            }
                            $i++;
                        }
                        $franjas[]=array('horainicio'=>$arh[0],'horafinal'=>$arh[1],'dias'=>$tmp);

                    }
                }
            }catch(Exception $err){
                $mensaje=$err->getMessage();
            }
        }else{
            $mensaje='No se pudo cargar los datos intentelo mas tarde';
        }
        $array=array('code'=>200,'message'=>$mensaje,'calendar'=>$data,'franja'=>$franjas);
        echo json_encode($array);
    }
    public function ajax_grabar_agenda_back(){
        $code=0;
        $mensaje='';
        if(isset($_GET) && is_array($_GET)){
            $nombre=$_GET['nombre'];
            $id=$_GET['codagenda'];
            $grupo=$_GET['grupos_usuarios'];
            $nompertenece=$_GET['namepertenece'];
            $concurrencia=$_GET['concurrencia'];
            $tmpta=explode(':',$_GET['tatencion']);
            if(isset($tmpta[1]) && isset($tmpta[0])){
                $tatencion=intval($tmpta[1])+(intval($tmpta[0])*60);
            }else{
                $tatencion=0;
            }
            $is_group=$_GET['tipopertenece'];
            $email=$_GET['emailpertenece'];
            $finindexdia=isset($_GET['horainicio'])?count($_GET['horainicio']):0;
            $ignorarferiados=(isset($_GET['ignorarferiados']) && $_GET['ignorarferiados']==1)?1:0;
            $tminimocancelacion=(isset($_GET['tmincancelacion']))?$_GET['tmincancelacion']:0;
            $tmpta=explode(':',$_GET['tconfirmacion']);
            $tconfirmacion=intval($tmpta[1])+(intval($tmpta[0])*60);

            $arrayshedule=array();
            $lunes=$martes=$miercoles=$jueves=$viernes=$sabado=$domingo=array();
            $franja=array();
            $swvalhoracero=true;
            $swhorval=true;
            try{
                $i=0;
                $cuenta=0;
                while($cuenta<$finindexdia){
                    $dias='';
                    $arr=(isset($_GET['cmbdias'.($i+1)]))?$_GET['cmbdias'.($i+1)]:'';
                    $indx=0;
                    if(isset($arr) && is_array($arr)){
                        foreach($arr as $item){
                            switch($item){
                                case "lunes":
                                    $lunes=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'lunes',$lunes);
                                break;
                                case "martes":
                                    $martes=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'martes',$martes);
                                break;
                                case "miercoles":
                                    $miercoles=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'miercoles',$miercoles);
                                break;
                                case "jueves":
                                    $jueves=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'jueves',$jueves);
                                break;
                                case "viernes":
                                    $viernes=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'viernes',$viernes);
                                break;
                                case "sabado":
                                    $sabado=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'sabado',$sabado);
                                break;
                                case "domingo":
                                    $domingo=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'domingo',$domingo);
                                break;
                            }
                        }
                        $cuenta++;
                    }
                    $i++;
                }
                if(isset($lunes) && is_array($lunes) && (count($lunes)>0)){
                    $franja['lunes']=$lunes;
                }
                if(isset($martes) && is_array($martes) && (count($martes)>0)){
                    $franja['martes']=$martes;
                }
                if(isset($miercoles) && is_array($miercoles) && (count($miercoles)>0)){
                    $franja['miercoles']=$miercoles;
                }
                if(isset($jueves) && is_array($jueves) && (count($jueves)>0)){
                    $franja['jueves']=$jueves;
                }
                if(isset($viernes) && is_array($viernes) && (count($viernes)>0)){
                    $franja['viernes']=$viernes;
                }
                if(isset($sabado) && is_array($sabado) && (count($sabado)>0)){
                    $franja['sabado']=$sabado;
                }
                if(isset($domingo) && is_array($domingo) && (count($domingo)>0)){
                    $franja['domingo']=$domingo;
                }
            }catch(Exception $err){
                $swhorval=false;
                $mensaje=$err->getMessage();
            }
            $serialfranja='{';
            $i=0;
            $swhayfranja=false;
            foreach($franja as $key => $value){
                $swhayfranja=true;
                if($i>0){
                    $serialfranja=$serialfranja.',"'.$key.'":{';
                }else{
                    $serialfranja=$serialfranja.'"'.$key.'":{';
                }
                $j=0;
                foreach($value as $item){
                    $hor=explode('-',$item);
                    if($hor[0]==$hor[1]){
                        $swvalhoracero=false;
                    }
                    if($j>0){
                        $serialfranja=$serialfranja.',"'.$j.'":"'.$item.'"';
                    }else{
                        $serialfranja=$serialfranja.'"'.$j.'":"'.$item.'"';    
                    }
                    $j++;
                }
                $serialfranja=$serialfranja.'}';
                $i++;
            }
            $serialfranja=$serialfranja.'}';
            $json='{
                "name": "'.$nombre.'",
                "owner_id": "'.$grupo.'",
                "owner_name": "'.$nompertenece.'",
                "owner_email": "'.$email.'",
                "is_group": "'.$is_group.'",
                "schedule": '.$serialfranja.',
                "time_attention": "'.$tatencion.'",
                "concurrency": "'.$concurrencia.'",
                "ignore_non_working_days": "'.$ignorarferiados.'",
                "time_cancel_appointment": "'.$tminimocancelacion.'",
                "time_confirm_appointment": "'.$tconfirmacion.'"
                }';
            if($swhayfranja){
                if($swvalhoracero){
                    if($tatencion>0){
                        try{
                            $uri=$this->base_services.''.$this->context.'calendars';//url del servicio con los parametros
                            $response = \Httpful\Request::post($uri)
                                ->body($json)
                                ->expectsJson()
                                ->addHeaders(array(
                                    'appkey' => $this->appkey,              // heder de la app key
                                    'domain' => $this->domain,              // heder de domain
                                ))
                                ->sendIt();
                            $code=$response->code;
                            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                                $code=$response->body[0]->response->code;
                                $mensaje=$response->body[0]->response->message;
                            }else{
                                $code=$response->body->response->code;
                                switch($code){
                                    case "1040":
                                        $mensaje='No puede ingresar una agenda con el mismo nombre';
                                    break;
                                    case "1020":
                                        $mensaje=''.$response->body->response->message;
                                    break;
                                    default:
                                        $mensaje='Error General';
                                        $mensaje=$response->body->response->message;
                                    break;
                                }
                            }
                        }catch(Exception $err){
                            //$mensaje=$err->getMessage();}
                            $mensaje='El servicio tuvo un problema verifique los datos y vuelva a intentarlo';
                        }
                    }else{
                        $code=2;
                        $mensaje='Debe seleccionar el tiempo de atenci&oacuten';
                    }
                }else{
                    $code=3;
                    //$mensaje='No se puede grabar agenda con una hora inicial y hora final iguales';
                    $mensaje='Rango de horas inv&aacute;lido';
                }
            }else{
                $code=4;
                if($swhorval){
                    $mensaje='Debe Agregar almenos una franja horaria';
                }else{
                    $mensaje='Rango de horas inv&aacute;lido';
                }
                //$mensaje='Debe agregar al menos una franja horaria';
            }
            
        }
        $array=array('code'=>$code,'message'=>$mensaje);
        echo json_encode($array);
    }
    private function add_rangos_franjas($rangi,$rangof,$nomdia,$array){
        try{
            $i=0;
            foreach($array as $item){
                $tmp=explode('-',$item);
                $hini=strtotime($tmp[0]);
                $hinf=strtotime($tmp[1]);
                $hora1=strtotime($rangi);
                $hora2=strtotime($rangof);
                if(($hora1>=$hini &&  $hora1<$hinf) || ($hora2>$hini &&  $hora2<=$hinf)){
                    //throw new Exception('El dia '.$nomdia.' con horas entre '.$rangi.'-'.$rangof.' esta generando inconsistencia con otro rango favor verifiquelo para continuar');
                    throw new Exception('Rango de horas inv&aacute;lido');
                }else{
                    if(($hini>=$hora1 && $hini<$hora2) || ($hinf>$hora1 && $hinf<=$hora2)){
                        //throw new Exception('El dia '.$nomdia.' con horas entre '.$rangi.'-'.$rangof.' esta generando inconsistencia con otro rango favor verifiquelo para continuar');
                        throw new Exception('Rango de horas inv&aacute;lido');
                    }
                }
                $i++;
            }
            $array[]=$rangi.'-'.$rangof;

            return $array;
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
    }
    public function ajax_editar_agenda_back(){//funcion editar agenda (backend) del Service
        $code=0;
        $mensaje='No se pudo guardar, vualeva a intentarlo, si el problema persiste consute con el administrador.';
        if(isset($_GET) && is_array($_GET)){

            $nombre=$_GET['nombre'];
            $id=$_GET['codagenda'];
            $grupo=$_GET['grupos_usuarios'];
            $nompertenece=$_GET['namepertenece'];
            $concurrencia=$_GET['concurrencia'];
            $tmpta=explode(':',$_GET['tatencion']);
            if(isset($tmpta[1]) && isset($tmpta[0])){
                $tatencion=intval($tmpta[1])+(intval($tmpta[0])*60);
            }else{
                $tatencion=0;
            }
            $is_group=$_GET['tipopertenece'];
            $email=$_GET['emailpertenece'];
            $finindexdia=isset($_GET['horainicio'])?count($_GET['horainicio']):0;
            $ignorarferiados=(isset($_GET['ignorarferiados']) && $_GET['ignorarferiados']==1)?1:0;
            $tminimocancelacion=(isset($_GET['tmincancelacion']))?$_GET['tmincancelacion']:0;
            
            $tmpta=explode(':',$_GET['tconfirmacion']);
            $tconfirmacion=intval($tmpta[1])+(intval($tmpta[0])*60);
            $arrayshedule=array();
            $lunes=$martes=$miercoles=$jueves=$viernes=$sabado=$domingo=array();
            $franja=array();
            $swvalhoracero=true;
            $swhorval=true;
            try{
                $i=0;
                $cuenta=0;
                while($cuenta<$finindexdia){
                    $dias='';
                    $arr=(isset($_GET['cmbdias'.($i+1)]))?$_GET['cmbdias'.($i+1)]:'';
                    $indx=0;
                    if(isset($arr) && is_array($arr)){
                        foreach($arr as $item){
                            switch($item){
                                case "lunes":
                                    //echo 'cuenta: '.$_GET['horafin'][$cuenta];
                                    $lunes=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'lunes',$lunes);
                                    //print_r($lunes);
                                break;
                                case "martes":
                                    $martes=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'martes',$martes);
                                break;
                                case "miercoles":
                                    $miercoles=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'miercoles',$miercoles);
                                break;
                                case "jueves":
                                    $jueves=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'jueves',$jueves);
                                break;
                                case "viernes":
                                    $viernes=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'viernes',$viernes);
                                break;
                                case "sabado":
                                    $sabado=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'sabado',$sabado);
                                break;
                                case "domingo":
                                    $domingo=$this->add_rangos_franjas($_GET['horainicio'][$cuenta],$_GET['horafin'][$cuenta],'domingo',$domingo);
                                break;
                            }
                        }
                        $cuenta++;
                    }
                    $i++;
                }
                if(isset($lunes) && is_array($lunes) && (count($lunes)>0)){
                    $franja['lunes']=$lunes;
                }
                if(isset($martes) && is_array($martes) && (count($martes)>0)){
                    $franja['martes']=$martes;
                }
                if(isset($miercoles) && is_array($miercoles) && (count($miercoles)>0)){
                    $franja['miercoles']=$miercoles;
                }
                if(isset($jueves) && is_array($jueves) && (count($jueves)>0)){
                    $franja['jueves']=$jueves;
                }
                if(isset($viernes) && is_array($viernes) && (count($viernes)>0)){
                    $franja['viernes']=$viernes;
                }
                if(isset($sabado) && is_array($sabado) && (count($sabado)>0)){
                    $franja['sabado']=$sabado;
                }
                if(isset($domingo) && is_array($domingo) && (count($domingo)>0)){
                    $franja['domingo']=$domingo;
                }
            }catch(Exception $err){
                $swhorval=false;
                $mensaje=$err->getMessage();
            }
            $serialfranja='{';
            $i=0;
            $swhayfranja=false;
            foreach($franja as $key => $value){
                $swhayfranja=true;
                if($i>0){
                    $serialfranja=$serialfranja.',"'.$key.'":{';
                }else{
                    $serialfranja=$serialfranja.'"'.$key.'":{';
                }
                $j=0;
                foreach($value as $item){
                    $hor=explode('-',$item);
                    if($hor[0]==$hor[1]){
                        $swvalhoracero=false;
                    }
                    if($j>0){
                        $serialfranja=$serialfranja.',"'.$j.'":"'.$item.'"';
                    }else{
                        $serialfranja=$serialfranja.'"'.$j.'":"'.$item.'"';    
                    }
                    $j++;
                }
                $serialfranja=$serialfranja.'}';
                $i++;
            }
            $serialfranja=$serialfranja.'}';
            $json='{
                "name": "'.$nombre.'",
                "owner_id": "'.$grupo.'",
                "owner_name": "'.$nompertenece.'",
                "owner_email": "'.$email.'",
                "is_group": "'.$is_group.'",
                "schedule": '.$serialfranja.',
                "time_attention": "'.$tatencion.'",
                "concurrency": "'.$concurrencia.'",
                "ignore_non_working_days": "'.$ignorarferiados.'",
                "time_cancel_appointment": "'.$tminimocancelacion.'",
                "time_confirm_appointment": "'.$tconfirmacion.'"
                }';
            if($swhayfranja){
                if($swvalhoracero){
                    if($tatencion>0){
                        try{
                            $uri=$this->base_services.''.$this->context.'calendars/'.$id;//url del servicio con los parametros
                            $response = \Httpful\Request::put($uri)
                                ->body($json)
                                ->expectsJson()
                                ->addHeaders(array(
                                    'appkey' => $this->appkey,              // heder de la app key
                                    'domain' => $this->domain,              // heder de domain
                                ))
                                ->sendIt();
                            $code=$response->code;
                            if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                                $code=$response->body[0]->response->code;
                                $mensaje=$response->body[0]->response->message;
                            }else{
                                $code=$response->body->response->code;
                                $mensaje=$response->body->response->message;
                                if($code==500){
                                    $mensaje='No se pude actualizar porque tiene citas agendadas';
                                }
                            }
                        }catch(Exception $err){
                            $mensaje=$err->getMessage();
                        }
                    }else{
                        $code=2;
                        $mensaje='Debe seleccionar el tiempo de atenci&oacuten';
                    }
                }else{
                    $code=3;
                    //$mensaje='No se puede grabar agenda con una hora inicial y hora final iguales';
                    $mensaje='Rango de horas inv&aacute;lido';
                }
            }else{
                $code=4;
                if($swhorval){
                    $mensaje='Debe Agregar almenos una franja horaria';
                }else{
                    $mensaje='Rango de horas inv&aacute;lido';   
                }
            }
        }
        $array=array('code'=>$code,'message'=>$mensaje);
        echo json_encode($array);
    }
    public function EmptyCalendar(){
        $var='{
                "success": 1,
                "result": [

                ]
            }
            ';
        echo $var;
    }
    public function diasFeriados(){
        $code=0;
        $mensaje='';
        $data=array();
        try{
            $uri=$this->base_services.''.$this->context.'daysOff';//url del servicio con los parametros
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
                $mensaje=$response->body[0]->response->message;
                foreach($response->body[1]->daysoff as $item){
                    $tmp=date('d-m',strtotime($item->date_dayoff));
                    $data[]=array('date_dayoff'=>$tmp,'name'=>$item->name,'id'=>$item->id);
                }
            }
        }catch(Exception $err){
            $mensaje=$err->getMessage();
        }
        $array=array('code'=>$code,'message'=>$mensaje,'daysoff'=>$data);
        echo json_encode($array);
    }
    public function ajax_agregar_dia_feriado(){
        $code=0;
        $mensaje='';
        $data=array();
        $fecha=(isset($_GET['fecha']) && !empty($_GET['fecha']))?$_GET['fecha']:'';
        $name=(isset($_GET['name']))?$_GET['name']:'';
        if(!empty($fecha)){
            $json='{
                "date_dayoff": "'.$fecha.'",
                "name": "'.$name.'"
                }';
            try{
                $uri=$this->base_services.''.$this->context.'daysOff';//url del servicio con los parametros
                $response = \Httpful\Request::post($uri)
                    ->expectsJson()
                    ->body($json)
                    ->addHeaders(array(
                        'appkey' => $this->appkey,             // heder de la app key
                        'domain' => $this->domain,                              // heder de domain
                    ))
                    ->sendIt();
                $code=$response->code;
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    $code=$response->body[0]->response->code;
                    $mensaje=$response->body[0]->response->message;
                }else{
                    if(isset($response->body->response->code)){
                        $code=$response->body->response->code;
                        switch($code){
                            case '1080':
                                $mensaje='No se puede agregar este dia festivo porque ya existen citas para este dia';
                            break;
                            default:
                                $mensaje=$response->body->response->message;
                            break;
                        }
                    }
                }
            }catch(Exception $err){
                $mensaje=$err->getMessage();
            }
        }else{
            $mensaje='No se pudo ingresar el dia el parametro de fecha a ingresar es incorrecto';
        }
        $array=array('code'=>$code,'mensaje'=>$mensaje,'daysoff'=>$data);
        echo json_encode($array);
    }
    public function ajax_eliminar_dia_feriado(){
        $code=0;
        $mensaje='';
        $data=array();
        $id=(isset($_GET['id']) && is_numeric($_GET['id']))?$_GET['id']:0;
        if($id>0){
            try{
                $uri=$this->base_services.''.$this->context.'daysOff/'.$id;//url del servicio con los parametros
                $response = \Httpful\Request::delete($uri)
                    ->expectsJson()
                    ->addHeaders(array(
                        'appkey' => $this->appkey,             // heder de la app key
                        'domain' => $this->domain,                              // heder de domain
                    ))
                    ->sendIt();
                $code=$response->code;
                if(isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)){
                    $code=$response->body[0]->response->code;
                    $mensaje=$response->body[0]->response->message;
                }else{
                    if(isset($response->body->response->code)){
                        $code=$response->body->response->code;
                        $mensaje=$response->body->response->message;
                    }
                }
            }catch(Exception $err){
                $mensaje=$err->getMessage();
            }
        }else{
            $mensaje='No se pudo eliminar el dia, el parametro de la fecha a eliminar es incorrecto';
        }
        $array=array('code'=>$code,'mensaje'=>$mensaje,'daysoff'=>$data);
        echo json_encode($array);
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */