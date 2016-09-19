<?php

class Apitest extends Controller {

    public function __construct() {
        parent::Controller();
    }
	
	public function index() {	
		
		$this->load->view('api');
	}
	
}