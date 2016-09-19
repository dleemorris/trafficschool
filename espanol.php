<?php

class Espanol extends Controller {
    
    function __construct() {
        parent::Controller();
    }
    
    function index(){
        $this->load->view('espanol');
    }
}
?>