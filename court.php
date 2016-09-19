<?php

class Court extends Controller {
    
    function __construct(){
        parent::Controller();
        $this->load->model('course');
    }
    
    function index(){
        $state_id = $this->input->post('state');
        $county_id = $this->input->post('county');        
        if($state_id && $county_id) {
            $state = $this->course->getState($state_id); 
            $county = $this->course->getCounty($county_id);
            $data['state_id'] = $state_id;
            $data['county_id'] = $county_id; 
            $data['state'] = $state['state_name'];
            $data['county'] = $county['county_name'];
                    
            $data['court'] = $this->course->getAllCourt($county_id);
            $this->load->view('court',$data);
        } else {
            redirect('home');
        }
    }
}
?>