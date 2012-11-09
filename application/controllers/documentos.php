<?php
class Documentos extends MY_Controller {

    function __construct() {
        parent::__construct();

        UsuarioSesion::force_login();
    }
    
    function get($filename){
        //Chequeamos permisos
        $file=Doctrine_Query::create()
                ->from('File f, f.Tramite t, t.Etapas e, e.Usuario u')
                ->where('f.filename = ? AND f.tipo = ? AND u.id = ?',array($filename,'documento',UsuarioSesion::usuario()->id))
                ->fetchOne();
        
        if(!$file){
            echo 'Usuario no tiene permisos para ver este archivo.';
            exit;
        }
        
        
        $path='uploads/documentos/'.$filename;
        
        if(preg_match('/^\.\./', $filename)){
            echo 'Archivo invalido';
            exit;
        }

        if(!file_exists($path)){
            echo 'Archivo no existe';
            exit;
        }
  
        header('Content-Type: '.  mime_content_type($path));
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}

?>
