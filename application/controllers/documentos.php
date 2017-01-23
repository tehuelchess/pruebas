<?php
class Documentos extends MY_Controller {
    
     private $_alfresco;
    private $_cms;

    function __construct() {
        parent::__construct();
        $alfresco = new Alfresco();
        $cms = new Config_cms_alfresco();
        $this->_alfresco = $alfresco;
        $this->_cms = $cms;
    }
    
    function get($filename){
        
        $id=$this->input->get('id');
        $token=$this->input->get('token');
        
        //Chequeamos permisos del frontend
        $file=Doctrine_Query::create()
                ->from('File f, f.Tramite t, t.Etapas e, e.Usuario u')
                ->where('f.id = ? AND f.llave = ? AND u.id = ?',array($id,$token,UsuarioSesion::usuario()->id))
                ->fetchOne();
        
        if(!$file){
            //Chequeamos permisos en el backend
            $file=Doctrine_Query::create()
                ->from('File f, f.Tramite.Proceso.Cuenta.UsuariosBackend u')
                ->where('f.id = ? AND f.llave = ? AND u.id = ? AND (u.rol like "%super%" OR u.rol like "%operacion%" OR u.rol like "%seguimiento%")',array($id,$token,UsuarioBackendSesion::usuario()->id))
                ->fetchOne();
            
            if(!$file){
                echo 'Usuario no tiene permisos para ver este archivo.';
                exit;
            }
        }
   
        $file_data = $this->getFile($file); 
        
        if($file->alfresco_noderef != NULL){ 
            $mime_type = finfo_buffer($f, $file_data, FILEINFO_MIME_TYPE);
            $mime = explode('/', $mime_type);
            $mime = isset($mime[0]) ? $mime[0] : '';
        }else{

            $path='uploads/documentos/'.$file->filename;
        
            if(preg_match('/^\.\./', $file->filename)){
                echo 'Archivo invalido';
                exit;
            }

            if(!file_exists($path)){
                echo 'Archivo no existe';
                exit;
            }
            $mime = get_mime_by_extension($path);
        }
       
        
        $friendlyName=str_replace(' ','-',convert_accented_characters(mb_convert_case($file->Tramite->Proceso->Cuenta->nombre.' '.$file->Tramite->Proceso->nombre,MB_CASE_LOWER).'-'.$file->id)).'.'.pathinfo($file->filename,PATHINFO_EXTENSION);

        header('Content-Type: '. $mime);
        header('Content-Disposition: attachment; filename="'.$friendlyName.'"');
        echo $file_data;
    }
    

    //Acceso que utiliza applet de firma con token
    function firma_get(){
        $id=$this->input->get('id');
        $llave_firma=$this->input->get('token');
        
        if(!$id || !$llave_firma){
            $resultado=new stdClass();
            $resultado->status=1;
            $resultado->error='Faltan parametros';
            echo json_encode($resultado);
            exit;
        }
        
        $file=Doctrine_Query::create()
                ->from('File f, f.Tramite.Etapas.Usuario u')
                ->where('f.id = ? AND f.tipo = ? AND f.llave_firma = ? AND u.id = ?',array($id,'documento',$llave_firma,UsuarioSesion::usuario()->id))
                ->fetchOne();
        
        $resultado=new stdClass();
        if(!$file){
            $resultado->status=1;
            $resultado->error='Token no corresponde';
        }else{
            $resultado->status=0;
            $resultado->tipo='pdf';
            //Obtener archivo desde filesystem o CMS
            $file_data=$this->getFile($file);
            $resultado->documento=base64_encode();
        }
        
        echo json_encode($resultado);
    }
    
    function firma_post(){
        $id=$this->input->post('id');
        $llave_firma=$this->input->post('token');
        $documento=$this->input->post('documento');
        
        if(!$id || !$llave_firma || !$documento){
            $resultado=new stdClass();
            $resultado->status=1;
            $resultado->error='Faltan parametros';
            echo json_encode($resultado);
            exit;
        }
        
        $file=Doctrine_Query::create()
                ->from('File f, f.Tramite.Etapas.Usuario u')
                ->where('f.id = ? AND f.tipo = ? AND f.llave_firma = ? AND u.id = ?',array($id,'documento',$llave_firma,UsuarioSesion::usuario()->id))
                ->fetchOne();
        
        $resultado=new stdClass();
        if(!$file){
            $resultado->status=1;
            $resultado->error='Token no corresponde';
        }else{
            $resultado->status=0;
            //Se guardar el archivo en FS
            $this->writeFile($file->filename,$documento);
            //file_put_contents('uploads/documentos/'.$file->filename, base64_decode($documento));
        }
        
        echo json_encode($resultado);
    }
    
