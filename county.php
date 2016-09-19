<?php

class County extends Controller {
    
    function __construct(){
        parent::Controller();
        $this->load->model('course');
    }
    
    function index(){
        $state_id = $this->input->post('state');
        if($state_id>0) {		
			switch($state_id) {
				case 10: //Florida State
					redirect('florida');
				break;				
				case 31: //New Jersey State
					redirect('new_jersey');
				break;				
				case 33: //New York State
					redirect('new_york');				
				break;				
				default: //Other States
					$state = $this->course->getState($state_id);
					$data['county'] = $this->course->getAllCounty($state_id);
					$data['state_id'] = $state_id;
					$data['state'] = $state['state_name'];
					$this->load->view('county',$data);
				break;
			}
        } else {
            redirect('home');
        }
        /*$this->form_validation->set_error_delimiters('<div id="errmsg">'.$this->config->item('ErrorImage'), '</div>');
        $this->form_validation->set_rules('county', 'County', 'required');                
        if(array_key_exists('continue',$_POST)):        
            if($this->form_validation->run() == TRUE):
                $url = 'court/index/'.$state_id.'/'.$this->input->post('county');
                redirect($url);
            endif;
        endif;*/
    }
}
?>