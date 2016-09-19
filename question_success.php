<?php

/**
 * @class Table of Contents
 * @copyright 2010
 */

class Question_success extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course_content');
    }
        
    function index() {
	//echo "<pre>";print_r($this->session);
	
        if($this->session->userdata('complete_chapter') && $this->session->userdata('complete_part')):
            $chapter = $this->session->userdata('complete_chapter');
            $part = $this->session->userdata('complete_part');
            if($this->user->ValidateUserCourse($this->session->userdata('course_id'),$chapter,$part)){                
                $data['chapter'] = $this->course_content->getChapterContent($chapter);
                $data['parts'] = $this->course_content->getPartContent($part);
                $data['question'] = $this->course_content->getPartQuestion($part);
                $data['cObj'] = $this;
				 $answer =$this->session->userdata('answer');
				 $data['answer']= $answer;
				 if ($this->course_content->validateTestQuestion($answer, $part) == FALSE) {
                        $data['wrong_ids'] = $this->course_content->getWrongAnswer($answer, $part);
						//echo count($wrong_ids);                       
                    }
				$this->load->view('question_success',$data);
            } else {
                redirect ('table_contents');
            }
        else:
            redirect ('table_contents');
        endif;
    }  
    
    function nextPart() {
        $currentstatus = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));
        if($this->session->userdata('complete_chapter') && $this->session->userdata('complete_part')):            
            $this->session->unset_userdata('complete_chapter');
            $this->session->unset_userdata('complete_part');
            
            if($this->user->findProfileUpdate($this->session->userdata('student_id'),$this->session->userdata('course_id'),$currentstatus['current_chapter_id']) == FALSE) {
                redirect('profile');
            } else if($currentstatus['chapter_completed'] == 'Yes') {
                redirect('payment');
            } else {
                redirect('table_contents/content/'.$currentstatus['current_chapter_id'].'/'.$currentstatus['current_part_id']);    
            }
        else:
            redirect('table_contents/content/'.$currentstatus['current_chapter_id'].'/'.$currentstatus['current_part_id']);           
        endif;
    }  
    
    function getPartQuestionOption($question) {       
        return $this->course_content->getQuestionOption($question);
    }    
      
}
?>