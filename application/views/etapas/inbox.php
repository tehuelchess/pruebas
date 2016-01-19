<h2 style="line-height: 28px;">
    Bandeja de Entrada
    <!--buscador-->   
    <div class='pull-right'>
        <form class="form-search" method="GET" action="<?= current_url() ?>">
            <div class="input-append">
                <input name="buscar" value="<?= $buscar?>" type="text" class="search-query" />
                <button type="submit" class="btn">Buscar</button>
            </div>
        </form>
    </div>
</h2>
<?php if (count($etapas) > 0): ?>

<table id="mainTable" class="table">
    <thead>
        <tr>
            <th><a href="<?=current_url().'?orderby=id&direction='.($direction=='asc'?'desc':'asc')?>">Nro</a></th>
            <th>Ref.</th>
            <th><a href="<?=current_url().'?orderby=proceso_nombre&direction='.($direction=='asc'?'desc':'asc')?>">Nombre</a></th>
            <th><a href="<?=current_url().'?orderby=tarea_nombre&direction='.($direction=='asc'?'desc':'asc')?>">Etapa</a></th>
            <th><a href="<?=current_url().'?orderby=updated_at&direction='.($direction=='asc'?'desc':'asc')?>">Modificación</a></th>
            <th><a href="<?=current_url().'?orderby=vencimiento_at&direction='.($direction=='asc'?'desc':'asc')?>">Vencimiento</a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($etapas as $e): ?>
            <tr <?=$e->getPrevisualizacion()?'data-toggle="popover" data-html="true" data-title="<h4>Previsualización</h4>" data-content="'.htmlspecialchars($e->getPrevisualizacion()).'" data-trigger="hover" data-placement="bottom"':''?>>
                <td><?=$e->Tramite->id?></td>
                <td class="name">
                    <?php 
                          $tramite_nro = '';
                          foreach ($e->getDatosSeguimiento() as $tra_nro){
                             if($tra_nro->nombre == 'tramite_ref'){
                                  $tramite_nro = $tra_nro->valor;
                             }                              
                          }                         
                          echo $tramite_nro;
                    ?>                        
                </td><!--Nro. tramites-->                
                <!--<td class="name"><a class="preventDoubleRequest" href="<?//=site_url('etapas/ejecutar/'.$e->id)?>"><?//= $e->Tramite->Proceso->nombre ?></a></td> Nombre-->
                <td class="name"><a class="preventDoubleRequest" href="<?=site_url('etapas/ejecutar/'.$e->id)?>">
                     <?php 
                          $tramite_descripcion ='';
                          foreach ($e->getDatosSeguimiento() as $tra){
                             if($tra->nombre == 'tramite_descripcion'){
                                  $tramite_descripcion = $tra->valor;
                             }  
                          }
                         echo $tramite_descripcion != '' ? $tramite_descripcion : $e->Tramite->Proceso->nombre;
                    ?>                    
                </a></td><!--Tramites-->                
                <td><?=$e->Tarea->nombre?></td>
                <td class="time"><?= strftime('%d.%b.%Y',mysql_to_unix($e->updated_at))?><br /><?= strftime('%H:%M:%S',mysql_to_unix($e->updated_at))?></td>
                <td><?=$e->vencimiento_at?strftime('%c',strtotime($e->vencimiento_at)):'N/A'?></td>
                <td class="actions">
                    <a href="<?=site_url('etapas/ejecutar/'.$e->id)?>" class="btn btn-primary preventDoubleRequest"><i class="icon-edit icon-white"></i> Realizar</a>
                    <!--<?php if($e->netapas==1):?><a href="<?=site_url('tramites/eliminar/'.$e->tramite_id)?>" class="btn" onclick="return confirm('¿Esta seguro que desea eliminar este tramite?')"><i class="icon-trash"></i></a><?php endif ?>-->
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php else: ?>
<p>No hay trámites pendientes en su bandeja de entrada.</p>
<?php endif; ?>
