<?php
require_once('campo.php');
class CampoFile extends Campo {
    
    public $requiere_datos=false;

    protected function display($modo, $dato,$etapa_id) {
        if(!$etapa_id){
            $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
            $display.='<button type="button" class="btn">Subir archivo</button>';
            return $display;
        }
        
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div>';
        if($modo!='visualizacion')
            $display.='<div class="file-uploader" data-action="'.site_url('uploader/datos/'.$this->id.'/'.$etapa->id).'"></div>';
        $display.='<input type="hidden" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : '') . '" />';
        if ($dato){
            $display.='<p class="link"><a href="' . site_url('uploader/datos_get?filename=' . urlencode ($dato->valor)) . '" target="_blank">' . htmlspecialchars ($dato->valor) . '</a>';
            if(!($modo=='visualizacion'))
                $display.='(<a class="remove" href="#">X</a>)</p>';
        }
        else
            $display.='<p class="link"></p>';
        $display.='</div>';

        return $display;
    }

    
    public function extraForm() {
        $filetypes=array();
        if(isset($this->extra->filetypes))
            $filetypes=$this->extra->filetypes;
        
        $output= '<select name="extra[filetypes][]" multiple>';
        $output.='<option name="jpg" '.(in_array('jpg', $filetypes)?'selected':'').'>jpg</option>';
        $output.='<option name="png" '.(in_array('png', $filetypes)?'selected':'').'>png</option>';
        $output.='<option name="gif" '.(in_array('gif', $filetypes)?'selected':'').'>gif</option>';
        $output.='<option name="pdf" '.(in_array('pdf', $filetypes)?'selected':'').'>pdf</option>';
        $output.='<option name="doc" '.(in_array('doc', $filetypes)?'selected':'').'>doc</option>';
        $output.='<option name="docx" '.(in_array('docx', $filetypes)?'selected':'').'>docx</option>';
        $output.='<option name="zip" '.(in_array('zip', $filetypes)?'selected':'').'>zip</option>';
        $output.='<option name="rar" '.(in_array('rar', $filetypes)?'selected':'').'>rar</option>';
        $output.='</select>';
        
        return $output;
    }
}