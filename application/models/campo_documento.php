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
            return '<p><a href="#">' . $this->etiqueta . '</a></p>';
        }

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file=$this->Documento->generar($etapa_id);

            $dato = new DatoSeguimiento();
            $dato->nombre = $this->nombre;
            $dato->valor = $file->filename;
            $dato->etapa_id = $etapa_id;
            $dato->save();
        }

        $display = '<p><a target="_blank" href="' . site_url('documentos/get/' . $dato->valor) . '">' . $this->etiqueta . '</a></p>';

        return $display;
    }
    
    private function displayFirmador($modo, $dato, $etapa_id) {
        if (!$etapa_id) {
            return '<p>' . $this->etiqueta . '</p>';
        }

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file=$this->Documento->generar($etapa_id);

            $dato = new DatoSeguimiento();
            $dato->nombre = $this->nombre;
            $dato->valor = $file->filename;
            $dato->etapa_id = $etapa_id;
            $dato->save();
        }else{
            $file=Doctrine::getTable('File')->findOneByTipoAndFilename('documento',$dato->valor);
        }

        $display = '<p>'.$this->etiqueta.'</p>';
        $display .= '<p><a class="btn btn-info" target="_blank" href="' . site_url('documentos/get/' . $dato->valor) . '"><i class="icon-search icon-white"></i> Previsualizar el documento</a></p>';
        $display .= '<script>
            $(document).ready(function() {
                var os = navigator.platform;

                if (os === "MacIntel") {
                    $("#password").show();
                    $("#password button").click(function() {
                        var passwordToken = $("#passwordTokenValue").val();
                        var value = document.SignerApplet.hasPK(passwordToken);
                        if (value === "true") {
                            $("#firmaDiv").css("visibility", "visible");
                        }
                        else {
                            alert("No se ha detectado Token, por favor inserte su Token de firma o la password ingresada es la incorrecta");
                        }
                    });
                }

                $("#firmaDiv button").click(function() {
                    var resultadoApplet = document.SignerApplet.signDocuments();
                    var status=$(resultadoApplet).find("documento").attr("RESULTADO");
                    if (status==="true") {
                        alert("Documento firmado con éxito.");         
                    }else{
                        alert("Hubo un error al intentar firmar el documento.");
                    }
                });
            });
        </script>';
        
        $display .= '<div id="password" style="display: none;">
            <label>Contraseña del Token:</label> <input id="passwordTokenValue" type="password" />
            <button type="button" class="btn">Desbloquear Token</button>
        </div>';
        
        $display .= '<div id="firmaDiv">
            <script>
                var os = navigator.platform;
                var attributes = {
                    code: os === "MacIntel" ? "cl.agile.pdf.applet.SignerAppletMinSegPressMAC" : "cl.agile.pdf.applet.SignerAppletMinSegPress",
                    width: "350",
                    height: "100",
                    name: "SignerApplet"
                };
                var parameters = {
                    jnlp_href: base_url+"assets/applets/signer/" + (os === "MacIntel" ? "SignerApplet_0_5.jnlp" : "SignerApplet_0_5_win.jnlp")
                            , documentosPdf: "<PorFirmar><documento id=\''.$file->id.'\' token=\''.$file->llave_firma.'\' comentario=\'Firmado Digitalmente\' lugar=\'Santiago\' tipoFirma=\'TIPO_DOC\'/></PorFirmar>"
                            , urlBaseGet: "'.site_url('documentos/firma_get').'"
                            , urlBasePost: "'.site_url('documentos/firma_post').'"
                            , cLetra: "000000"
                            , cFondo: "FFFFFF"};

                deployJava.runApplet(attributes, parameters, "1.6");
            </script>

            <div><button type="button" class="btn btn-success"><i class="icon-pencil icon-white"></i> Firmar Documento</button></div>

        </div>';

        return $display;
    }
    
    
    public function backendExtraFields() {
        $firmar=isset($this->extra->firmar)?$this->extra->firmar:null;
        
        $html='<label>Documento</label>';
        $html.='<select name="documento_id">';
        $html.='<option value=""></option>';
        foreach($this->Formulario->Proceso->Documentos as $d)
            $html.='<option value="'.$d->id.'" '.($this->documento_id==$d->id?'selected':'').'>'.$d->nombre.'</option>';
        $html.='</select>';
        $html.='<label class="checkbox"><input type="checkbox" name="extra[firmar]" '.($firmar?'checked':'').' /> Deseo firmar con token en este paso.</label>';
        
        return $html;
    }
    
    public function backendExtraValidate() {
        parent::backendExtraValidate();
        
        $CI= &get_instance();
        $CI->form_validation->set_rules('documento_id','Documento','required');
    }

}