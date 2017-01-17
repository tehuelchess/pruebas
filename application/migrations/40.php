<?php
class Migration_40 extends Doctrine_Migration_Base {
    
    public function up() {
        $this->addColumn('file', 'alfresco_noderef', 'VARCHAR(255)');
    }
    
    public function down() {
        $this->removeColumn('file', 'alfresco_noderef');
    }
}
