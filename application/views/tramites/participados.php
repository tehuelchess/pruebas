<h2 style="line-height: 28px;">
    Solicitudes en que ha participado
    <!--buscador-->   
    <div class='pull-right'>
        <form class="form-search" method="POST" action="<?= preg_replace('/\/\d+/', '', current_url()) ?>">            
            <div class="input-append">
                <input name="query" value="<?= $query ?>" type="text" class="search-query" />
                <button type="submit" class="btn">Buscar</button>
            </div>
        </form>
    </div>
</h2>
<?php if (count($tramites) > 0): ?>
    <table id="mainTable" class="table">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Ref.</th>
                <th>Nombre</th>
                <th>Etapa</th>
                <th>Fecha Modificación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tramites as $t): ?>
                <tr>
                   <td><?= $t->id ?></td>                   
                    <td class="name"><?//= $t->Proceso->nombre ?>
                        <?php 
                            $tramite_nro ='';
                            foreach ($t->getValorDatoSeguimiento() as $tra_nro){
                               if($tra_nro->nombre == 'tramite_ref'){
                                    $tramite_nro = $tra_nro->valor;
                                }                              
                            }                         
                            echo $tramite_nro;
                        ?>
                    </td>
                    <td class="name">  
                        <?php 
                            $tramite_descripcion ='';
                            foreach ($t->getValorDatoSeguimiento() as $tra){
                                if($tra->nombre == 'tramite_descripcion'){
                                    $tramite_descripcion = $tra->valor;
                                }  
                            }
                            echo $tramite_descripcion != '' ? $tramite_descripcion : $t->Proceso->nombre;
                        ?>
                    </td>
                    <td>
                        <?php
                        $etapas_array = array();
                        foreach ($t->getEtapasActuales() as $e)
                            $etapas_array[] = $e->Tarea->nombre;
                        echo implode(', ', $etapas_array);
                        ?>
                    </td>
                    <td class="time"><?= strftime('%d.%b.%Y', mysql_to_unix($t->updated_at)) ?><br /><?= strftime('%H:%M:%S', mysql_to_unix($t->updated_at)) ?></td>
                    <td><?= $t->pendiente ? 'Pendiente' : 'Completado' ?></td>
                    <td class="actions">
                        <?php $etapas = $t->getEtapasParticipadas(UsuarioSesion::usuario()->id) ?>
                        <?php if (count($etapas) == 3e4354) : ?>
                            <a href="<?= site_url('etapas/ver/' . $etapas[0]->id) ?>" class="btn btn-primary">Ver historial</a>
                        <?php else: ?>
                            <div class="btn-group">
                                <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                                    Ver historial
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($etapas as $e): ?>
                                        <li><a href="<?= site_url('etapas/ver/' . $e->id) ?>"><?= $e->Tarea->nombre ?></a></li>
                                    <?php endforeach ?>
                                </ul>
                            </div>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><?= $links ?></p>   
<?php else: ?>
    <p>Ud no ha participado en trámites.</p>
<?php endif; ?>
