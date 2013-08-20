<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Claveunica extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        UsuarioManagerSesion::force_login();
    }

    public function index() {
        redirect('manager/claveunica/cuentas');
    }

    public function cuentas($cuenta_id = null) {
        if (!$cuenta_id) {

            //Seleccionamos los tramites que se han avanzado o tienen datos
            $tramites = Doctrine_Query::create()
                    ->from('Tramite t, t.Etapas e, e.DatosSeguimiento d, e.Usuario u')
                    ->where('u.open_id = 1')
                    ->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')  //Mostramos solo los que se han avanzado o tienen datos
                    ->groupBy('t.id')
                    ->execute();

            $data['ntramites'] = $tramites->count();
            
            //Los agrupamos por cuenta
            foreach($tramites as $t)
                $tramites_arr[]=$t->id;
            
            $cuentas = Doctrine_Query::create()
                    ->from('Cuenta c, c.Procesos.Tramites t')
                    ->select('c.*, COUNT(t.id) as ntramites')
                    ->whereIn('t.id',$tramites_arr)
                    ->groupBy('c.id')
                    ->execute();

            $data['cuentas'] = $cuentas;

            $data['title'] = 'Cuentas';
            $data['content'] = 'manager/claveunica/cuentas';
        }else{


            $tramites = Doctrine_Query::create()
                    ->from('Tramite t, t.Proceso.Cuenta c, t.Etapas e, e.DatosSeguimiento d, e.Usuario u')
                    ->where('u.open_id = 1 AND c.id = ?',$cuenta_id)
                    ->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')  //Mostramos solo los que se han avanzado o tienen datos
                    ->groupBy('t.id')
                    ->orderBy('t.updated_at DESC')
                    ->execute();

            $data['tramites'] = $tramites;

            $data['title'] = $tramites[0]->Proceso->Cuenta->nombre;
            $data['content'] = 'manager/claveunica/cuenta';
        }

        $this->load->view('manager/template', $data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */