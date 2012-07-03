<script src="<?= base_url() ?>assets/js/dashboard.js" type="text/javascript"></script>

<div id="dashboard">
    <div class="row-fluid">
        <?php foreach($widgets as $w):?>
        <div class="span4">
            <div class="widget" data-id="<?=$w->id?>">
            <?php $data['widget']=$w; $this->load->view('backend/portada/widget_load',$data) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>