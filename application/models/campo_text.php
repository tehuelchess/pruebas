<?php
require_once('campo.php');
class CampoText extends Campo{
    
    public $requiere_datos=false;

    protected function display($modo, $dato,$etapa_id) {
        if($etapa_id){
            $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
            $regla=new Regla($this->valor_default);
            $valor_default=$regla->getExpresionParaOutput($etapa->tramite_id);
        }else{
            $valor_default=$this->valor_default;
        }
        
        
        $display='<label>' . $this->etiqueta . (!$this->readonly && !in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';    
        $display.='<input ' . ($this->readonly || $modo == 'visualizacion' ? 'readonly' : '') . ' type="text" name="' . $this->nombre . '" value="' . ($dato?htmlspecialchars($dato->valor):htmlspecialchars($valor_default)) . '" />';
    
        return $display;
    }
    
}