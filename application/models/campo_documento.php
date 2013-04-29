<?php
require_once('campo.php');
class CampoDocumento extends Campo {

    public $requiere_nombre=true;
    public $requiere_datos=false;
    public $estatico=true;
    
    function setTableDefinition() {
        parent::setTableDefinition();
        
        $this->hasColumn('readonly','bool',1,array('default'=>1));
    }
    
    function setUp() {
        parent::setUp();
        $this->setTableName("campo");
    }

    public function setReadonly($readonly) {
        $this->_set('readonly', 1);
    }
    
    
    protected function display($modo, $dato, $etapa_id) {
        if (!$etapa_id) {
            return '<p><a href="#">' . $this->etiqueta . '</a></p>';
        }

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            Doctrine_Manager::connection()->beginTransaction();
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            
            $file=new File();
            $file->tramite_id=$etapa->Tramite->id;
            $file->tipo='documento';
            $file->save();
            
            $res=$this->Documento->generar($file->id,$file->tramite_id);
            
            $file->llave_copia=$res->llave_copia;
            $file->validez=$res->validez;
            $file->filename = $res->filename;
            $file->save();
            
            $dato = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($this->nombre,$etapa->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = $this->nombre;
            $dato->valor = $file->filename;
            $dato->etapa_id = $etapa->id;
            $dato->save();
            
            
            Doctrine_Manager::connection()->commit();
        }

        $display = '<p><a target="_blank" href="' . site_url('documentos/get/' . $dato->valor) . '">' . $this->etiqueta . '</a></p>';
        //$display='<p><a href="'.site_url('documentos/ver/'.$this->documento_id.'/'.$etapa_id).'">'.$this->etiqueta.'</a></p>';

        return $display;
    }
    
    
    public function backendExtraFields() {
        $html='<label>Documento</label>';
        $html.='<select name="documento_id">';
        $html.='<option value=""></option>';
        foreach($this->Formulario->Proceso->Documentos as $d)
            $html.='<option value="'.$d->id.'" '.($this->documento_id==$d->id?'selected':'').'>'.$d->nombre.'</option>';
        $html.='</select>';
        
        return $html;
    }
    
    public function backendExtraValidate() {
        parent::backendExtraValidate();
        
        $CI= &get_instance();
        $CI->form_validation->set_rules('documento_id','Documento','required');
    }

}