<?php

class Migration_6 extends Doctrine_Migration_Base {

    public function up() {
        $this->addColumn('documento', 'titulo', 'string', 128, array('notnull'=>1));
    }

    public function postUp() {
        $documentos=Doctrine::getTable('Documento')->findAll();
        
        foreach($documentos as $d){
            if($d->tipo=='certificado'){
                $d->titulo=$d->nombre;
                $d->save();
            }
        }
    }

    public function down() {
        $this->removeColumn('documento', 'titulo');
    }

}