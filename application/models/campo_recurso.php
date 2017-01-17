<?php

require_once('campo.php');

class CampoRecurso extends Campo
{
    public $datos_recursos = true;
    public $requiere_nombre = true;
    public $requiere_datos = false;
    public $estatico = true;
    
    public function setReadonly($readonly)
    {
        $this->_set('readonly', 1);
    }
    
    protected function display($modo, $dato, $etapa_id)
    {
        $display = '';
        
        if (!$etapa_id) {      
            $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . '</label>';
            $display .= '<div class="controls">';
            $display .= '<div class="cont-link float-left">';
            
            if (isset($this->extra->resources)) {
                $resources = $this->extra->resources;                
                if (isset($resources[1]) && $resources[1] == 'Imagen') {
                    $display .= '<span style="font-size: 32px;" class="glyphicon glyphicon-picture" aria-hidden="true"></span>';
                } else if (isset($resources[1]) && $resources[1] == 'Documento') {
                    $display .= '<span style="font-size: 32px;" class="glyphicon glyphicon-file" aria-hidden="true"></span>';
                } else {
                    $display .= '<span style="font-size: 32px;" class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span>';
                }
            }            
            
            $display .= '</div></div>';
        } else {
            $display = '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . '</label>';
            $display .= '<div class="controls">';            
            
            if (isset($this->extra->resources)) {
                $resources = $this->extra->resources;
                
                if (isset($resources[0]) && isset($resources[1]) && isset($resources[2])) {
                    $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
                    $cuenta_id = $etapa->Tramite->Proceso->cuenta_id;                
                    $cms = new Config_cms_alfresco();
                    $cms->setAccount($cuenta_id);
                    $cms->loadData();
                    $DS = DIRECTORY_SEPARATOR;
                    $pathFile = FCPATH . 'uploads' . $DS . 'datos';
                    
                    if ($cms->getCheck() == 1) {
                        $node = str_replace('workspace://SpacesStore/', '', $resources[0]);
                        $alfresco = new Alfresco();
                        $fileExist = $alfresco->searchFile($cms, $node);
                    
                        if ($fileExist) {                            
                            $CI = &get_instance();
                            $nodeRef = str_replace('://', '/', $resources[0]);
                            $base64 = base64_encode($nodeRef);
                            $url = site_url('alfrescocms/showfiledata?base=' . $base64 . '&filename=' . $resources[2] . '&etapa_id=' . $etapa_id);

                            if ($resources[1] == 'Imagen') {
                                $urlImg = site_url('alfrescocms/showimage?base=' . $base64 . '&etapa_id=' . $etapa_id);
                                if ($urlImg == 'ERROR') {
                                    $display .= '<p>Archivo no disponible</p>';
                                } else {
                                    $display .= '<a href="' . $url . '" target="_blank"><img src="' . $urlImg . '" alt="' . $resources[2] . '" title="' . $resources[2] . '" ></a>';                                
                                }
                                
                            } else if ($resources[1] == 'Documento') {
                                $display .= '<div class="cont-link float-left">';
                                $display .= '<a href="' . $url . '"><span style="font-size: 32px;" class="glyphicon glyphicon-file" aria-hidden="true"></span></a>';
                                $display .= '</div>';
                            } else {
                                $display .= '<div class="cont-link float-left">';
                                $display .= '<a href="' . $url . '"><span style="font-size: 32px;" class="glyphicon glyphicon-ban-circle" aria-hidden="true"></span></a>';
                                $display .= '</div>';
                            }
                        } else {
                            $display .= '<div class="float-left">';
                            $display .= '<p>Archivo no disponible</p>';
                            $display .= '</div>';
                        }
                    }
                }
            }            
            
            $display .= '</div>';
        }
        
        return $display;
    }
    
    public function extraForm()
    {   
        $resources = array();
        if (isset($this->extra->resources)) {
            $resources = $this->extra->resources;
        }
        
        if (isset($resources[0]) && $resources[1]) {
            $output = '<select style="display: none;" id="resource_alfresco" name="extra[resources][]" multiple>';
            $output .='<option name="' . $resources[0] . '" selected>' . $resources[0] . '</option>';
            $output .='<option name="' . $resources[1] . '" selected>' . $resources[1] . '</option>';
            $output .='<option name="' . $resources[2] . '" selected>' . $resources[2] . '</option>';
            $output .='<option name="' . $resources[3] . '" selected>' . $resources[3] . '</option>';
            $output .='</select>';
        } else {
            $output = '<select style="display: none;" id="resource_alfresco" name="extra[resources][]" multiple></select>';
        }
        
        return $output; 
    }
    
    public function backendExtraValidate()
    {
        $CI = &get_instance();
        $CI->form_validation->set_rules('extra[resources][]', 'Archivo', 'required');
    }
    
    
}
