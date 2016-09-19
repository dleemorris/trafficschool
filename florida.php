<?php
class Florida extends Controller {
    
    function __construct() {
        parent::Controller();
    }
    
    function index(){
        $this->load->view('florida');
    }
}
?>