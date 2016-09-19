<?php

class Getstart extends Controller {
    
    function __construct() {
        parent::Controller();
		$this->load->model('course');
    }
    
    function index(){
		$state = $this->course->getStateByIds('5, 10, 31, 33');
        $this->load->view('get_start',array('state'=>$state));
    }
}

?>