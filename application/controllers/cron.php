<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->input->is_cli_request()) {
            echo 'Accion no permitida';
            exit;
    }
    }
    
    public function hourly(){
        //Indexamos las busquedas en Sphinx
        system('cd sphinx; searchd; indexer --rotate --all');
    }

    public function daily() {             
        //Buscamos las etapas que estan por vencer, pendientes y que requieren ser notificadas
        $etapas = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea t')
                ->select('e.*, (CASE t.vencimiento_unidad WHEN "D" THEN (e.created_at + INTERVAL t.vencimiento_valor DAY) WHEN "W" THEN (e.created_at + INTERVAL t.vencimiento_valor WEEK) WHEN "M" THEN (e.created_at + INTERVAL t.vencimiento_valor MONTH) END) as fecha_vencimiento')
                ->where('t.vencimiento = 1 AND e.pendiente = 1 AND t.vencimiento_notificar = 1')
                ->having('DATEDIFF(fecha_vencimiento,NOW()) = 1')
                ->execute();
        foreach ($etapas as $e) {
            echo 'Enviando correo de notificacion para etapa ' . $e->id . "\n";
            $this->email->from('simple@chilesinpapeleo.cl');
            $this->email->to($e->Tarea->vencimiento_notificar_email);
            $this->email->subject('Simple - Etapa se encuentra por vencer');
            $this->email->message('La etapa "' . $e->Tarea->nombre . '" se encuentra a 1 día de vencer.' . "\n\n" . 'Usuario asignado: ' . $e->Usuario->usuario);
            $this->email->send();
        }


        //Hacemos un respaldo de la base de datos
        $backupName = $this->db->database . '_' . date("Ymd-His") . '.gz';
        $command = $this->config->item('mysqldump_location') . ' -h '.$this->db->hostname.' -u '.$this->db->username.' -p'.$this->db->password.' '.$this->db->database.' | gzip > '.$backupName;
        system($command);
        system('sshpass -p '.$this->config->item('backupserver_password').' scp ' . $backupName . ' '.$this->config->item('backupserver_username').'@'.$this->config->item('backupserver_ip').':'.$this->config->item('backupserver_path'));
        system('rm ' . $backupName);
        
        //Limpia los tramites que que llevan mas de 1 dia sin modificarse, sin avanzar de etapa y sin datos ingresados (En blanco).
        $tramites_en_blanco=Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u, e.DatosSeguimiento d')
                ->where('DATEDIFF(NOW(),t.updated_at) >= 1 AND t.pendiente = 1')
                ->groupBy('t.id')
                ->having('COUNT(e.id) = 1 AND COUNT(d.id) = 0')
                ->execute();
        $tramites_en_blanco->delete();
        
        //Limpia los tramites que han sido iniciados por usuarios no registrados, y que llevan mas de 1 dia sin modificarse, y sin avanzar de etapa.
        $tramites_en_primera_etapa=Doctrine_Query::create()
                ->from('Tramite t, t.Etapas e, e.Usuario u')
                ->where('DATEDIFF(NOW(),t.updated_at) >= 1 AND t.pendiente = 1')
                ->groupBy('t.id')
                ->having('COUNT(e.id) = 1')
                ->execute();
        foreach($tramites_en_primera_etapa as $t)
            if($t->Etapas[0]->Usuario->registrado == 0)
                $t->delete();
        
        //Elimino los registros no registrados con mas de 1 dia de antiguedad y que no hayan iniciado etapas
        $noregistrados=Doctrine_Query::create()
                ->from('Usuario u, u.Etapas e')
                ->where('u.registrado = 0 AND DATEDIFF(NOW(),u.updated_at) >= 1')
                ->groupBy('u.id')
                ->having('COUNT(e.id) = 0')
                ->execute();     
        $noregistrados->delete();   
    }
    
    }
