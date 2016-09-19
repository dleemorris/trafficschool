<?php

class Privacy extends Controller {
    
    function __construct() {
        parent::Controller();
        $this->load->model('cms');
    }
    
    function index(){
        $data['cms'] = $this->cms->getPageContent('Privacy Policy');
        $this->load->view('privacy',$data);
    }
}
?>