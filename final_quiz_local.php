<?php

/**
 * @author MESMERiZE
 * @copyright 2010
 */

class Final_quiz extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course_content');        
    }
    
    function index() {
        $status = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));        
        
        if($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'No' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE):
             
            $question = $this->course_content->getFinalQuestion($this->session->userdata('course_id'));
			$data['total_question'] = count($question);
			$data['question'] = $question;
            $data['cObj'] = $this; 
            $data['err'] = '';
			
			//echo count($_POST['answer']). "||". count($data['question']);
			//exit;
			
			//&& count($_POST['answer']) == count($data['question'])			 
            if($this->input->server('REQUEST_METHOD') == 'POST' && array_key_exists('save',$_POST) && $this->input->post('hd_total_question') > 0):
			
                $answer = $_POST['answer']; //$this->input->post('answer');
                $total = $this->input->post('hd_total_question'); //$result['total'];				
				
                $result = $this->course_content->validateFinalTestQuestion($answer,$total,$this->session->userdata('course_id'));                                        

                $correct = $result['correct'];
                $wrong = $result['wrong'];
                
                $rate = (($correct/$total) * 100);				
                $percentage = round($rate);
				
				//echo $total."||".$correct."||".$wrong."||".$percentage; exit;
				
                if($percentage >= 80):
				
					$value = array('final_quiz'=>'Yes','percentage'=>$percentage,'quiz_completed_date' => date('Y-m-d H:i:s')); 
                    $this->user->updateStudentCourse($this->session->userdata('student_id'),$this->session->userdata('course_id'),$value);
					
					
					if($this->config->item('WORKING') == 'LIVE') {
										                    
						$profile = $this->user->getProfile($this->session->userdata('student_id'));
						$data = array('LicenseNumber'=>$profile['license_number'],
										'Password'=>base64_decode($profile['password']),
										'StudentName'=>$profile['first_name']." ".$profile['last_name'],
										'url'=>base_url()); 




										
						
						/* Parser Email Template */
						$this->load->library('parser');
						$htmlmessage = $this->parser->parse('email_template/course_complete',$data, TRUE);
			
						/* Email Config */                 
						$this->load->library('email');
									
						$this->email->from($this->config->item('OwnerEmail'), $this->config->item('OwnerName'));
						$this->email->to($profile['email']);
						
						$this->email->subject('CONGRATULATIONS! Course Completed - Traffic School');
						$this->email->message($htmlmessage);
						$this->email->send();
						/* End Email Config */
					}
                    
                    redirect('course_completed');
                else:
                    /*$data['wrong_ids'] = $this->course_content->getFinalWrongAnswer($answer,$this->session->userdata('course_id'));
                    $data['wrong'] = $wrong;
                    $data['total'] = $total;
                    $data['correct'] = $correct;
                    $data['percentage'] = $percentage;*/
					
					redirect('final_quiz/quiz_fail/'.$total.'/'.$correct.'/'.$percentage);
					
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

    function getFinalQuestionOption($question) {       
        return $this->course_content->getFinalQuestionOption($question);
    }
    
	public function quiz_fail() {
		
		$total = $this->uri->segment(3);
		$correct = $this->uri->segment(4);
		$percentage = $this->uri->segment(5);
		
		$this->load->view('final_quiz_failed', array('total'=>$total,'correct'=>$correct,'percentage'=>$percentage));
	}
}
?>