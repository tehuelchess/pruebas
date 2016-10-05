<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class DiaFeriado extends CI_Controller {
    private $domain='';
    private $appkey='';
    private $base_services='';
    private $context='';
    private $records=10;

    public function __construct() {
        parent::__construct();
        UsuarioManagerSesion::force_login();
        require_once APPPATH . 'third_party/httpful/bootstrap.php';
        $this->base_services=$this->config->item('base_service');
        $this->context=$this->config->item('context_service');
        $this->records=$this->config->item('records');
        try{
            $service=new Connect_services();
            $service->setCuenta(1);
            $service->load_data();
            $this->domain=$service->getDomain();
            $this->appkey=$service->getAppkey();
        }catch(Exception $err){
            echo 'Error: '.$err->getMessage();
        }
    }

    public function index() {
        $data['cuentas']='';
        
        $data['title']='D&iacute;as Feriados';
        $data['content']='manager/diaferiado/index';
        
        $this->load->view('manager/template',$data);
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
    public function ajax_dia_conf_global($fecha){
        $data['fecha'] = $fecha;
        $this->load->view ( 'manager/diaferiado/ajax_dia_calendario', $data );
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
                //echo 'appkey: '.$this->appkey.'  Domain: '.$this->domain;
                //echo $json;
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
                                $mensaje='No se puede agregar este d&iacute;a festivo porque ya existen citas para este d&iacute;a';
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
            $mensaje='No se pudo ingresar el d&iacute;a el parametro de fecha a ingresar es incorrecto';
        }
        $array=array('code'=>$code,'mensaje'=>$mensaje,'daysoff'=>$data);
        echo json_encode($array);
    }
    public function ajax_confirmar_eliminar_dia(){
        $data['selecciono'] =(isset($_GET['select']) && isset($_GET['fecha']) && !empty($_GET['fecha']))?$_GET['select']:0;
        $data['fecha']=(isset($_GET['fecha']))?$_GET['fecha']:'';
        $data['id'] =(isset($_GET['id']))?$_GET['id']:'';
        $this->load->view ( 'manager/diaferiado/ajax_confirmar_eliminar_dia', $data );
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
            $mensaje='No se pudo eliminar el d&iacute;a, el parametro de la fecha a eliminar es incorrecto';
        }
        $array=array('code'=>$code,'mensaje'=>$mensaje,'daysoff'=>$data);
        echo json_encode($array);
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */