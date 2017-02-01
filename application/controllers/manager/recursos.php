<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Recursos extends CI_Controller
{
    public $_cms;
    public $pathFolder;
    public $alfresco;
    
    public function __construct()
    {
        parent::__construct();
        
        UsuarioManagerSesion::force_login();
        
        $alfresco = new Alfresco();
        $this->alfresco = $alfresco;
        
        $cms = new Config_cms_alfresco();
        $cms->setUserName($this->alfresco->getUser());
        $cms->setPassword($this->alfresco->getPassword());
        $this->_cms = $cms;
        $this->pathFolder = 'RESOURCES';
    }
    
    public function index()
    {
        $data['title'] = 'Recursos Generales';
        $data['content'] = 'manager/recursos/index';
        $data['folderSave'] = $this->pathFolder;
        $data['checked'] = 1;
        $data['data'] = array();
        
        $this->load->view('manager/template', $data);
    }
    
    private function view()
    {        
        return Alfresco::listFilesFrom($this->_cms, $this->alfresco, $this->pathFolder);
    }
    
    public function ajaxViewEdit()
    {        
        $data['nombre'] = $this->input->get('nombre');
        $data['desc'] = $this->input->get('desc');
        $data['id'] = $this->input->get('id');
        $data['identificador'] = $this->input->get('_identificador');
        $data['tipo'] = $this->input->get('_tipo');
        
        $nodeRef = base64_encode($data['id']);
        $url_view = site_url('manager/recursos/getfile?base=' . $nodeRef . '&filename=' . $data['nombre']);
        
        $data['urlfile'] = $url_view;
        $data['noderef'] = $data['id'];
        
        $this->load->view('manager/recursos/ajax_editar', $data); 
    }
    
    public function ajaxSaveEdit()
    {        
        $desc = $this->input->get('descripcion');        
        $identificador = $this->input->get('identificador');
        $noderef = $this->input->get('noderef');
        $array = array();        
        
        try {                
            if (!empty($noderef) && !empty($desc) && !empty($identificador)) {                    
                if (preg_match('/^[\w\-]+$/', $identificador)) {

                    // Se habilita DublinCore
                    $er = $this->alfresco->enableMetadata($this->_cms, $noderef);

                    if ($this->alfresco->error === null) {

                        $usuarioManager = UsuarioBackendSesion::usuario();
                        $nombreUsuario = $usuarioManager->apellidos . ', ' . $usuarioManager->nombre;                        
                        $rights = Alfresco::$copyrightFiles;
                        $identif_upper = strtoupper($identificador);

                        $metadata = array('properties' => array(
                            '{http://www.alfresco.org/model/content/1.0}title' => $identif_upper,
                            '{http://www.alfresco.org/model/content/1.0}description' => $desc,
                            '{http://www.alfresco.org/model/content/1.0}author' => $nombreUsuario,
                            '{http://www.alfresco.org/model/content/1.0}subject' => $desc,
                            '{http://www.alfresco.org/model/content/1.0}publisher' => $nombreUsuario,
                            '{http://www.alfresco.org/model/content/1.0}contributor' => $nombreUsuario,
                            '{http://www.alfresco.org/model/content/1.0}identifier' => $identif_upper,
                            '{http://www.alfresco.org/model/content/1.0}rights' => $rights
                        ));

                        //Se agrega la Descripción en la metadata                                
                        $rsmetadata = $this->alfresco->addMetadata($this->_cms, $noderef, $metadata);

                        if ($this->alfresco->error !== null) {
                            $array = array('status' => 'error', 'error' => $this->alfresco->error);
                        } else {
                            $array = array('status' => 'success');
                        }
                    } else {
                        $array = array('status' => 'error', 'error' => $this->alfresco->error);
                    }
                } else {
                    $array = array('status' => 'error', 'error' => 'El identificador sólo acepta números, letras y guiones');
                }
            } else {
                $array = array('status' => 'error', 'error' => 'Todos los campos son obligatorios');
            }
        } catch(Exception $err) {            
            log_message('error', 'No se pudo subir el archivo. Error: ' . $err->getMessage());
            $array = array('status' => 'error', 'error' => 'Error en el servicio: ' . $err->getMessage());
        }    
        
        echo json_encode($array);
    }
    
    public function ajaxView()
    {        
        try {
            
            $data['checked'] = $this->_cms->getCheck();            
            $rows = array();
            
            if (isset($data['checked']) && $data['checked'] == 1) {
                $data = $this->view();
                usort($data, array($this->alfresco, 'cmp'));
                
                if (count($data) > 0) {                    
                    foreach($data as $item) {
                        $id = str_replace('://', '/', $item->noderef);                        
                        $base = base64_encode($id);
                        $url_view = site_url('manager/recursos/getfile?base=' . $base . '&filename=' . $item->nombre);
                        $acciones = '<a class="btn btn-primary bnt-recursos js-editar-recurso" data-id="' . $id . '" data-noderef="' . $item->noderef . '" data-nombre="' . $item->nombre . '" data-desc="' . $item->descripcion . '" data-identificador="' . $item->identificador . '" data-tipo="' . $item->tipo . '" href="javascript:;"><i class="icon-white icon-edit"></i> Editar</a> <a class="btn btn-danger bnt-recursos btncanappofun js-eliminar-recurso" href="javascript:;" ><i class="icon-white icon-remove"></i> Eliminar</a>';
                        $rows[] = '<tr><td class="identificador"><a class="link-id" style="cursor: pointer;" href="' . $url_view . '" target="_blank">' . $item->identificador . '</a></td><td>' . $item->mimetype . '</td><td>' . $item->descripcion . '</td><td>' . $acciones . '</td></tr>';
                    }
                } else {
                    $rows[] = '<tr><td colspan="4">No existen recursos</td></tr>';
                }
            }
        } catch(Exception $err) {
            $rows[] = '<tr><td colspan="4">No existen recursos</td></tr>';
        }
        
        echo json_encode(array('rows' => $rows));
    }
    
    public function ajaxDelete()
    {
        $nombre = $this->input->get('nombre');
        $error = true;
        $mensaje = 'No se pudo eliminar el archivo';
        
        try {
            $error = Alfresco::eliminarArchivo($this->_cms,$this->alfresco,$this->pathFolder,$nombre);
        } catch(Exception $err) {
            log_message('error', $err->getMessage());
        }
        
        echo json_encode(array('error' => $error, 'mensaje' => $mensaje));
    }
    
    public function save()
    {        
        $file = 'uploads/datos/'.$this->input->get('namefile');
        $archivo = $this->input->get('namefile');
        $desc = $this->input->get('descripcion');
        $identificador = $this->input->get('identificador');
        $tipo = $this->input->get('tipo');
        $array = array();
        
        if (file_exists($file)) {
            try {                
                if (!empty($file) && !empty($desc) && !empty($tipo) && !empty($identificador)) {                    
                    if (preg_match('/^[\w\-]+$/', $identificador)) {
                        switch ($tipo) {
                            case 1:
                                $tipo = 'text.document';
                                break;
                            case 2:
                                $tipo = 'image';
                                break;
                            case 3:
                                $tipo = 'image.icon';
                                break;
                            default :
                                $tipo = 'error';
                        }
                        
                        if (!empty($tipo) && $tipo != 'error') {                            
                            $response = $this->alfresco->searchFolder($this->_cms, $this->pathFolder. '/' . $archivo);

                            if ($this->alfresco->error !== null) {                                
                                $array = array('status' => 'error', 'error' => $this->alfresco->error);
                            } else {
                                // Si no existe se sube
                                if (!$response) {
                                    
                                    $usuarioManager = UsuarioBackendSesion::usuario();
                                    $nombreUsuario = $usuarioManager->apellidos . ', ' . $usuarioManager->nombre;                                    
                                    $rights = Alfresco::$copyrightFiles;
                                    $identif_upper = strtoupper($identificador);

                                    $metadata = array('properties' => array(
                                        '{http://www.alfresco.org/model/content/1.0}title' => $identif_upper,
                                        '{http://www.alfresco.org/model/content/1.0}description' => $desc,
                                        '{http://www.alfresco.org/model/content/1.0}author' => $nombreUsuario,
                                        '{http://www.alfresco.org/model/content/1.0}subject' => $desc,
                                        '{http://www.alfresco.org/model/content/1.0}publisher' => $nombreUsuario,
                                        '{http://www.alfresco.org/model/content/1.0}contributor' => $nombreUsuario,
                                        '{http://www.alfresco.org/model/content/1.0}identifier' => $identif_upper,
                                        '{http://www.alfresco.org/model/content/1.0}coverage' => 'Santiago (Chile)',
                                        '{http://www.alfresco.org/model/content/1.0}rights' => $rights,
                                        '{http://www.alfresco.org/model/content/1.0}dcsource' => 'Administrador Simple',
                                        '{http://www.alfresco.org/model/content/1.0}type' => $tipo
                                    ));
                                    
                                    $response = $this->alfresco->uploadFile($this->_cms, $this->pathFolder, $archivo, '', null, $desc, '', $metadata);                                    
                                    if ($response['error']) {                                        
                                        $array = array('status' => 'error', 'error' => 'No se pudo subir el archivo. Error: ' . $response['messageError']);
                                    } else {
                                        $array = array('status' => 'success');
                                    }                                    
                                } else {
                                    $array = array('status' => 'error', 'error' => 'No se pudo grabar el archivo porque ya existe en los recursos');
                                }
                            }
                        } else {
                            $array = array('status' => 'error', 'error' => 'Tipo de archivo no identificado');
                        }
                    } else {
                        $array = array('status' => 'error', 'error' => 'El identificador sólo acepta números, letras y guiones');
                    }
                } else {
                    $array = array('status' => 'error', 'error' => 'Todos los campos son obligatorios');
                }
            } catch(Exception $err) {
                log_message('error', 'No se pudo subir el archivo. Error: ' . $err->getMessage());
                $array = array('status' => 'error', 'error' => 'Error en el servicio: ' . $err->getMessage());
            }
        } else {
            $array = array('status' => 'error', 'error' => 'Archivo no existe');
        }        
        
        echo json_encode($array);
    }
    
    public function deleteFile()
    {
        $file = 'uploads/datos/' . $this->input->get('file');
        if (file_exists($file)) {
            @unlink($file);
        }        
    }
    
    public function getFile()
    {
        $base = $this->input->get('base');
        $filename = $this->input->get('filename');
        $urifile = base64_decode($base);
        
        if (!empty(trim($base))) {
            try {                
                $file_data = $this->alfresco->getFile($this->_cms, $urifile);
                $f = finfo_open();
                $mime_type = finfo_buffer($f, $file_data, FILEINFO_MIME_TYPE);
                
                $mime = explode('/', $mime_type);
                $mime = isset($mime[0]) ? $mime[0] : '';
                
                if ($mime != 'image')
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                header('Content-Type: ' . $mime_type);
                
                echo $file_data;
            } catch(Exception $err) {
                echo 'No se pudo obtener el archivo del repositorio';
            }
        } else {
            echo 'Archivo no existe';
        }
    }
}

