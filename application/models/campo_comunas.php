<?php
require_once('campo.php');
class CampoComunas extends Campo{
    
    public $requiere_datos=false;
    
    protected function display($modo, $dato) {
        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<select class="regiones" data-id="'.$this->id.'" name="' . $this->nombre . '[region]" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
        $display.='<option value="">Seleccione región</option>';
        $display.='</select>';
        $display.='<br />';
        $display.='<select class="comunas" data-id="'.$this->id.'" name="' . $this->nombre . '[comuna]" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
        $display.='<option value="">Seleccione comuna</option>';
        $display.='</select>';

        $display.='
            <script>
                $(document).ready(function(){
                    var justLoadedRegion=true;
                    var justLoadedComuna=true;
                    var defaultRegion="'.($dato && $dato->valor?$dato->valor->region:'').'";
                    var defaultComuna="'.($dato && $dato->valor?$dato->valor->comuna:'').'";
                        
                    updateRegiones();
                    
                    function updateRegiones(){
                        $.getJSON("https://apis.modernizacion.cl/dpa/regiones?callback=?",function(data){
                            var html="<option value=\'\'>Seleccione region</option>";
                            $(data).each(function(i,el){
                                html+="<option value="+el.codigo+">"+el.nombre+"</option>";
                            });
                            $("select.regiones[data-id='.$this->id.']").html(html).change(function(event){
                                updateComunas(this.value);
                            });
                            
                            if(justLoadedRegion){
                                $("select.regiones[data-id='.$this->id.']").val(defaultRegion).change();
                                justLoadedRegion=false;
                            }
                        });
                    }
                    
                    function updateComunas(regionId){
                        if(!regionId)
                            return;
                        
                        $.getJSON("https://apis.modernizacion.cl/dpa/regiones/"+regionId+"/comunas?callback=?",function(data){
                            var html="<option value=\'\'>Seleccione comuna</option>";
                            if(data){
                                $(data).each(function(i,el){
                                    html+="<option value="+el.codigo+">"+el.nombre+"</option>";
                                });
                            }
                            $("select.comunas[data-id='.$this->id.']").html(html);

                            if(justLoadedComuna){
                                $("select.comunas[data-id='.$this->id.']").val(defaultComuna).change();
                                justLoadedComuna=false;
                            }
                        });
                    }
                });
                

                
            </script>';
        
        return $display;
    }
    
    public function formValidate() {
        $CI=& get_instance();
        $CI->form_validation->set_rules($this->nombre.'[region]', $this->etiqueta.' - Región', implode('|', $this->validacion));
        $CI->form_validation->set_rules($this->nombre.'[comuna]', $this->etiqueta.' - Comuna', implode('|', $this->validacion));
    }
    
}