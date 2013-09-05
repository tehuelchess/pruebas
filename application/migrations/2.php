<?php
class Migration_2 extends Doctrine_Migration_Base {
    public function up(){
        $this->addColumn( 'documento', 'subtitulo', 'string' , 128, array( 'notnull' => 1));
        
    }
    
    public function postUp() {
        $documentos=Doctrine::getTable('Documento')->findByTipo('certificado');
        foreach($documentos as $d){
            $d->subtitulo='Certificado Gratuito';
            $d->save();
        }
    }
    
    public function down(){
        $this->removeColumn( 'documento', 'subtitulo' );
    }
}