<?php foreach($etapas as $e):?>
<ol>
    <li>
        <p>Estado: <?=$e->pendiente==0?'Completado':'Pendiente'?></p>
        <p><?=$e->created_at?'Inicio: '.strftime('%c',mysql_to_unix($e->created_at)):''?></p>
        <p><?=$e->ended_at?'Término: '.strftime('%c',mysql_to_unix($e->ended_at)):''?></p>
        <p>Asignado a: <?=!$e->usuario_id?'Ninguno':!$e->Usuario->registrado?'No registrado':$e->Usuario->open_id?$e->Usuario->nombre.' '.$e->Usuario->apellidos:$e->Usuario->usuario?></p>
        <p><a href="<?=site_url('backend/seguimiento/ver_etapa/'.$e->id)?>">Revisar detalle</a></p> 
        <hr />
    </li>
</ol>
<?php endforeach; ?>
