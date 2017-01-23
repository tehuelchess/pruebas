<?php

require_once APPPATH . 'third_party/httpful/bootstrap.php';
require_once APPPATH . 'third_party/vendor/autoload.php';

use GuzzleHttp\Client;
use Httpful\Request;
use GuzzleHttp\Exception\ClientException;

/**
 * Clase Alfresco API Restful
 *
 * @author gescalante@arkho.tech
 */
class Alfresco
{
    
    private $baseUrl;
    private $user;
    private $password;
    public static $pathSearchFile;
    public static $pathSearchFolder;
    public static $pathCreateFolder;
    public static $pathEnableMetadata;
    public static $pathAddMetadata;
    public static $pathDeleteFile;
    public static $pathUploadFile;
    public static $copyrightFiles;
    public static $header;
    public $error;
    
    public function __construct()
    {        
        $CI =& get_instance();        
        
        $this->baseUrl = $CI->config->item('base_url_service_alfresco');
        $this->user = $CI->config->item('user_alfresco');
        $this->password = $CI->config->item('password_alfresco');
        
        self::$pathSearchFile = $CI->config->item('url_service_alfresco_search_file');
        self::$pathCreateFolder = $CI->config->item('url_service_alfresco_create_folder');
        self::$pathSearchFolder = $CI->config->item('url_service_alfresco_search_folder');
        self::$pathEnableMetadata = $CI->config->item('url_service_alfresco_enable_dublincore_metadata');
        self::$pathAddMetadata = $CI->config->item('url_service_alfresco_add_dublincore_metadata');
        self::$pathDeleteFile = $CI->config->item('url_service_alfresco_delete_file');
        self::$pathUploadFile = $CI->config->item('url_service_alfresco_upload_file');
        self::$copyrightFiles = $CI->config->item('copyrights_files_uploaded_alfresco');
        self::$header = array('Content-Type' => 'application/json');      
    }
    
