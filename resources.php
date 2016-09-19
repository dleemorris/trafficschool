<?php

class Resources extends Controller {
    
    function __construct() {
        parent::Controller();
		 $this->load->model('cms');
    }
    
    function index(){
		$data['resources'] = $this->cms->getPageContent('Resources');        
       $this->load->view('resources',$data);     
    }    
}
?>