<?php

class CampoInstitucionesGob extends Campo{
    

    protected function display($modo, $dato) {
        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<select class="entidades" data-id="'.$this->id.'" name="' . $this->nombre . '[entidad]" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
        $display.='<option></option>';
        $display.='</select>';
        $display.='<br />';
        $display.='<select class="instituciones" data-id="'.$this->id.'" name="' . $this->nombre . '[institucion]" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
        $display.='<option></option>';
        $display.='</select>';

        $display.='
            <script>
                $(document).ready(function(){
                    var justLoadedEntidad=true;
                    var justLoadedInstitucion=true;
                    var defaultEntidad="'.($dato && $dato->valor?$dato->valor->entidad:'').'";
                    var defaultInstitucion="'.($dato && $dato->valor?$dato->valor->institucion:'').'";
                        
                    updateEntidades();
                    
                    function updateEntidades(){
                        $.getJSON("https://apis.modernizacion.cl/instituciones/api/entidades?callback=?",function(data){
                            var html="<option></option>";
                            $(data.items).each(function(i,el){
                                html+="<option value="+el.codigo+">"+el.nombre+"</option>";
                            });
                            $("select.entidades[data-id='.$this->id.']").html(html).change(function(event){
                                updateInstituciones(this.value);
                            });
                            
                            if(justLoadedEntidad){
                                $("select.entidades[data-id='.$this->id.']").val(defaultEntidad).change();
                                justLoadedEntidad=false;
                            }
                        });
                    }
                    
                    function updateInstituciones(entidadId){
                        
                        $.getJSON("https://apis.modernizacion.cl/instituciones/api/entidades/"+entidadId+"/instituciones?callback=?",function(data){
                            var html="<option></option>";
                            if(data){
                                //$("select.instituciones[data-id='.$this->id.']").removeClass("hide");
                                $(data.items).each(function(i,el){
                                    html+="<option value="+el.codigo+">"+el.nombre+"</option>";
                                });
                            }else{
                                //$("select.instituciones[data-id='.$this->id.']").addClass("hide");
                            }
                            $("select.instituciones[data-id='.$this->id.']").html(html);

                            if(justLoadedInstitucion){
                                $("select.instituciones[data-id='.$this->id.']").val(defaultInstitucion).change();
                                justLoadedInstitucion=false;
                            }
                        });
                    }
                });
                

                
            </script>';
        
        return $display;
    }
    
    public function formValidate() {
        $CI=& get_instance();
        $CI->form_validation->set_rules($this->nombre.'[entidad]', $this->etiqueta.' - Entidad', implode('|', $this->validacion));
        $CI->form_validation->set_rules($this->nombre.'[institucion]', $this->etiqueta.' - Institución', implode('|', $this->validacion));
    }
    
}