    /**
     * Retorna el usuario Alfresco
     * 
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Retorna el password Alfresco
     * 
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Busca un archivo en Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $noderef
     * @return boolean
     */
    public function searchFile($cms, $noderef)
    {
        $res = false;
        $this->error = null;
        
        try {
            // Verifico que se este implementando Alfresco
            if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

                // Verifico que se haya configurado Alfresco
                if ($cms->getUserName() != '' && $cms->getPassword() != '') {
                    $header = array('Content-Type' => 'application/json');
                    
                    if ($noderef) {
                        // Verifico si la carpeta con el nombre del proceso existe
                        $url = $this->baseUrl . self::$pathSearchFile . $noderef;
                        
                        $response = Request::get($url)
                            ->expectsJson()
                            ->authenticateWith($cms->getUserName(), $cms->getPassword())
                            ->addHeaders(self::$header)
                            ->send();
                        
                        // Si hay un error en la API de Alfresco
                        if (isset($response->body->error)) {
                            $this->error = $response->body->error->briefSummary;
                            log_message('error', $response->body->error->briefSummary);
                        } else {
                            // Si no encontro la carpeta del proceso
                            if (isset($response->code)) {
                                if ($response->code == 200) {
                                    $res = true;
                                } elseif ($response->code != 404) {
                                    $this->error = $response->body->message;
                                    log_message('error', $response->body->message);
                                }
                            }                    
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            log_message('error', $ex->getMessage());
        }
        
        
        return $res;
    }
    
    /**
     * Retorna un array de objetos
     * 
     * @param object $response
     * @return array
     */
    public function listFiles($response)
    {        
        $data = array();
        
        try {
            $code = isset($response->code) ? $response->code : 0;
            if ($code == 200) {
                if (isset($response->body->items) && is_array($response->body->items)) {
                    foreach($response->body->items as $item) {                            
                        $object = new stdClass();
                        $object->nombre = $item->fileName;
                        $object->descripcion = (isset($item->description)) ? $item->description : '';
                        $object->noderef = $item->nodeRef;
                        $object->tipo = $item->type;
                        
                        $mime = explode('/', $item->mimetype);
                        $mimetype = isset($mime[0]) && $mime[0] == 'image' ? 'Imagen' : 'Documento';
                        $path = strtoupper(trim($item->location->path));
                        
                        $object->mimetype = $mimetype;
                        $object->identificador = $item->title;
                        $object->global = $path == '/RESOURCES' ? true : false;
                        $data[] = $object;
                    }
                }
            }        
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            log_message('error', $ex->getMessage());
        }
        
        return $data;
    }
    
    public function cmp($a, $b)
    {
        return strcmp($a->identificador, $b->identificador);
    }
    
    /**
     * Busca un folder en Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $pathFolder
     * @param boolean $returnObject
     * @return mixed boolean/Object
     */
    public function searchFolder($cms, $pathFolder, $returnObject = false)
    {
        $res = false;
        $this->error = null;
        
        try {
            // Verifico que se este implementando Alfresco
            if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

                // Verifico que se haya configurado Alfresco
                if ($cms->getUserName() != '' && $cms->getPassword() != '') {
                    
                    // Verifico si la carpeta con el nombre del proceso existe
                    $url = $this->baseUrl . self::$pathSearchFolder . $pathFolder;
                    $response = Request::get($url)
                        ->expectsJson()
                        ->authenticateWith($cms->getUserName(), $cms->getPassword())
                        ->addHeaders(self::$header)
                        ->send();
                    
                    // Si hay un error en la API de Alfresco
                    if (isset($response->body->error)) {
                        $this->error = $response->body->error->briefSummary;
                        log_message('error', $response->body->error->briefSummary);
                    } else {

                        // Si no encontro la carpeta del proceso                        
                        if ($response->code == 404) {
                            $res = false;
                        } elseif ($response->code == 200) {
                            $res = $returnObject ? $response : true;
                        } else {
                            $this->error = $response->body->message;
                            log_message('error', $response->body->message);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            log_message('error', $ex->getMessage());
        }
        
        return $res;
    }
    
    /**
     * Crea un folder en Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $folderName
     * @param string $folderTitle
     * @param string $folderDesc
     * @param string $folderPath
     * @param string $type
     * @return boolean
     */
    public function createFolder($cms, $folderName, 
            $folderTitle, $folderDesc, $folderPath = '', $type = 'cm:folder')
    {
        $res = false;
        $this->error = null;
        
        try {
            // Verifico que se este implementando Alfresco
            if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

                // Verifico que se haya configurado Alfresco
                if ($cms->getUserName() != '' && $cms->getPassword() != '') {

                    $json = array(
                        'name' => self::sanitizeFolderTitle($folderName),
                        'title' => $folderTitle,
                        'description' => $folderDesc,
                        'type' => $type
                    );
                    $url = $this->baseUrl . self::$pathCreateFolder . $folderPath;
                    
                    $response = Request::post($url)
                        ->body(json_encode($json))
                        ->authenticateWith($cms->getUserName(), $cms->getPassword())
                        ->addHeaders(self::$header)
                        ->sendIt();            

                    // Si hay un error en la API de Alfresco
                    if (isset($response->body->error)) {
                        $this->error = $response->body->error->briefSummary;
                        log_message('error', $response->body->error->briefSummary);
                    } else {            
                        // Si hay un error en la API de Alfresco
                        if (isset($response->body->error)) {
                            $this->error = $response->body->error->briefSummary;
                            log_message('error', $response->body->error->briefSummary);
                        } else {
                            // Si no encontro la carpeta del proceso
                            if (isset($response->code)) {
                                if ($response->code == 200) {
                                    $res = true;
                                } else {                            
                                    $this->error = $response->body->message;
                                    log_message('error', $response->body->message);
                                }
                            }                    
                        }
                    }
                }
            }
        } catch(Exception $err) {
            // OJO ELIMINAR ESTE CODIGO EN CUANTO SE SOLUCIONE EL BUG DE CREAR CARPETAS Y
            // DESCOMENTAR EL CODIGO DE ABAJO
            $res = true;
            //echo $err->getMessage();
            //$this->error = $ex->getMessage();
            //log_message('warning', $ex->getMessage());
        }
        
       return $res;
    }
    
    /**
     * Habilita metadata DublinCore en Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $nodeRef
     * @return boolean
     */
    public function enableMetadata($cms, $nodeRef)
    {
        $res = false;
        $this->error = null;
        
        try {
            // Verifico que se este implementando Alfresco
            if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

                // Verifico que se haya configurado Alfresco
                if ($cms->getUserName() != '' && $cms->getPassword() != '') {
                    if ($nodeRef) {
                        $url = $this->baseUrl . self::$pathEnableMetadata . $nodeRef;
                        $body = '{"added" : ["cm:dublincore"], "removed" : []}';

                        $response = Request::post($url)
                            ->expectsJson()
                            ->sendsJson()
                            ->body($body)
                            ->authenticateWith($cms->getUserName(), $cms->getPassword())
                            ->addHeaders(self::$header)
                            ->send();

                        if (isset($response->body->error)) {                    
                            log_message('error', $response->body->error->briefSummary);
                            $this->error = $response->body->error->briefSummary;
                        } else {                
                            if ($response->code != 200) {
                                log_message('error', $response->body->message);
                                $this->error = $response->body->message;
                            } else {
                                $res = true;
                            }
                        }
                    }
                }
            }
        } catch(Exception $err) {
            log_message('error', $err->getMessage());
            $this->error = $err->getMessage();
        }
        
        return $res;
    }
    
    /**
     * Adiciona metadata DublinCore en Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $nodeRef
     * @param array $metadata
     * @return boolean
     */
    public function addMetadata($cms, $nodeRef, $metadata)
    {
        $resp = false;
        $this->error = null;
        
        try {
            // Verifico que se este implementando Alfresco
            if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

                // Verifico que se haya configurado Alfresco
                if ($cms->getUserName() != '' && $cms->getPassword() != '') {
                    
                    if (!empty($nodeRef)) {
                        $nodeRef = trim(str_replace('://', '/', $nodeRef));
                        $response = $this->enableMetadata($cms, $nodeRef);                    
                    
                        if ($response) {
                            if (is_array($metadata) && count($metadata) > 0) {
                                $url = $this->baseUrl . self::$pathAddMetadata . $nodeRef;                        

                                // Agrego la metadata al archivo
                                $response = Request::post($url)
                                    ->expectsJson()
                                    ->sendsJson()
                                    ->body(json_encode($metadata, JSON_UNESCAPED_SLASHES))
                                    ->authenticateWith($cms->getUserName(), $cms->getPassword())
                                    ->addHeaders(self::$header)
                                    ->send();

                                // Si hay un error en la API de Alfresco
                                if (isset($response->body->error)) {
                                    $this->error = $response->body->error->briefSummary;
                                    log_message('error', $response->body->error->briefSummary);                            
                                } else {
                                    if ($response->hasErrors()) {
                                        $this->error = $response->body->message;
                                        log_message('error', $response->body->message);
                                    } else {
                                        if (isset($response->code) && $response->code == 200) {
                                            $resp = true;
                                        } else {
                                            $this->error = $response->body->message;
                                            log_message('error', $response->body->message);
                                        }
                                    }
                                }                                
                            }
                        }
                    }
                }
            }
        } catch(Exception $err) {
            log_message('error', $err->getMessage());
            $this->error = $err->getMessage();
        }
        
        return $resp;
    }    
    
    /**
     * Obtiene el archivo desde Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $noderef
     * @return string
     * @throws Exception
     */
    public function getFile($cms, $noderef)
    {        
        $context = '/alfresco/s/api/node/content/';
        $this->error = null;
        
        try {
            // Verifico que se este implementando Alfresco
            if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

                // Verifico que se haya configurado Alfresco
                if ($cms->getUserName() != '' && $cms->getPassword() != '') {
                   
                    if ($noderef) {
                        $uri = $this->baseUrl . $context . $noderef;                        
                        $response = Request::get($uri)
                            ->authenticateWith($cms->getUserName(), $cms->getPassword())
                            ->sendIt();
                        if (isset($response->body) && isset($response->code) && $response->code == 200) {
                            $data = $response->body;
                            return $data;
                        } else {
                            log_message('error', 'No se pudo obtener archivo desde el repositorio');
                            $this->error = 'No se pudo obtener archivo desde el repositorio';
                            throw new Exception('No se pudo obtener archivo desde el repositorio: Error '.$response->code);
                        }
                    }
                }
            }
        } catch(Exception $err) {
            log_message('error', $err->getMessage());
            $this->error = $err->getMessage();
            throw new Exception($err->getMessage());
        }
    }
    
    /**
     * 
     * Renderiza la imagen desde Alfresco en el navegador
     * @param int $etapa_id
     * @param string $base
     * @throws Exception
     */
    public function showImage($etapa_id, $base)
    {
        $this->error = null;        
        $urifile = base64_decode($base);        
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);        
        $context = '/alfresco/s/api/node/';
        
        if ($etapa && !empty(trim($base))) {
            try {
                $cuenta_id = $etapa->Tramite->Proceso->cuenta_id;                
                $cms = new Config_cms_alfresco();
                $cms->setAccount($cuenta_id);
                $cms->loadData();
                $uri = $this->baseUrl . '' . $context . '' . $urifile . '/content';
                
                $response = Request::get($uri)
                    ->authenticateWith($cms->getUserName(), $cms->getPassword())
                    ->sendIt();
                if (isset($response->body) && isset($response->code) && $response->code == 200) {                     
                    return $response->body;                    
                } else {
                    log_message('error', 'No se pudo obtener archivo desde el repositorio');
                    $this->error = 'No se pudo obtener archivo desde el repositorio';
                    throw new Exception('No se pudo obtener archivo desde el repositorio');
                }
            } catch(Exception $err) {
                log_message('error', $err->getMessage());
                $this->error = $err->getMessage();
                throw new Exception($err->getMessage());
            }
        }        
    }
    
    /**
     * Elimina un archivo desde Alfresco
     * 
     * @param Config_cms_alfresco $cms
     * @param string $pathFile
     * @param boolean $silenceMode
     * @return boolean
     */
    public function deleteFile($cms, $pathFile, $silentMode = false)
    {
        $this->error = null;
        $res = false;
        
        // Verifico que se este implementando Alfresco
        if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

            // Verifico que se haya configurado Alfresco
            if ($cms->getUserName() != '' && $cms->getPassword() != '') {                
                
                if (!empty($pathFile)) {
                    $url = $this->baseUrl . self::$pathDeleteFile . $pathFile;
                    $response = Request::delete($url)
                        ->authenticateWith($cms->getUserName(), $cms->getPassword())
                        ->send();
                    
                    if (isset($response->body->error)) {                    
                        log_message('error', $response->body->error->briefSummary);
                        
                        if (!$silentMode)
                            $this->error = $response->body->error->briefSummary;
                    } else {                
                        if ($response->code != 200) {
                            log_message('error', $response->body->message);
                            
                            if (!$silentMode)
                                $this->error = $response->body->message;
                        } else {
                            $res = true;
                        }
                    }
                }        
            }
        }
        
        if ($silentMode)
            $res = true;
        
        return $res;        
    }
    
