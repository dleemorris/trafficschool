<?php
class New_york extends Controller {
    
    function __construct() {
        parent::Controller();
    }
    
    function index(){
        $this->load->view('new_york');
    }
}
?>