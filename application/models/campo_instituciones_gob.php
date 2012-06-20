<?php

class CampoInstitucionesGob extends Campo{
    

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato = NULL;
        if ($tramite_id)
            $dato = $this->getDatoDeTramite($tramite_id);
        
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
                    updateEntidades();
                });
                function updateEntidades(){
                    $.getJSON("https://apis.modernizacion.cl/instituciones/api/entidades?callback=?",function(data){
                        var html="<option></option>";
                        $(data.items).each(function(i,el){
                            html+="<option value="+el.codigo+">"+el.nombre+"</option>";
                        });
                        $("select.entidades[data-id='.$this->id.']").html(html).change(function(event){
                            updateInstituciones(this.value);
                        });
                    });
                }

                function updateInstituciones(entidadId){
                    
                    $.getJSON("https://apis.modernizacion.cl/instituciones/api/entidades/"+entidadId+"/instituciones?callback=?",function(data){
                        var html="";
                        $(data.items).each(function(i,el){
                            html+="<option value="+el.codigo+">"+el.nombre+"</option>";
                        });
                        $("select.instituciones[data-id='.$this->id.']").html(html)
                    });
                }
            </script>';
        
        return $display;
    }
    
}