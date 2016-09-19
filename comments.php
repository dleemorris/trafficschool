<?php

class Comments extends Controller {
    
    function __construct() {
        parent::Controller();
        $this->load->model('cms');
    }
    
    function index(){
        $data['comments'] = $this->cms->getCustomerComment();        
        $this->load->view('comments',$data);
    }
}

?>