    /**
     * Recupera un archivo desde alfresco
     * 
     * @param type $file Referencia al objeto de datos archivo
     */
    private function getFile($file){
        
        $file_data = NULL;
        if($file->alfresco_noderef == NULL){
            //Enc caso de que existan referencias a filesystem
            $file_data=file_get_contents('uploads/documentos/'.$file->filename);
        }else{
            
            $cuenta_id = Cuenta::cuentaSegunDominio()->id;   
            $this->_cms->setAccount($cuenta_id);
            $this->_cms->loadData();   
            $noderef= str_replace("://", "/",$file->alfresco_noderef);
            try{
               $file_data = $this->_alfresco->getFile($this->_cms,$noderef); 
            }catch(Exception $e){
                error_log("Error al recuperar archivo ($noderef): ".$e->getMessage());
            }
        }
        return $file_data;
    }
    
    private function writeFile($file,$documento,$etapa){
        //Determinar como se sabe si esta activo CMS
        //Inicializar los datos:
         $cuenta_id = Cuenta::cuentaSegunDominio()->id; 
         $nombreCuenta = Cuenta::cuentaSegunDominio()->nombre_largo;
         $this->_cms->setAccount($cuenta_id);
         $this->_cms->loadData(); 
         //Genrar la metadata
         
         $folderRoot = strtoupper(Alfresco::sanitizeFolderTitle($this->_cms->getRootFolder()));
         $folderProceso = strtoupper($etapa->Tramite->Proceso->id . '-' . Alfresco::sanitizeFolderTitle($etapa->Tramite->Proceso->nombre));
         $folderTramite = $etapa->Tramite->id;
         
         $path = $folderRoot . '/' . $folderProceso . '/' . $folderTramite;
         
         $friendlyName=str_replace(' ','-',convert_accented_characters(mb_convert_case($file->Tramite->Proceso->Cuenta->nombre.' '.$file->Tramite->Proceso->nombre,MB_CASE_LOWER).'-'.$file->id)).'.'.pathinfo($path,PATHINFO_EXTENSION);
         
         Alfresco::checkAndCreateFullPath($this->_alfresco,
                 $this->_cms, 
                 $folderRoot,
                 "Carpeta para tramites del sitio: ".$nombreCuenta,
                 $folderProceso,
                 $etapa->Tramite->Proceso->nombre,
                 $folderTramite);
         
         try{
            $resp = $this->_alfresco->uploadFile($this->_cms, $path, $file->filename, $friendlyName, $file ,'','',array(),true,"documentos");
         }catch(Exception $e){
             error_log("Error: ".$e->getMessage());
         }
    }    
    
    private function checkAndCreatefolders($alfresco,$cms,$root,$proc,$nombre_proc,$tramite){
        //check root
        try{
         if( !$alfresco->searchFolder($cms, $root)){
             $sitio = Cuenta::cuentaSegunDominio()->nombre_largo;
            if(!$alfresco->createFolder($cms, $root,
                     $sitio,
                    "Carpeta para tramites del sitio: ".$sitio)){
                error_log("error","no se pudo crear la carpeta raíz: ".$root);
            }
         }
         //Check la carpeta de proceso
         if(!$alfresco->searchFolder($cms, $root.'/'.$proc)){
             if($alfresco->createFolder($cms, $proc ,$nombre_proc,$nombre_proc,$root)){
                 error_log("error","no se pudo crear la carpeta de proceso ".$proc);
             }
         }
         //crea l a carpeta de proceso
         if(!$alfresco->searchFolder($cms, $root.'/'.$proc.'/'.$tramite)){
             if($alfresco->createFolder($cms, 
                     $tramite,
                     "Trámite con identificador ".$tramite ,
                     "Trámite con identificador ".$tramite,
                     $root.'/'.$proc)){
                 error_log("error","no se pudo crear la carpeta de proceso ".$tramite);
             }
         }
        }catch(Exception $e){
            error_log("Error al realizar la operación checkAndCreateFolder: ".$e->getMessage());
        }
    }
}
