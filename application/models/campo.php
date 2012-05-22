<?php

class Campo extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('posicion');
        $this->hasColumn('tipo');
        $this->hasColumn('formulario_id');
        $this->hasColumn('etiqueta');
        $this->hasColumn('validacion');
        $this->hasColumn('datos');
    }

    function setUp() {
        parent::setUp();

        $this->hasOne('Formulario', array(
            'local' => 'formulario_id',
            'foreign' => 'id'
        ));
    }

    //Despliega la vista de un campo del formulario
    //tramite_id indica al tramite que pertenece este campo
    //modo es visualizacion o edicion
    public function display($modo = 'edicion', $tramite_id = NULL) {
        $display = NULL;

        $validacion = explode('|', $this->validacion);

        $dato_almacenado = array();
        if ($tramite_id) {
            $datos = Doctrine::getTable('Dato')->findByTramiteIdAndNombre($tramite_id, $this->nombre);
            if($datos->count())
                foreach ($datos as $d)
                    $dato_almacenado[] = $d->valor;
            else
                $dato_almacenado[]='';
        }else{
            
            $dato_almacenado[]='';
        }


        if ($this->tipo == 'text') {
            $display.='<label>' . $this->etiqueta . (in_array('required', $validacion) ? '' : ' (Opcional)') . '</label>';
            $display.='<input ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="text" name="' . $this->nombre . '" value="' . $dato_almacenado[0] . '" />';
        }else if ($this->tipo == 'textarea') {
            $display.='<label>' . $this->etiqueta . (in_array('required', $validacion) ? '' : ' (Opcional)') . '</label>';
            $display.='<textarea ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' name="' . $this->nombre . '">' . $dato_almacenado[0] . '</textarea>';
        } else if ($this->tipo == 'select') {
            $display.='<label>' . $this->etiqueta . (in_array('required', $validacion) ? '' : ' (Opcional)') . '</label>';
            $display.='<select name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
            foreach ($this->getDatosFromJSON() as $d) {
                $display.='<option value="' . $d->valor . '" ' . ($d->valor == $dato_almacenado[0] ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
            }
            $display.='</select>';
        } else if ($this->tipo == 'radio') {
            $display.='<label>' . $this->etiqueta . (in_array('required', $validacion) ? '' : ' (Opcional)') . '</label>';
            foreach ($this->getDatosFromJSON() as $d) {
                $display.='<label class="radio">';
                $display.='<input ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="radio" name="' . $this->nombre . '" value="' . $d->valor . '" ' . ($d->valor == $dato_almacenado[0] ? 'checked' : '') . ' />';
                $display.=$d->etiqueta . '</label>';
            }
        } else if ($this->tipo == 'checkbox') {
            $display.='<label>' . $this->etiqueta . (in_array('required', $validacion) ? '' : ' (Opcional)') . '</label>';
            foreach ($this->getDatosFromJSON() as $d) {
                $display.='<label class="checkbox">';
                $display.='<input ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="checkbox" name="' . $this->nombre . '[]" value="' . $d->valor . '" ' . (in_array($d->valor, $dato_almacenado) ? 'checked' : '') . ' />';
                $display.=$d->etiqueta . '</label>';
            }
        }else if ($this->tipo == 'file') {
            $display.='<label>' . $this->etiqueta . (in_array('required', $validacion) ? '' : ' (Opcional)') . '</label>';
            $display.='<div>';
            $display.='<div class="'.($modo=='visualizacion'?'':'file-uploader').'" name="' . $this->nombre . '" value="' . $dato_almacenado[0] . '"></div>';
            $display.='<input type="hidden" name="' . $this->nombre . '" value="' . $dato_almacenado[0] . '" />';
            //if($dato_almacenado[0])
                $display.='<p><a href="'.base_url().'uploads/datos/'.$dato_almacenado[0].'" target="_blank">'.$dato_almacenado[0].'</a></p>';
            $display.='</div>';
        }

        return $display;
    }

    public function setDatosFromArray($datos_array) {
        if ($datos_array) {
            $this->datos = json_encode($datos_array);
        } else {
            $this->datos = NULL;
        }
    }

    public function getDatosFromJSON() {
        return json_decode($this->datos);
    }

}
