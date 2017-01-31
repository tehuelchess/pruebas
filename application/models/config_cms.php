<?php

class Config_cms_alfresco
{    
    private $username = '';
    private $password = '';
    private $rootFolder = '';
    private $title = '';
    private $desc = '';
    private $account = 0;
    private $component = '';
    private $check = 1;

    function __construct()
    {             
        $this->component = 'alfresco';        
        $this->check = 1;
    }
    
    public function getUserName()
    {
        return $this->username;
    }
    
    public function setUserName($user = '')
    {
        if (isset($user) && !empty(trim($user))) {
            $this->username = $user;
        } else {
            throw new Exception('El User Name del CMS no puede estar vacio');
        }
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($pass = '')
    {
        if (isset($pass) && !empty(trim($pass))) {
            $this->password = $pass;
        } else {
            throw new Exception('El Password del CMS no puede estar vacío');
        }
    }
    
    public function getRootFolder()
    {
        return $this->rootFolder;
    }
    
    public function setRootFolder($folder = '')
    {
        if (isset($folder) && !empty(trim($folder))) {
            $folder = strtoupper($folder);
            $this->rootFolder = $folder;
        } else {
            throw new Exception('El Nombre de la carpeta raíz del CMS no puede estar vacío');
        }
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title = '')
    {
        if (isset($title) && !empty(trim($title))) {
            $this->title = $title;
        } else {
            throw new Exception('El Título de la carpeta raíz del CMS no puede estar vacío');
        }
    }
    
    public function getDescription()
    {
        return $this->desc;
    }
    
    public function setDescription($desc = '')
    {
        if (isset($desc) && !empty(trim($desc))) {
            $this->desc = $desc;
        } else {
            throw new Exception('La Descripción de la carpeta raíz del CMS no puede estar vacío');
        }
    }
    
    public function getAccount()
    {
        return $this->account;
    }
    
    public function setAccount($account = 0)
    {
        if (isset($account) && is_numeric($account)) {
            $this->account = $account;
        } else {
            throw new Exception('La cuenta de simple no puede estar vacía y debe ser numérica');
        }
    }
    
    public function getComponent()
    {
        $this->component;
    }
    
    public function setComponent($component = '')
    {
        if (isset($component) && !empty(trim($component))) {
            $this->component = $component;
        } else {
            throw new Exception('El componente no puede estar vacío');
        }
    }
    
    public function getCheck()
    {
        return $this->check;
    }
    
    public function setCheck($check = 1)
    {
        $this->check = $check;
    }
    
    public function save()
    {
        $res = false;
        
        try {
            $this->validateAll();
            
            $objuser = new Config_general();

            $objuser->componente = $this->component;
            $objuser->cuenta = $this->account;
            $objuser->llave = 'user_name_cms';
            $objuser->valor = $this->username;

            $objpass = new Config_general();

            $objpass->componente = $this->component;
            $objpass->cuenta = $this->account;
            $objpass->llave = 'password_cms';
            $objpass->valor = $this->password;
            
            $objroot = new Config_general();

            $objroot->componente = $this->component;
            $objroot->cuenta = $this->account;
            $objroot->llave = 'root_folder_name_cms';
            $objroot->valor = $this->rootFolder;

            $objtitle = new Config_general();

            $objtitle->componente = $this->component;
            $objtitle->cuenta = $this->account;
            $objtitle->llave = 'tittle_cms';
            $objtitle->valor = $this->title;

            $objdesc = new Config_general();

            $objdesc->componente = $this->component;
            $objdesc->cuenta = $this->account;
            $objdesc->llave = 'description_cms';
            $objdesc->valor = $this->desc;
            
            if ($this->isCreate()) {
                $objuser->actualizar();
                $objpass->actualizar();
                $objroot->actualizar();
                $objtitle->actualizar();
                $objdesc->actualizar();                
                
                $this->newFolderAccount();
            } else {
                $this->newFolderAccount();
                
                $objuser->save();
                $objpass->save();
                $objroot->save();
                $objtitle->save();
                $objdesc->save();
            }
            
            $res = true;
        } catch(Exception $err) {
            
            throw new Exception("Error al guardar: ".$err->getMessage());
        }
        
        return $res;
    }
    
    private function newFolderAccount()
    {        
        try {
            
            $alfresco = new Alfresco();
            
            // Verifico si la carpeta con el nombre del proceso existe
            $resp = $alfresco->searchFolder($this, $this->rootFolder);
            
            if ($alfresco->error === null) {
                // No existe la carpeta, se debe crear
                if (!$resp){
                    log_message("debug","No existe la carpeta");
                    // Se crea la carpeta raiz
                    $resp = $alfresco->createFolder(
                        $this, 
                        $this->rootFolder, 
                        $this->title, 
                        $this->desc
                    );
                    log_message("info","Carpeta creada ".$this->rootFolder);
                    if ($alfresco->error === null || trim($alfresco->error)==='') {
                        log_message("debug","Creando carpeta resources");
                        // Se crea la carpeta RESOURCES
                        $resp = $alfresco->searchFolder($this, $this->rootFolder . '/RESOURCES');
                        if ($alfresco->error === null && !$resp) {
                            $success_folder = $alfresco->createFolder($this, 'RESOURCES', 'Recursos', 'Recursos locales',  $this->rootFolder);
                        }

                        if ($alfresco->error !== null) {
                            throw new Exception($alfresco->error);
                        }
                    } else {
                        throw new Exception("Error al crear la carpetea: ".$alfresco->error);
                    }
                } else {
                    log_message("debug","Existe la carpeta");
                    // Se crea la carpeta RESOURCES
                    $resp = $alfresco->searchFolder($this, $this->rootFolder . '/RESOURCES');
                    if ($alfresco->error === null && !$resp) {
                        $success_folder = $alfresco->createFolder($this, 'RESOURCES', 'Recursos', 'Recursos locales',  $this->rootFolder);
                    }

                    if ($alfresco->error !== null) {
                        throw new Exception($alfresco->error);
                    }
                }
            } else {
                throw new Exception("Existen errores al buscar la carpeta. ".$alfresco->error);
            }
        } catch(Exception $err) {
            throw new Exception("newFolderAccount : ".$err->getMessage(), $err->getCode());
        }
    }
    
    private function validateAll()
    {        
        if (empty(trim($this->component))) {
            throw new Exception('El componente no puede estar vacío');
        }
        
        if (empty(trim($this->username))) {
            throw new Exception('El User Name no puede estar vacío');
        }
        
        if (empty(trim($this->password))) {
            throw new Exception('El Password no puede estar vacío');
        }
        
        if (empty(trim($this->rootFolder))) {
            throw new Exception('El Nombre de la carpeta raíz del CMS no puede estar vacío');
        }
        
        if (empty(trim($this->title))) {
            throw new Exception('El Título de la carpeta raíz del CMS no puede estar vacío');
        }
        
        if (!is_numeric($this->account) || $this->account == 0) {
            throw new Exception('La cuenta de simple no puede estar vacía y debe ser numérica');
        }
        
        if (!is_numeric($this->check)) {
            throw new Exception('El check tiene un valor inválido');
        }        
    }
    
    private function isCreate()
    {
        try {
            $result = Doctrine_Query::create ()
            ->select('COUNT(componente) AS cuenta')
            ->from ('config_general')
            ->where ("componente = ? AND cuenta = ?", array('alfresco', $this->account))
            ->execute ();
            if ($result[0]->cuenta >= 1) {
                return true;
            } else {
                return false;
            }    
        } catch(Exception $err) {
            throw new Exception($err->getMessage());
        }
    }
    
    private function loadField($value_llave)
    {
        try {
            $result = Doctrine_Query::create ()
            ->select('valor as campo')
            ->from ('config_general')
            ->where ("componente=? AND cuenta=? and llave=?", array('alfresco', $this->account, $value_llave))
            ->execute ();            
            return isset($result[0]->campo) ? $result[0]->campo : '';
        } catch(Exception $err) {
            throw new Exception($err->getMessage());
        }
    }
    
    public function loadData()
    {
        try {                        
            $this->username = $this->loadField('user_name_cms');
            $this->password = $this->loadField('password_cms');
            $this->rootFolder = $this->loadField('root_folder_name_cms');
            $this->title = $this->loadField('tittle_cms');
            $this->desc = $this->loadField('description_cms');
        } catch(Exception $err) {
            throw new Exception($err->getMessage());
        }
    }
    
    public function updateCheck()
    {
        try {
            if ($this->isCreate()) {
                $objcheck = new Config_general();
                $objcheck->componente = $this->component;
                $objcheck->cuenta = $this->account;
                $objcheck->llave = 'check_cms';
                $objcheck->valor = $this->check;
                $objcheck->actualizar();
            }
        } catch(Exception $err) {
            throw new Exception($err->getMessage());
        }
    }
    
    public function getExt($mime)
    {
        switch($mime) {
            case "image/jpeg":
                return '.jpg';
            break;
            case "image/png":
                return '.png';
            break;
            case "image/gif":
                return '.gif';
            break;
            case "application/pdf":
                return '.pdf';
            break;
            case "application/msword":
                return '.doc';
            break;
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                return '.docx';
            break;
            case "application/vnd.ms-excel":
                return '.xls';
            break;
            case "application/vnd.ms-excel":
                return '.xls';
            break;
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                return '.xlsx';
            break;
            case "application/vnd.ms-project":
                return '.mpp';
            break;
            case "application/vnd.visio":
                return '.vsd';
            break;
            case "application/vnd.ms-powerpoint":
                return '.ppt';
            break;
            case "application/vnd.openxmlformats-officedocument.presentationml.presentation":
                return '.pptx';
            break;
            case "application/zip":
                return '.zip';
            break;
            case "application/x-rar-compressed":
                return '.rar';
            break;
            case "application/vnd.oasis.opendocument.text":
                return '.odt';
            break;
            case "application/vnd.oasis.opendocument.presentation":
                return '.odp';
            break;
            case "application/vnd.oasis.opendocument.graphics":
                return '.ods';
            break;
            default:
                return '';
            break;
        }
    }
}
