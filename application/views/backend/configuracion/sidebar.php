<ul class="nav nav-list">
    <li class="nav-header">
        General
    </li>
    <li class="<?=strpos($this->uri->segment(3),'misitio')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/misitio')?>">Mi sitio</a>
    </li>
    <li class="<?=strpos($this->uri->segment(3),'plantilla_seleccion')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/plantilla_seleccion')?>">Plantillas de Simple</a>
    </li>
    <li class="<?=strpos($this->uri->segment(3),'modelador')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/modelador')?>">Configuración Modelador</a>
    </li>
    <li style="display:none" class="<?=strpos($this->uri->segment(3),'feriados')===0?'active':''?>"><!-- se deshabilito porque se movio a manager -->
        <a href="<?=site_url('backend/configuracion/feriados')?>">Dias Feriados</a>
    </li>
    <li class="<?=strpos($this->uri->segment(3),'conf_services')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/conf_services')?>">Configuraci&oacute;n Services</a>
    </li>
    <li class="nav-header">
        Accesos Frontend
    </li>
    <li class="<?=strpos($this->uri->segment(3),'usuario')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/usuarios')?>">Usuarios</a>
    </li>
    <li class="<?=strpos($this->uri->segment(3),'grupo')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/grupos_usuarios')?>">Grupos de Usuarios</a>
    </li>
    <li class="nav-header">
        Accesos Backend
    </li>
    <li class="<?=strpos($this->uri->segment(3),'backend_usuario')===0?'active':''?>">
        <a href="<?=site_url('backend/configuracion/backend_usuarios')?>">Usuarios</a>
    </li>

</ul>