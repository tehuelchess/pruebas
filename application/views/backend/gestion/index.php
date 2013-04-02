<script src="<?= base_url() ?>assets/js/dashboard.js" type="text/javascript"></script>

<div class="row-fluid">
    <div class="span12">
        <p>
        <div class="btn-group">
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-plus icon-white"></i> Nuevo widget <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="<?=site_url('backend/gestion/widget_create/tramite_etapas')?>">Tramite por etapas</a></li>
                <li><a href="<?=site_url('backend/gestion/widget_create/tramites_cantidad')?>">Tramites realizados</a></li>
            </ul>
        </div>
        </p>
    </div>
</div>

<div id="dashboard">

        <?php foreach($widgets as $w):?>
            <div class="widget" data-id="<?=$w->id?>">
            <?php $data['widget']=$w; $this->load->view('backend/gestion/widget_load',$data) ?>
            </div>
        <?php endforeach; ?>
    
    
</div>