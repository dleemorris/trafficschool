<?php

class Contactus extends Controller {
    
    function __construct() {
        parent::Controller();
    }
    
    function index(){
        $this->form_validation->set_error_delimiters('<div id="errmsg" class="errmsg">'.$this->config->item('ErrorImage'), '</div>');
        if($this->form_validation->run('contactus')==TRUE):
            $data = array('Name'=>$this->input->post('name'),
                    'Email'=>$this->input->post('email'),
                    'Phone'=>$this->input->post('phone'),
                    'Comment'=>$this->input->post('comment'),
                    'url'=>base_url());
            
            /* Parser Email Template */
            $this->load->library('parser');
            $htmlmessage = $this->parser->parse('email_template/contactus',$data, TRUE);

			if($this->config->item('WORKING') == 'LIVE') {
				/* Email Config */                 
				$this->load->library('email');
							
				$this->email->from($this->input->post('email'), $this->input->post('name'));
				$this->email->to($this->config->item('OwnerEmail')); //testing@digitalbrandgroup.com
				
				$this->email->subject('Contact Mail from Traffic School');
				$this->email->message($htmlmessage);
				$this->email->send();            
				/* End Email Config */
			}
            redirect('contactus');            
        endif;
        
        $this->load->view('contactus');        
    }    
}
?>