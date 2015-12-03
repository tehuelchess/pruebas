<h2 style="line-height: 28px;">
    Etapas sin asignar
    <!--buscador--> 
    <div class='pull-right'>
        <form class="form-search" method="GET" action="<?= current_url() ?>">
            <div class="input-append">
                <input name="query" value="<?= $query ?>" type="text" class="search-query" />
                <button type="submit" class="btn">Buscar</button>
            </div>
        </form>
    </div>
</h2>
<?php if (count($etapas) > 0): ?>

<table id="mainTable" class="table">
    <thead>
        <tr>
            <th>Nro</th>
            <th>Ref.</th>
            <th>Nombre</th>
            <th>Etapa</th>
            <th>Modificación</th>
            <th>Vencimiento</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($etapas as $e): ?>
            <tr <?=$e->getPrevisualizacion()?'data-toggle="popover" data-html="true" data-title="<h4>Previsualización</h4>" data-content="'.htmlspecialchars($e->getPrevisualizacion()).'" data-trigger="hover" data-placement="bottom"':''?>>
                <td><?=$e->Tramite->id?></td>
                <td class="name"><?//= $e->Tramite->Proceso->nombre ?>
                    <?php 
                        $tramite_nro ='';
                        foreach ($e->getDatosSeguimiento() as $tra_nro){
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
                        foreach ($e->getDatosSeguimiento() as $tra){
                            if($tra->nombre == 'tramite_descripcion'){
                                $tramite_descripcion = $tra->valor;
                            }  
                        }
                        echo $tramite_descripcion != '' ? $tramite_descripcion : $e->Tramite->Proceso->nombre;
                    ?>
                </td>
                <td><?=$e->Tarea->nombre ?></td>
                <td class="time"><?= strftime('%d.%b.%Y',mysql_to_unix($e->updated_at))?><br /><?= strftime('%H:%M:%S',mysql_to_unix($e->updated_at))?></td>
                <td><?=$e->vencimiento_at?strftime('%c',strtotime($e->vencimiento_at)):'N/A'?></td>
                <td class="actions"><a href="<?=site_url('etapas/asignar/'.$e->id)?>" class="btn btn-primary"><i class="icon-check icon-white"></i> Asignármelo</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<p><?= $links ?></p>
<?php else: ?>
<p>No hay trámites para ser asignados.</p>
<?php endif; ?>
