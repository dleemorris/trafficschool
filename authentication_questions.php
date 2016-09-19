<?php

/**
 * @class Table of Contents
 * @copyright 2010
 */

class Authentication_questions extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course_content');
    }
    
    function index() {
      // echo "<pre>"; print_r($this->session);
	  //echo $this->session->userdata('student_id');
	  //echo 'nnnnnnnnnnnn'.$this->session->userdata('course_id');
        if($this->user->checkUserAQuestionSet($this->session->userdata('student_id'))==FALSE && $this->user->checkAQuestionAvailable() == TRUE):	
		//if($this->session->userdata('student_id')):
		//echo 'vvbnbv';
		  //$data['question']=$this->user->getAuthenQuestionsByCourse($this->session->userdata['course_id']);	
			//die;
			$data['question'] = $this->user->getAllAuthenQuestion();
			//print_r($data);
            $data['cObj'] = $this;
			
            if(array_key_exists('save',$_POST)):                
                $this->form_validation->set_error_delimiters('<div id="errmsg">'.$this->config->item('ErrorImage'), '</div>');
                if($this->form_validation->run('auth_question')== TRUE):
                    $value = $this->input->post('question');
                    $this->user->insertUserAQuestion($this->session->userdata('student_id'),$value);
                    //$this->user->updateProfile($this->session->userdata('student_id'),array('profile_updated'=>'Yes'));
                    redirect('table_contents');
                endif;
            endif;            
			
            $this->load->view('authen_question', $data);
        else:
            redirect ('table_contents');
        endif;
    }    
    
    function getAQuestionOption($question) {       
        return $this->user->getAuthenQuestionOption($question);
    }
    
    function checkOptions($sel) {
        $flag = TRUE;
        $qust = $this->input->post('question[]');
        if(is_array($qust)):       
            foreach($qust as $ksy=>$val) {
                if($val==''):
                    $flag = FALSE;
                endif;
            }
            if($flag):
                return TRUE;
            else:
                $this->form_validation->set_message('checkOptions', '%s value is required for all Question');
                return FALSE;
            endif;
        else:
            return TRUE;
        endif;
    } 
}
?>