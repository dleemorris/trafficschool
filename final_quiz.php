<?php

/**
 * @author MESMERiZE
 * @copyright 2010
 */
 
/**
 * edits uploaded by JM on 11/29/11
 */

class Final_quiz extends Controller {
    
    function __construct() {
        parent::Controller();
		
        $this->load->model('user');
		$this->load->model('course');		
        $this->load->model('course_content');        
    }
    
    function index() {
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }		
		
        $status = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));   
        
        if($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'No' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE):
			$data = array();
						
			$data['fails']= $this->user->getFails($this->session->userdata('student_id'));     
		
			$profile = $this->user->getProfile($this->session->userdata('student_id'));
			$state = $this->user->getStatesById($profile['state_id']);
			
            $county = $this->course->getCounty($status['county_id']);
            $court = $this->course->getCourt($status['court_id']);
			
			
			$data['profile'] = $profile;
			$data['StateCode'] = $state['state_code'];
			$data['county_key'] = $county['county_key'];
			$data['court_key'] = $court['court_key'];
			$data['course_id'] = $this->session->userdata('course_id'); //~JM Edit //Send course_id over to the final_quiz page
			
            $data['question'] = $this->course_content->getFinalQuestion($this->session->userdata('course_id'));
            $data['cObj'] = $this; 
            $data['err'] = '';            
 
            if(array_key_exists('save',$_POST) && count($_POST['answer']) == count($data['question'])):
                $answer = $this->input->post('answer');
                $result = $this->course_content->validateFinalTestQuestion($answer,$this->session->userdata('course_id'));                                        
                $total = $result['total'];
                $correct = $result['correct'];
                $wrong = $result['wrong'];
                
                $rate = (($correct/$total) * 100);
                $percentage = round($rate);
                if($percentage >= 80):
                    $value = array('final_quiz'=>'Yes'); 
                    $this->user->updateStudentCourse($this->session->userdata('student_id'),$this->session->userdata('course_id'),$value);
                    
                    $profile = $this->user->getProfile($this->session->userdata('student_id'));
                    $data = array('LicenseNumber'=>$profile['license_number'],
                            'Password'=>base64_decode($profile['password']),
                            'StudentName'=>$profile['first_name']." ".$profile['last_name'],
                            'url'=>base_url());      
                    
                    // Parser Email Template 
                    $this->load->library('parser');
                    $htmlmessage = $this->parser->parse('email_template/course_complete',$data, TRUE);
        
                    // Email Config                  
                    $this->load->library('email');
                                
                    $this->email->from('support@702OnlineSchools.com', 'Trafficschool');
                    $this->email->to($profile['email']);
                    
                    $this->email->subject('CONGRATULATIONS! Course Completed - Traffic School');
                    $this->email->message($htmlmessage);
                    $this->email->send();            
                    // End Email Config 
                    
                    redirect('course_completed');
                else:
                    $data['wrong_ids'] = $this->course_content->getFinalWrongAnswer($answer,$this->session->userdata('course_id'));
                    $data['wrong'] = $wrong;
                    $data['total'] = $total;
                    $data['correct'] = $correct;
                    $data['percentage'] = $percentage;
                endif;
            elseif(array_key_exists('save',$_POST)):
                $data['err'] = '<div id="errmsg" style="padding-top:30px;">The Option field value is required for all Question</div>';                                                        
            endif; 
					
			
		
			
			
			
			
            $this->load->view('final_quiz',$data);
            
        elseif($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'Yes' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE):
            redirect('course_completed');
        elseif($status['chapter_completed'] == 'Yes' && $order == FALSE):
            redirect('payment');
        else:
            redirect('table_contents');
        endif;
    }
	
	/*public function getconfirmation() {
		echo "Return URL <hr />";
		var_dump($_POST);
	}*/
	
	public function getforward() {
		
        if(!$this->authenticate->is_login()) {
			
			if($_POST['StudentUserID']!='' && $_POST['StudentDriLicNum']!='') {				
				$data = array('UserID'=>trim($_POST['StudentUserID']),'licenseNumber'=>trim($_POST['StudentDriLicNum']));
				
				if(!$this->authenticate->setAPIUserLogin($data)) {
					redirect('home');
				}
			} else {
				redirect('home');
			}
        }
		
		$profile = $this->user->getProfile($this->session->userdata('student_id'));
		
		if($_POST['StudentUserID']==$profile['student_id'] && $_POST['StudentDriLicNum']==$profile['license_number']) {
			
			if($_POST['Status'] == 'pass') {

				$value = array('final_quiz'=>'Yes','percentage'=>$_POST['Percentage'],'quiz_completed_date' => date('Y-m-d H:i:s')); 
				$this->user->updateStudentCourse($this->session->userdata('student_id'),$this->session->userdata('course_id'),$value);
				
				$data = array('LicenseNumber'=>$profile['license_number'],
						'Password'=>base64_decode($profile['password']),
						'StudentName'=>$profile['first_name']." ".$profile['last_name'],
						'url'=>base_url());
				
				// Parser Email Template 
				$this->load->library('parser');
				$htmlmessage = $this->parser->parse('email_template/course_complete',$data, TRUE);
	
				// Email Config
				$this->load->library('email');

				$this->email->from($this->config->item('OwnerEmail'), $this->config->item('OwnerName'));
				$this->email->to($profile['email']);
				
				$this->email->subject('CONGRATULATIONS! Course Completed - Traffic School');
				$this->email->message($htmlmessage);
				$this->email->send();
				// End Email Config
				
				redirect('course_completed');
			}
			
		} else {
				redirect('home');
		}		
	}

    /*function getFinalQuestionOption($question) {       
        return $this->course_content->getFinalQuestionOption($question);
    }*/
    
}
?>