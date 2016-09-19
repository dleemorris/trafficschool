<?php

class Terms extends Controller {
    
    function __construct() {
        parent::Controller();
        $this->load->model('cms');
    }
    
    function index(){
        $data['cms'] = $this->cms->getPageContent('Website Terms of Use');
        $this->load->view('terms',$data);
    }
}
?>