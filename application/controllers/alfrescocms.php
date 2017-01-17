<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
 
class AlfrescoCms extends MY_Controller
{
    private $_alfresco;
    private $_cms;
    
    public function __construct()
    {
        parent::__construct();
        
        $alfresco = new Alfresco();
        $cms = new Config_cms_alfresco();
        $this->_alfresco = $alfresco;
        $this->_cms = $cms;
    }
    
    /**
     * Sube un archivo a SIMPLE
     * 
     * @return json
     */
    public function uploadFileToSimple()
    {
        require_once APPPATH . 'third_party/file-uploader.php';
        
        $result = array('status' => 0);
        
        try {

            $allowedExtensions = array('gif', 'jpg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx', 'mpp', 'vsd', 'odt', 'odp', 'ods', 'odg');
            $sizeLimit = 20 * 1024 * 1024;
            
            $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);            
            $result = $uploader->handleUpload('uploads/datos/');
            $archivo = '';
            
            if (isset($result['success'])) {
                $archivo = $result['file_name'];
                $archivo = trim($archivo);
                $archivo = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $archivo);
                $archivo = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $archivo);
                $archivo = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $archivo);
                $archivo = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $archivo);
                $archivo = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $archivo);
                $archivo = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $archivo);
                $archivo = str_replace(array("\\","¨","º","-","~","#","@","|","!","\"","·","$","%","&","/","(", ")","?","'","¡","¿","[","^","`","]","+","}","{","¨","´",">","< ",";", ",",":"," "),'', $archivo);
            }
            
            $result['file_name'] = $archivo;
            $result['id'] = rand(1, 99);
            $result['llave'] = strtolower(random_string('alnum', 12));
        } catch(Exception $err) {
            log_message('error', $err->getMessage());
            echo $err->getMessage();
        }
      
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Muestra el archivo en pantalla o enlace para descarga del mismo
     * 
     */
    public function showFileData()
    {
        $etapa_id = $this->input->get('etapa_id');
        $base = $this->input->get('base');
        $filename = $this->input->get('filename');        
        $urifile = base64_decode($base);        
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        
        if ($etapa && !empty(trim($base))) {
            try {
                $cuenta_id = $etapa->Tramite->Proceso->cuenta_id;                
                $this->_cms->setAccount($cuenta_id);
                $this->_cms->loadData();                
                $file_data = $this->_alfresco->getFile($this->_cms, $urifile);                
                $f = finfo_open();
                $mime_type = finfo_buffer($f, $file_data, FILEINFO_MIME_TYPE);
          
                $mime = explode('/', $mime_type);
                $mime = isset($mime[0]) ? $mime[0] : '';
                
                if ($mime != 'image')
                    header('Content-Disposition: attachment; filename="' . $filename . '"');          
          
                header('Content-Type: '.$mime_type);
                echo $file_data;
            } catch(Exception $err) {                
                echo 'No se pudo obtener el archivo del repositorio';
            }
        } else {
            echo 'Archivo no existe';
        }
    }
    
    /**
     * Renderiza imagen en pantalla
     * 
     */
    public function showImage()
    {
        try {
            $etapa_id = $this->input->get('etapa_id');
            $base = $this->input->get('base');
            
            $image = $this->_alfresco->showImage($etapa_id, $base);
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
            header('Content-Type: '.$mime_type);
            echo $image;        
        } catch (Exception $ex) {
            echo 'ERROR';
        }        
    }
    
    /**
     * Migra los archivos a alfresco
     * 
     * @param int $cuenta_id
     * @return array
     */
    public function migrate($cuenta_id = 0)
    {
        $data_config = array();
        $logs = '';
        
        $CI =& get_instance();        
        echo "Z>".$CI->config->item('migrate_enable')." \n";
        if( !$CI->config->item('migrate_enable') ){
            echo "Migración deshabilitada.<bt/>\n";
        }
                        
        try {
            // Selecciono todos las cuentas que no tengan configuracion de alfresco
            $query_config = Doctrine_Query::create()
                            ->from('Cuenta c')
                            ->leftJoin('c.Config_general cg')
                            ->orderBy('c.id');

            if ((int)$cuenta_id > 0)
                $query_config = $query_config->andWhere('c.id = ?', $cuenta_id);

            $query_config = $query_config->execute();
            
            $rows = $query_config->count(); 

            foreach ($query_config as $item) {                    
                $data_config[$item->id]['account_name'] = $item->nombre;
                $data_config[$item->id]['account_fullname'] = $item->nombre_largo;

                foreach ($item->Config_general as $config) {
                    switch ($config->llave) {
                        case 'user_name_cms':
                            $data_config[$item->id]['user'] = $config->valor;
                            break;
                        case 'password_cms':
                            $data_config[$item->id]['password'] = $config->valor;
                            break;
                        case 'root_folder_name_cms':
                            $data_config[$item->id]['root_folder'] = $config->valor;
                            break;
                    }
                }

                if (!isset($data_config[$item->id]['user']) || !isset($data_config[$item->id]['password'])
                        || !isset($data_config[$item->id]['root_folder']))
                    $logs .= 'Id cuenta: ' . $item->id . "\n";
            }
            
            $pathFile = APPPATH . 'logs' . DIRECTORY_SEPARATOR . 'cuentas_sin_config.txt';
            
            echo "Creando log de archivos: ".$pathFile;
            
            print_r($logs);
            // Si el archivo existe lo elimino
            if (file_exists($pathFile))
                @unlink($pathFile);
            
            // Escribo los IDs inexistentes en un txt
            if ($rows > 0 && !empty($logs)) {                
                $file = fopen($pathFile, 'w');
                fwrite($file, $logs);
                fclose($file);
            }

            // Obtengo los registros de los archivos a migrar a alfresco
            $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();

            $addWhere = '';
            if ((int)$cuenta_id > 0)
                $addWhere = ' AND p.cuenta_id = ' . $cuenta_id;

            
             $query = "SELECT DISTINCT f.id, f.tramite_id,  f.filename,
            t.proceso_id, p.nombre AS nombre_proceso, u.registrado, u.nombres, 
            u.apellido_materno, u.apellido_paterno, p.cuenta_id, f.tipo
            FROM dato_seguimiento d
            INNER JOIN etapa e ON d.etapa_id = e.id
            INNER JOIN tramite t ON e.tramite_id = t.id
            INNER JOIN `file` f ON f.tramite_id = t.id
            INNER JOIN usuario u ON u.id = e.usuario_id
            INNER JOIN proceso p ON t.proceso_id = p.id
            WHERE f.filename = REPLACE(d.valor, '\"', '')
            AND (f.alfresco_noderef IS NULL OR f.alfresco_noderef = '')" . $addWhere .
            ' ORDER BY f.id';
            $stmt = $pdo->prepare($query);
           
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            $rows = count($results);
            $logs = '';            
            
            foreach ($results as $item) {

                if ($item->filename != '' && (int)$item->tramite_id > 0 &&
                     (int)$item->proceso_id > 0 && 
                    $item->nombre_proceso != '') {

                    $cuenta_id = isset($data_config[(int)$item->cuenta_id]) ? (int)$item->cuenta_id : 0;
                    if ($cuenta_id > 0) {

                        // Verifico que si se haya configurado la cuenta con los accesos a alfresco
                        if (isset($data_config[$cuenta_id]['user']) && !empty($data_config[$cuenta_id]['user']) &&
                            isset($data_config[$cuenta_id]['password']) && !empty($data_config[$cuenta_id]['password']) &&
                            isset($data_config[$cuenta_id]['root_folder']) && !empty($data_config[$cuenta_id]['root_folder'])) {
                            
                            $DS = DIRECTORY_SEPARATOR;
                            $dir = ($item->tipo == 'dato') ? 'datos' : 'documentos';
                            $pathFileUpload = FCPATH . 'uploads' . $DS . $dir . $DS . $item->filename;

                            if (file_exists($pathFileUpload)) {
                                $this->_cms->setAccount($cuenta_id);
                                $this->_cms->setUserName($data_config[$cuenta_id]['user']);
                                $this->_cms->setPassword($data_config[$cuenta_id]['password']);
                                $this->_cms->setRootFolder($data_config[$cuenta_id]['root_folder']);
                                
                                $etapa = new stdClass();
                                $etapa->Tramite->Proceso->id = $item->proceso_id;
                                $etapa->Tramite->id = $item->tramite_id;
                                $etapa->Tramite->Proceso->nombre = $item->nombre_proceso;
                                $etapa->Tramite->Proceso->cuenta_id = $cuenta_id;
                                
                                $DS = DIRECTORY_SEPARATOR;
                                $upload_path = FCPATH . 'uploads' . $DS . 'datos' . $DS . $item->filename;
                                if (file_exists($upload_path)) {
                                    
                                    $folderRoot = strtoupper(Alfresco::sanitizeFolderTitle($this->_cms->getRootFolder()));
                                    $folderProceso = strtoupper($etapa->Tramite->Proceso->id . '-' . Alfresco::sanitizeFolderTitle($etapa->Tramite->Proceso->nombre));
                                    $folderTramite = $etapa->Tramite->id;
                                    $resp = false;

                                    if (!empty($folderRoot)) {
                                        // Verifico si la carpeta con el nombre del proceso existe
                                        $pathFolder = $folderRoot . '/' . $folderProceso;
                                        $resp = $this->_alfresco->searchFolder($this->_cms, $pathFolder);

                                        if ($this->_alfresco->error === null) {

                                            // No existe la carpeta, se debe crear
                                            if (!$resp) {

                                                // Se crea la carpeta del proceso
                                                $resp = $this->_alfresco->createFolder(
                                                    $this->_cms, 
                                                    $folderProceso, 
                                                    $etapa->Tramite->Proceso->nombre, 
                                                    $etapa->Tramite->Proceso->nombre, 
                                                    $folderRoot
                                                );

                                                if ($this->_alfresco->error === null && $resp) {

                                                    // Se crea la carpeta del tramite
                                                    $resp = $this->_alfresco->createFolder(
                                                        $this->_cms, 
                                                        $folderTramite, 
                                                        'Trámite con identificador ' . $folderTramite,
                                                        'Trámite con identificador ' . $folderTramite,
                                                        $folderRoot . '/' . $folderProceso
                                                    );
                                                }
                                            } else {
                                                $pathFolder = $folderRoot . '/' . $folderProceso . '/' . $folderTramite;
                                                $resp = $this->_alfresco->searchFolder($this->_cms, $pathFolder);

                                                if ($this->_alfresco->error === null) {

                                                    // No existe la carpeta, se debe crear
                                                    if (!$resp) {

                                                        // Se crea la carpeta del tramite
                                                        $resp = $this->_alfresco->createFolder(
                                                            $this->_cms, 
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
                                        $this->_alfresco->error = 'El folder root no existe';
                                    }                                

                                    if ($resp) {                                    
                                        $path = $folderRoot . '/' . $folderProceso . '/' . $folderTramite;
                                        $resp = $this->_alfresco->uploadFile($this->_cms, $path, $item->filename, $item->filename, $etapa,'','',array(),false);
                                    }
                                }
                                
                                if (!$resp || $this->_alfresco->error !== null) {
                                    $logs .= 'Id Archivo: ' . $item->id . ' Nombre: ' . $item->filename . ' Error: ' . $this->_alfresco->error . "\n";
                                }
                            } else {
                                $logs .= 'Id Archivo: ' . $item->id . ' Nombre: ' . $item->filename . ' Error: El archivo no existe en la ruta uploads/datos/' . "\n";
                            }
                        }
                    } else {
                        $logs .= 'Id Archivo: ' . $item->id . ' Nombre: ' . $item->filename . ' Error: Id cuenta no definido' . "\n";
                    }                        
                } else {
                    $logs .= 'Id Archivo: ' . $item->id . ' Nombre: ' . $item->filename . ' Error: no tiene todos los datos requeridos para poder subir el archivo a Alfresco' . "\n";
                }
            }
            
            $pathFile = APPPATH . 'logs' . DIRECTORY_SEPARATOR . 'archivos_sin_subir.txt';

            // Si el archivo existe lo elimino
            if (file_exists($pathFile))
                @unlink($pathFile);
            
            if ($rows > 0 && !empty($logs)) {
                $file = fopen($pathFile, 'w');
                fwrite($file, $logs);
                fclose($file);
            }
            
            echo "\n\r";
            echo 'La migracion ha terminado. Revise los logs para verificar si hubo errores durante el proceso.';
            echo "\n\r";
        } catch (Exception $ex) {
            log_message('error', $ex->getMessage());
            die($ex->getMessage() . "\n");
        }
    }
}
