<?php

class UsuarioSesion {

    private static $user;

    private function __construct() {
        
    }

    public static function usuario() {

        if (!isset(self::$user)) {

            $CI = & get_instance();

            if (!$user_id = $CI->session->userdata('usuario_id')) {
                return FALSE;
            }

            if (!$u = Doctrine::getTable('Usuario')->find($user_id)) {
                return FALSE;
            }

            self::$user = $u;
        }

        return self::$user;
    }

    public static function force_login() {
        $CI = & get_instance();

        $CI->load->library('LightOpenID');
        if ($CI->lightopenid->mode == 'id_res') {
            self::login_open_id();
        }

        if (!self::usuario()) {
            //Elimino los antiguos
            Doctrine::getTable('Usuario')->cleanNoRegistrados();

            //Creo un usuario no registrado
            $usuario = new Usuario();
            $usuario->usuario = random_string('unique');
            $usuario->setPasswordWithSalt(random_string('alnum', 32));
            $usuario->registrado = 0;
            $usuario->save();

            $CI->session->set_userdata('usuario_id', $usuario->id);
            self::$user = $usuario;
        }
    }

    public static function login($usuario, $password, $cuenta_id) {
        $CI = & get_instance();

        $autorizacion = self::validar_acceso($usuario, $password);

        if ($autorizacion) {
            $u = Doctrine::getTable('Usuario')->findOneByUsuarioAndCuentaIdAndOpenId($usuario,$cuenta_id,0);

            //Logueamos al usuario
            $CI->session->set_userdata('usuario_id', $u->id);
            self::$user = $u;

            return TRUE;
        }

        return FALSE;
    }

    public static function validar_acceso($usuario, $password) {
        $u = Doctrine::getTable('Usuario')->findOneByUsuarioAndOpenId($usuario,0);

        if ($u) {

            // this mutates (encrypts) the input password
            $u_input = new Usuario();
            $u_input->setPasswordWithSalt($password, $u->salt);

            // password match (comparing encrypted passwords)
            if ($u->password == $u_input->password) {
                unset($u_input);


                return TRUE;
            }

            unset($u_input);
        }

        // login failed
        return FALSE;
    }

    private static function login_open_id() {
        $CI = & get_instance();
        if ($CI->lightopenid->validate() && strpos($CI->lightopenid->identity,'https://www.claveunica.cl/')===0) {
            $atributos = $CI->lightopenid->getAttributes();
            $usuario = Doctrine::getTable('Usuario')->findOneByUsuarioAndOpenId($CI->lightopenid->identity,1);
            if (!$usuario) {
                $usuario = new Usuario();
                $usuario->usuario = $CI->lightopenid->identity;
                $usuario->registrado = 1;
                $usuario->open_id=1;
            }
            $usuario->rut=$atributos['person/guid'];
            $usuario->save();

            $CI->session->set_userdata('usuario_id', $usuario->id);
            self::$user = $usuario;
        }
    }

    public static function logout() {
        $CI = & get_instance();
        self::$user = NULL;
        $CI->session->unset_userdata('usuario_id');
    }

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

}

?>