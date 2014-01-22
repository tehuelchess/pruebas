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
        if(isset($this->extra->firmar) && $this->extra->firmar)
            return $this->displayFirmador($modo, $dato, $etapa_id);
        else
            return $this->displayDescarga ($modo, $dato, $etapa_id);
    }
    
    
    private function displayDescarga($modo, $dato, $etapa_id) {
        if (!$etapa_id) {
            return '<p><a class="btn btn-success" href="#"><i class="icon-download-alt icon-white"></i> ' . $this->etiqueta . '</a></p>';
        }

        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file=$this->Documento->generar($etapa->id);

            $dato = new DatoSeguimiento();
            $dato->nombre = $this->nombre;
            $dato->valor = $file->filename;
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }else{
            $file=Doctrine::getTable('File')->findOneByTipoAndFilename('documento',$dato->valor);
            if($etapa->pendiente && isset($this->extra->regenerar) && $this->extra->regenerar){
                $file->delete();
                $file=$this->Documento->generar($etapa->id);
                $dato->valor = $file->filename;
                $dato->save();
            }
        }

        $display = '<p><a class="btn btn-success" target="_blank" href="' . site_url('documentos/get/' . $file->filename) . '?id='.$file->id.'&token='.$file->llave.'"><i class="icon-download-alt icon-white"></i> ' . $this->etiqueta . '</a></p>';

        return $display;
    }
    
    private function displayFirmador($modo, $dato, $etapa_id) {
        if (!$etapa_id) {
            return '<p>' . $this->etiqueta . '</p>';
        }

        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file=$this->Documento->generar($etapa->id);

            $dato = new DatoSeguimiento();
            $dato->nombre = $this->nombre;
            $dato->valor = $file->filename;
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }else{
            $file=Doctrine::getTable('File')->findOneByTipoAndFilename('documento',$dato->valor);
            if($etapa->pendiente && isset($this->extra->regenerar) && $this->extra->regenerar){
                $file->delete();
                $file=$this->Documento->generar($etapa->id);
                $dato->valor = $file->filename;
                $dato->save();
            }
        }

        $display = '<p>'.$this->etiqueta.'</p>';
        $display .= '<div id="exito" class="alert alert-success" style="display: none;">Documento fue firmado con éxito.</div>';
        $display .= '<p><a class="btn btn-info" target="_blank" href="' . site_url('documentos/get/' . $dato->valor) .'?id='.$file->id.'&token='.$file->llave. '"><i class="icon-search icon-white"></i> Previsualizar el documento</a></p>';

        
        $isMac = stripos( $_SERVER['HTTP_USER_AGENT'] , 'macintosh' ) !== false;
        
        if($isMac){
        $display.='
        <script>
            function checkPasswordToken(){
                var passwordToken = $("#passwordTokenValue").val();
                        var value = document.SignerApplet.hasPK(passwordToken);
                        if (value === "true") {
                            
                        }
                        else {
                            alert("No se ha detectado Token, por favor inserte su Token de firma o la password ingresada es la incorrecta");
                        }
            }
        </script>
        <div id="password">
            <label>Contraseña del Token:</label> <input id="passwordTokenValue" type="password" />
            <button type="button" class="btn" onclick="checkPasswordToken()">Desbloquear Token</button>
        </div><br />';
        }
        
        
        $display .= '
            <script>
                function firmarConToken(){
                    var resultadoApplet = document.SignerApplet.signDocuments();
                    var status=$(resultadoApplet).find("documento").attr("RESULTADO");
                    if (status==="true") {
                        $("#exito").show();
                        $("#password").hide();
                        $("#firmaDiv").hide();
                        alert("Documento firmado con éxito.");     
                    }else{
                        alert("Hubo un error al intentar firmar el documento.");
                    }
                }
                function progreso(tot, eje, documento){
                
                }
            </script>
            <div id="firmaDiv">
            <label>Seleccione la firma</label>      
            <div style="float: left;">
            <applet code="'.($isMac?'cl.agile.pdf.applet.SignerAppletMinSegPressMAC':'cl.agile.pdf.applet.SignerAppletMinSegPress').'" width="350" height="25" name="SignerApplet">
                <param name="jnlp_href" value="'.base_url().'assets/applets/signer/'.($isMac?'SignerApplet_0_6.jnlp':'SignerApplet_0_7_win.jnlp').'" />
                <param name="documentosPdf" value="'.  htmlspecialchars('<PorFirmar><documento id=\''.$file->id.'\' token=\''.$file->llave_firma.'\' comentario=\'Firmado Digitalmente\' lugar=\'Santiago\' tipoFirma=\'TIPO_DOC\'/></PorFirmar>').'" />
                <param name="urlBaseGet" value="'.site_url('documentos/firma_get').'" />
                <param name="urlBasePost" value="'.site_url('documentos/firma_post').'" />
                <param name="cLetra" value="000000" />
                <param name="cFondo" value="FFFFFF" />
            </applet>
            </div>
            <div><button type="button" class="btn btn-success" onclick="firmarConToken()"><i class="icon-pencil icon-white"></i> Firmar Documento</button></div>

        </div>';

        return $display;
    }
    
    
    public function backendExtraFields() {
        $regenerar=isset($this->extra->regenerar)?$this->extra->regenerar:null;
        $firmar=isset($this->extra->firmar)?$this->extra->firmar:null;
        
        $html='<label>Documento</label>';
        $html.='<select name="documento_id">';
        $html.='<option value=""></option>';
        foreach($this->Formulario->Proceso->Documentos as $d)
            $html.='<option value="'.$d->id.'" '.($this->documento_id==$d->id?'selected':'').'>'.$d->nombre.'</option>';
        $html.='</select>';
        
        $html.='<label class="radio"><input type="radio" name="extra[regenerar]" value="0" '.(!$regenerar?'checked':'').' /> El documento se genera solo la primera vez que se visualiza este campo.</label>';
        $html.='<label class="radio"><input type="radio" name="extra[regenerar]" value="1" '.($regenerar?'checked':'').' /> El documento se regenera cada vez que se visualiza este campo.</label>';
        
        $html.='<label class="checkbox"><input type="checkbox" name="extra[firmar]" '.($firmar?'checked':'').' /> Deseo firmar con token en este paso.</label>';
        
        return $html;
    }
    
    public function backendExtraValidate() {
        parent::backendExtraValidate();
        
        $CI= &get_instance();
        $CI->form_validation->set_rules('documento_id','Documento','required');
    }

}