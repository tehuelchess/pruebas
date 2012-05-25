<?php
require_once 'application/third_party/file-uploader.php';

class Uploader extends CI_Controller {

    function __construct() {
        parent::__construct();
        
        UsuarioSesion::force_login();
    }

    function datos() {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('gif','jpg','png','pdf','doc','docx');
        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('uploads/datos/');
        /*
        if(isset($result['success'])){
            $size=getimagesize($result['full_path']);
            $result['resx']=$size[0];
            $result['resy']=$size[1];
        }*/
        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

    }

}

?>