    /**
     * Sube un archivo a Alfresco
     * 
     * @param Config_cms_alfresco $cms     
     * @param string $path
     * @param string $filename
     * @param string $labelField
     * @param Etapa $etapa
     * @param string $description
     * @param string $oldFilename     
     * @param array $metadata
     * @return boolean
     */
    public function uploadFile($cms, $path, $filename, $labelField = '', $etapa = null, $description = '', $oldFilename = '', $metadata = array(),$deleteFile=true,$source_dir = 'datos')
    {
        try {
            
            $usuario = UsuarioSesion::usuario();            
            $error = true;
            $this->error = null;
            
            // Verifico que se este implementando Alfresco
        if ($cms instanceof Config_cms_alfresco && $cms->getCheck()) {

            // Verifico que se haya configurado Alfresco
            if ($cms->getUserName() != '' && $cms->getPassword() != '') {
                
                    //Subo el archivo
                    if (!empty($filename) && !empty($path)) {                
                        $url = $this->baseUrl . self::$pathUploadFile;
                        $DS = DIRECTORY_SEPARATOR;
                        $pathFile = FCPATH . 'uploads' . $DS . $source_dir . $DS . $filename;
                        $path = rtrim(ltrim($path, '/'), '/');
                        $nombreProceso = (is_object($etapa) ? $etapa->Tramite->Proceso->nombre : '');
                        $descripcion = !empty($description) ? $description : $labelField . ' ' . $nombreProceso;
                                
                        // Se sube el archivo                                
                        $client = new Client();
                        
                        $response = $client->post($url, [
                            'auth' => [
                                $cms->getUserName(),
                                $cms->getPassword()
                            ],
                            'multipart' => [
                                [
                                    'name' => 'filedata',
                                    'contents' => fopen($pathFile, 'r')
                                ],
                                [
                                    'name' => 'siteid',
                                    'contents' => 'simple'
                                ],
                                [
                                    'name' => 'containerid',
                                    'contents' => 'documentLibrary'
                                ],
                                [
                                    'name' => 'uploadDirectory',
                                    'contents' => '/' . $path
                                ],
                                [
                                    'name' => 'description',
                                    'contents' => $descripcion
                                ],
                                [
                                    'name' => 'contenttype',
                                    'contents' => 'cm:content'
                                ],
                                [
                                    'name' => 'thumbnails',
                                    'contents' => 'doclib'
                                ],
                                [
                                    'name' => 'overwrite',
                                    'contents' => 'true'
                                ]
                            ]
                        ]);

                        if ($response->getStatusCode() == 200) {
                            $resp = json_decode($response->getBody(), true);                            

                            // Agrego la metadata del archivo                            
                            if (!isset($usuario->registrado) || (isset($usuario->registrado) && !$usuario->registrado)) {
                                $name = 'Usuario anónimo';
                            } else {
                                $name = $usuario->apellido_paterno . ' ' . $usuario->apellido_materno . ', ' . $usuario->nombres;
                            }

                            if (!is_array($metadata) || (is_array($metadata) && count($metadata) == 0)) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $content_type = finfo_file($finfo, $pathFile);                    
                                $nombre_cuenta = is_object($etapa) ? $etapa->Tramite->Proceso->Cuenta->nombre_largo : '';
                                $cuenta = $nombre_cuenta ? ', ' . $nombre_cuenta : '';
                                $dsource = 'SIMPLE' . $cuenta . ', ' . is_object($etapa) ? $nombreProceso : '';
                                $copyright = self::$copyrightFiles;

                                $metadata = array('properties' => array(
                                    '{http://www.alfresco.org/model/content/1.0}title' => $labelField,
                                    '{http://www.alfresco.org/model/content/1.0}description' => $labelField . ' ' . $nombreProceso,
                                    '{http://www.alfresco.org/model/content/1.0}author' => $name,
                                    '{http://www.alfresco.org/model/content/1.0}subject' => $labelField,
                                    '{http://www.alfresco.org/model/content/1.0}publisher' => $name,
                                    '{http://www.alfresco.org/model/content/1.0}contributor' => $name,
                                    '{http://www.alfresco.org/model/content/1.0}identifier' => $this->baseUrl . '/share/page/document-details?nodeRef=' . $resp['nodeRef'],
                                    '{http://www.alfresco.org/model/content/1.0}coverage' => 'Santiago (Chile)',
                                    '{http://www.alfresco.org/model/content/1.0}rights' => $copyright,
                                    '{http://www.alfresco.org/model/content/1.0}dcsource' => $dsource,
                                    '{http://www.alfresco.org/model/content/1.0}type' => $content_type
                                ));
                            }

                            $resp_metadata = $this->addMetadata($cms, $resp['nodeRef'], $metadata);

                            if ($resp_metadata) {

                                // Guardo el nodeRef generado al subir el archivo a Alfresco
                                if (is_object($etapa)) {                            
                                    $file = Doctrine::getTable('File')->findOneByFilenameAndTramiteId($filename, $etapa->Tramite->id);
                                    if (!$file) {
                                        $file = new File();
                                        $file->tramite_id = $etapa->Tramite->id;
                                        $file->filename = $filename;
                                        $file->tipo = 'dato';
                                        $file->llave = strtolower(random_string('alnum', 12));
                                    }                       

                                    $file->alfresco_noderef = $resp['nodeRef'];
                                    $file->save();                                    
                                }

                                // Elimino el archivo anterior que reside en Alfresco                                
                                if ($oldFilename) {
                                    $path .= '/' . $oldFilename;                                
                                    $response = $this->deleteFile($cms, $path, true);
                                }

                                // Elimino el archivo en simple                                                    
                                if (file_exists($pathFile) && $deleteFile) {
                                    @unlink($pathFile);
                                }
                                
                                $error = false;
                            }
                        } else {
                            log_message('error', 'No se pudo subir el archivo');                    
                            $this->error = 'No se pudo subir el archivo';
                        }
                    }
                }
            }
        } catch (ClientException $ex) {            
            log_message('error', $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (Exception $e) {            
            log_message('error', $e->getMessage());
            $this->error = $e->getMessage();
        }
        
        return array(
            'error' => $error,
            'messageError' => $this->error
        );
    }
    
    /**
     * Retorna un string con caracteres validos
     * 
     * @param string $title
     * @return string
     */
    public static function sanitizeFolderTitle($title)
    {
        $folder_title = trim($title);
        $folder_title = str_replace(' ', '-', $folder_title);
        $folder_title = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $folder_title);
        $folder_title = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $folder_title);
        $folder_title = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $folder_title);
        $folder_title = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),  $folder_title);
        $folder_title = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $folder_title);
        $folder_title = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $folder_title);
        $folder_title = str_replace(array("\\", "¨", "º", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "`", "]", "+", "}", "{", "´", ">", "< ", ";", ",", ":"), '', $folder_title);
        $folder_title = substr($folder_title, 0, 45);
        
        return $folder_title;
    }
    /**
     * Chquea que exista la ruta, sino existe la crea.
     * 
     * @param type $alfresco Instancia de Objeto Alfreco
     * @param type $cms Instancia del controlador para CMS
     * @param type $root Nombre de la carpeta Raíz
     * @param type $descroot Descripción de la carpeta 
     * @param type $proc Carpeta para clasificar procesos de simple
     * @param type $nombre_proc Nombre de la carpeta del tramite
     * @param type $tramite nombre de la carpeta que identifica una instancia de tramite
     */
    public static function checkAndCreateFullPath($alfresco,$cms,$root,$descroot,$proc,$nombre_proc,$tramite){
        //check root
        try{
         if( !$alfresco->searchFolder($cms, $root)){
             $sitio = Cuenta::cuentaSegunDominio()->nombre_largo;
            if(!$alfresco->createFolder($cms, $root,
                     $sitio,
                     $descroot)){
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
