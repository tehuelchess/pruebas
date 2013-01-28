<?php
require_once('accion.php');

class AccionWebservice extends Accion {

    public function displayForm() {
        $display = '<p>Esta accion consultara via REST la siguiente URL. Los resultados, seran almacenados como variables.</p>';
        $display.='<p>Los resultados esperados deben venir en formato JSON siguiendo este formato:</p>';
        $display.='<pre>
{
    "variable1": "valor1",
    "variable2": "valor2",
    ...
}</pre>';
        $display.= '<label>URL</label>';
        $display.='<input type="text" class="input-xxlarge" name="extra[url]" value="' . ($this->extra ? $this->extra->url : '') . '" />';


        return $display;
    }

    public function validateForm() {
        $CI = & get_instance();
        $CI->form_validation->set_rules('extra[url]', 'URL', 'required');
    }

    public function ejecutar($tramite_id) {
        $r=new Regla($this->extra->url);
        $url=$r->getExpresionParaOutput($tramite_id);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        
        $json=json_decode($result);
        
        foreach($json as $key=>$value){
            $dato=Doctrine::getTable('Dato')->findOneByNombreAndTramiteId($key,$tramite_id);
            if(!$dato)
                $dato=new Dato();
            $dato->nombre=$key;
            $dato->valor=$value;
            $dato->tramite_id=$tramite_id;
            $dato->save();
        }        
        
    }

}
