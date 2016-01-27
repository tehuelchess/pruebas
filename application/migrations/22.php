<?php
class Migration_22 extends Doctrine_Migration_Base {
    public function up(){

        $definition = array(
        'local'        => 'cuenta_id',
        'foreign'      => 'id',
        'foreignTable' => 'cuenta',
        'onDelete'     => 'CASCADE',
        'onUpdate'     => 'CASCADE',
        );

        $this->createForeignKey( 'auditoria_operaciones', 'fk_cuenta', $definition );
    }

    public function postUp() {
        $q = Doctrine_Manager::getInstance()->getCurrentConnection();
        $q->execute("UPDATE auditoria_operaciones set cuenta_id=3");
    }

    public function down(){
        $this->removeColumn('auditoria_operaciones', 'cuenta_id');
    }
}
