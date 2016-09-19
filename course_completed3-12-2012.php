<?php

/**
 * @author MESMERiZE
 * @copyright 2010
 */

/**
 * edits uploaded by JM on 11/29/11
 * edits uploaded by JM on 1/30/12 
 */

class Course_completed extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
    }
    
    function index() {
        $status = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));        
		$profile = $this->user->getProfile($this->session->userdata('student_id')); //load email for receipt page ~JM
		
		$data = array('email'=>$profile['email'],'first_name'=>$profile['first_name'],'last_name'=>$profile['last_name'],'license_number'=>$profile['license_number']);     
        //$data['fails']= $this->user->getFails($this->session->userdata('student_id'));
        if($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'Yes' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE):        
            $this->load->view('course_completed',$data);
        else:
            redirect('table_contents');
        endif;
    }
}
?>