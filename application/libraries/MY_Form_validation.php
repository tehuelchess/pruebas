<?php

Class MY_Form_validation extends CI_Form_validation {

    public function __construct($rules = array()) {
        parent::__construct($rules);

        $this->set_error_delimiters('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>', '</div>');
    }

}
