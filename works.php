<?php

class Works extends Controller {
    
    function __construct() {
        parent::Controller();
         $this->load->model('cms');
    }
    
    function index(){
        $data['cms'] = $this->cms->getPageContent('About Us');
        $this->load->view('works',$data);
    }
}
?>