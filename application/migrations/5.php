<?php

class Migration_5 extends Doctrine_Migration_Base {

    public function up() {
        $this->addColumn('tarea', 'grupos_usuarios', 'string', null);
    }

    public function postUp() {
        $tareas = Doctrine::getTable('Tarea')->findAll();

        foreach ($tareas as $t) {
            $grupos = array();
            foreach ($t->GruposUsuarios as $g)
                $grupos[] = $g->id;
            $t->grupos_usuarios = implode(',', $grupos);
            $t->save();
        }
    }

    public function down() {
        $this->removeColumn('tarea', 'grupos_usuarios');
    }

}