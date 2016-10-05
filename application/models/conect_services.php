<?php

class Connect_services{
    private $appkey='';
    private $domain='';
    private $cuenta=0;
    private $componente='';
    private $base_services='';
    private $context='';
    private $num_rows=0;

    function __construct(){
        $this->componente='token_services';
        $this->cuenta=1;
        $this->num_rows=10;
    }
    public function getAppkey(){
        return $this->appkey;
    }
    public function setAppkey($appkey=''){
        if(isset($appkey) && !empty(trim($appkey))){
            $this->appkey=$appkey;
        }else{
            throw new Exception('El Appkey no puede estar vacio');
        }
    }
    public function getDomain(){
        return $this->domain;
    }
    public function setDomain($domain=''){
        if(isset($domain) && !empty(trim($domain))){
            $this->domain=$domain;
        }else{
            throw new Exception('El Dominio no puede estar vacio');
        }
    }
    public function getCuenta(){
        return $this->cuenta;
    }
    public function setCuenta($cuenta=0){
        if(isset($cuenta) && is_numeric($cuenta)){
            $this->cuenta=$cuenta;
        }else{
            throw new Exception('La cuenta de simple no puede estar vacia y debe ser numerica');
        }
    }
    public function getComponente(){
        $this->componente;
    }
    public function setComponente($componente=''){
        if(isset($componente) && !empty(trim($componente))){
            $this->componente=$componente;
        }else{
            throw new Exception('El componente no puede estar vacio');
        }
    }
    public function getBaseService(){
        return $this->base_services;
    }
    public function setBaseService($uri=''){
        if(isset($uri) && !empty(trim($uri))){
            if(preg_match("/^h(t){2}p(s)?:(\/){2}([a-zA-Z0-9])+([a-zA-Z0-9\._-])*(:([0-9])*)?(\/)?$/",$uri)){
                $this->base_services=$uri;
            }else{
                throw new Exception('La url tiene un formato incorrecto');
            }
        }else{
            throw new Exception('La url del servicio no puede estar vacia');
        }
    }
    public function getContext(){
        return $this->context;
    }
    public function setContext($context=''){
        if(isset($context) && !empty(trim($context))){
            if(preg_match("/^(\/)?([a-zA-Z0-9])+([a-zA-Z0-9\._-])*(\/)?$/",$context)){
                $this->context=$context;
            }else{
                throw new Exception('El contexto un formato incorrecto');
            }
        }
    }
    public function getNumeroRegistroPagina(){
        return $this->num_rows;
    }
    public function setNumeroRegistroPagina($num=0){
        if(isset($num) && is_numeric($num) && $num>0){
            $this->num_rows=$num;
        }else{
            throw new Exception('El numero de registros por pagina debe ser mayor a 0');
        }
    }
    public function save(){
        try{
            $this->validateAll();
            Doctrine_Manager::connection()->beginTransaction();
            $objappkey=new Config_general();

            $objappkey->componente=$this->componente;
            $objappkey->cuenta=$this->cuenta;
            $objappkey->llave='appkey';
            $objappkey->valor=$this->appkey;
            
            $objdomain=new Config_general();

            $objdomain->componente=$this->componente;
            $objdomain->cuenta=$this->cuenta;
            $objdomain->llave='domain';
            $objdomain->valor=$this->domain;

            /*$objuri=new Config_general();

            $objuri->componente=$this->componente;
            $objuri->cuenta=$this->cuenta;
            $objuri->llave='base_services';
            $objuri->valor=$this->base_services;

            $objcontext=new Config_general();

            $objcontext->componente=$this->componente;
            $objcontext->cuenta=$this->cuenta;
            $objcontext->llave='context';
            $objcontext->valor=$this->context;

            $objnumrow=new Config_general();

            $objnumrow->componente=$this->componente;
            $objnumrow->cuenta=$this->cuenta;
            $objnumrow->llave='records';
            $objnumrow->valor=$this->num_rows;*/

            if($this->isCreate()){
                $objappkey->actualizar();
                $objdomain->actualizar();
                /*$objuri->actualizar();
                $objnumrow->actualizar();
                $objcontext->actualizar();*/
            }else{
                $objappkey->save();
                $objdomain->save();
                /*$objuri->save();
                $objcontext->save();
                $objnumrow->save();*/
            }
            Doctrine_Manager::connection()->commit();
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
    }
    private function validateAll(){
        if(empty(trim($this->appkey))){
            throw new Exception('El Appkey no puede estar vacio');
        }
        if(empty(trim($this->domain))){
            throw new Exception('El Dominio no puede estar vacio');
        }
        if(empty(trim($this->componente))){
            throw new Exception('El componente no puede estar vacio');
        }
        if(!is_numeric($this->cuenta) || $this->cuenta==0){
            throw new Exception('La cuenta de simple no puede estar vacia y debe ser numerica');
        }
        /*if(isset($this->base_services) && !empty(trim($this->base_services))){
            if(preg_match("/^h(t){2}p(s)?:(\/){2}([a-zA-Z0-9])+([a-zA-Z0-9\._-])*(:([0-9])*)?(\/)?$/",$this->base_services)){
            }else{
                throw new Exception('La url tiene un formato incorrecto');
            }
        }else{
            throw new Exception('La url del servicio no puede estar vacia');
        }
        if(isset($this->context) && !empty(trim($this->context))){
            if(preg_match("/^(\/)?([a-zA-Z0-9])+([a-zA-Z0-9\._-])*(\/)?$/",$this->context)){
            }else{
                throw new Exception('El contexto un formato incorrecto');
            }
        }
        if(!isset($this->num_rows) || !is_numeric($this->num_rows) || $this->num_rows<=0){
            throw new Exception('El numero de registros por pagina debe ser mayor a 0');
        }*/
    }
    private function isCreate(){
        try{
            $result = Doctrine_Query::create ()
            ->select('COUNT(componente) AS cuenta')
            ->from ('config_general')
            ->where ("componente = ? AND cuenta = ?",array('token_services',$this->cuenta))
            ->execute ();
            if($result[0]->cuenta>=1){
                return true;
            }else{
                return false;
            }    
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
    }
    private function loadCampo($value_llave){
        try{
            $result = Doctrine_Query::create ()
            ->select('valor')
            ->from ('config_general')
            ->where ("componente=? AND cuenta=? and llave=?",array('token_services',$this->cuenta,$value_llave))
            ->execute ();
            if(isset($result) && isset($result[0]->valor)  ){
                return $result[0]->valor;
            }else{
                return '';
            }
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
    }
    public function load_data(){
        try{
            $this->appkey=$this->loadCampo('appkey');
            $this->domain=$this->loadCampo('domain');
            /*$this->base_services=$this->loadCampo('base_services');
            $this->context=$this->loadCampo('context');
            $this->num_rows=$this->loadCampo('records');
            $this->num_rows=(isset($this->num_rows) && $this->num_rows!=0)?$this->num_rows:1;*/
        }catch(Exception $err){
            throw new Exception($err->getMessage());
        }
    }
}
