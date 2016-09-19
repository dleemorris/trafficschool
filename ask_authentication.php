<?php

/**
 * @class Authentication Quiz
 * @copyright 2010
 */

class Ask_authentication extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course_content');
    }
    
    function index() {
	//echo $this->input->post('question_id');die;
        if(array_key_exists('quiz',$_POST) && $this->input->post('question_id')):
            $question_id = $this->input->post('question_id');
            $option_id = $this->input->post('option_id'); 
            $this->form_validation->set_error_delimiters('<div id="errmsg" >'.$this->config->item('ErrorImage'), '</div>');
            $this->form_validation->set_rules('question', 'Question Option', 'required|trim');
            if($this->form_validation->run() == TRUE):
                if($option_id == $this->input->post('question')):
                    $url = $this->session->userdata('redirect_url');                    
                    $url = substr($url,1);
                    $this->session->unset_userdata('redirect_url');					
					//$newdata = array('renuauthenticate' => 'succ');
					//$this->session->set_userdata('goal','goal');
								
                    $this->user->updateQuestionTime($this->session->userdata('student_id'));
					//die;
					 //redirect('question/index');
                    redirect($url);   
                else:
                    redirect('ask_authentication/failed');
                endif;
            endif;                
        else:
            $question = $this->user->getUserAuthenQuestion($this->session->userdata('student_id'));   
             $count = count($question);
            $key = rand(0,$count);        
            $question_id = $question[$key]['authentication_question_id'];
            $option_id = $question[$key]['authentication_question_option_id'];            
        endif;
                
        if($question_id>0) {
            $data['athentication'] = $this->user->getAuthenQuestion($question_id);  
            $data['cObj'] = $this;      
            $data['question_id'] = $question_id;
            $data['option_id'] = $option_id;
            $this->load->view('ask_authentication',$data);
        }else {
              $this->load->view('table_contents');
			
        }       
    }
    
    function failed() {        
        $this->load->view('authentication_failed');
    }
        
    function getAQuestionOption($question) {       
        return $this->user->getAuthenQuestionOption($question);
    }
    
    
}
?>