<div class="row-fluid">
    
    <div class="span3">
        <?php $this->load->view('backend/configuracion/sidebar') ?>
    </div>
    <div class="span9">
        <ul class="breadcrumb">
            <li>
                <a href="<?=site_url('backend/configuracion')?>">Configuración</a> <span class="divider">/</span>
            </li>
            <li class="active">Usuarios</li>
        </ul>
        
        <?php $this->load->view('messages') ?>
        
        <p><a class="btn" href="<?=site_url('backend/configuracion/usuario_editar')?>"><i class="icon-file"></i> Nuevo</a></p>
        
        <table class="table">
            <tr>
                <th>Usuario</th>
                <th>Nombres</th>
                <th>Apellido Paterno</th>
                <th>Apellido Materno</th>
                <th>Pertenece a</th>
                <th>¿Fuera de oficina?</th>
                <th>Acciones</th>
            </tr>
            <?php foreach($usuarios as $u): ?>
            <tr>
                <td><?=$u->usuario?></td>
                <td><?=$u->nombres?></td>
                <td><?=$u->apellido_paterno?></td>
                <td><?=$u->apellido_materno?></td>
                <td>
                    <?php
                    $tmp=array();
                    foreach($u->GruposUsuarios as $g)
                        $tmp[]=$g->nombre;
                    echo implode(', ', $tmp);
                    ?>
                </td>
                <td><?=$u->vacaciones?'Si':'No'?></td>
                <td>
                    <a class="btn" href="<?=site_url('backend/configuracion/usuario_editar/'.$u->id)?>"><i class="icon-edit"></i> Editar</a>
                    <a class="btn" href="<?=site_url('backend/configuracion/usuario_eliminar/'.$u->id)?>" onclick="return confirm('¿Está seguro que desea eliminar?')"><i class="icon-remove"></i> Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>