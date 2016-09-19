<?php
class New_jersey extends Controller {
    
    function __construct() {
        parent::Controller();
    }
    
    function index(){
        $this->load->view('new_jersey');
    }
}
?>