<?php

class Faq extends Controller {
    
    function __construct() {
        parent::Controller();
        $this->load->model('cms');
    }
    
    function index(){
        $data['cms'] = $this->cms->getPageContent('FAQ');
        $this->load->view('faq',$data);
    }
}
?>