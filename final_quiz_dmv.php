<?php error_reporting(0);

/**
 * @author MESMERiZE
 * @copyright 2010
 */
 
/**
 * edits uploaded by JM on 11/29/11
 * edits uploaded by JM on 1/30/12
 */

class Final_quiz_dmv extends Controller {
    
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
                    
		$profile = $this->user->getProfile($this->session->userdata('student_id'));
					  
		if( $profile['city']== "Testtown" && $profile['zipcode'] == "97777" )
			$data['reviewer'] = true;						
			$data['fails']= $this->user->getFails($this->session->userdata('student_id'));
		//print_r($data['fails']);
		
		//echo $status['chapter_completed'];
		//echo '<br/>final_quiz'.$status['final_quiz'];
		/*
print_r($status);
echo "ss".$this->user->checkUserOrder($this->session->userdata('student_id'))."ss";
die;

*/
        if($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'No' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE){
			if( !array_key_exists('save',$_POST) ){ //
           		//$data['question'] = $this->course_content->getFinalQuestionDMV($this->session->userdata('course_id'));
				$data['question'] = $this->course_content->getFinalQuestion($this->session->userdata('course_id'));
				//$data['fails']['failed1']=0;
				//$data['fails']['failed2']=0;
				//print_r($data['question']);
				/**
				* begin formulae for determining the 25 questions the student will see ~JM Edit
				*/
				$prevAnswers = Array(); //output a warning for incorrect datatype ~JM
				$prevQnC = Array(); //output a warning for incorrect datatype ~JM
				//new dmv requirements ask us to pull 1 question from chapters 3, 6, 8 and 9 in addition to not having previous answers show up again		
				//see if they have already taken the test, if so, grab the most recent
				$qry = "SELECT f.answer_array, f.date
						FROM `finaltest_log` f 
						WHERE f.license_number = '".$this->session->userdata('license_number')."'
						ORDER BY ID DESC LIMIT 1";
				
				$sql = $this->db->query($qry,array($part_id));
				$res = $sql->result_array();
				foreach($res as $key=>$value){
					$prevQnC = unserialize($value['answer_array']);
				}
				$q = 0; //question and choice array from the database is formatted as follows: ['question']=>'choice' so let's re-format it
				foreach( $prevQnC as $user_question => $user_choice ){
					$prevAnswers[$q] = $user_question;
					$q++;
				}
				//print_r($prevAnswers);
				$qstn = "";
				$qstn = Array();
				$answer = Array();
				$data['cObj'] = $this; 
				$data['err'] = '';            
 
                } elseif(array_key_exists('save',$_POST) ){
					$answer = $this->input->post('answer');
					$result = $this->course_content->validateFinalTestQuestion($answer,$this->session->userdata('course_id'));
					$total = $result['total'];
					$correct = $result['correct']+'0';
					$wrong = $result['wrong'];
					
					$rate = (($correct/$total) * 100);
					$percentage = round($rate);
					$date['percent'] = $percentage;
					if($percentage >= 70){
						$value = array('final_quiz'=>'Yes','disqualified'=>'0','percentage' => $percentage,'quiz_completed_date' => date('Y-m-d H:i:s')); 
						$this->user->updateStudentCourse($this->session->userdata('student_id'),$this->session->userdata('course_id'),$value);
						
						$data = array('LicenseNumber'=>$profile['license_number'],
								'Password'=>base64_decode($profile['password']),
								'StudentName'=>$profile['first_name']." ".$profile['last_name'],
								'url'=>base_url());
		
								
						// /* Parser Email Template */
						 $this->load->library('parser');
						$htmlmessage = $this->parser->parse('email_template/course_complete',$data, TRUE);
						// /* Email Config */                 
						$this->load->library('email');
						$this->email->from('support@702OnlineSchools.com', 'Trafficschool');
						$this->email->to($profile['email']);
						$this->email->subject('CONGRATULATIONS! Course Completed - Traffic School');
						$this->email->message($htmlmessage);
						 if( $_SERVER['SERVER_NAME'] == "bsd-staging" || $_SERVER['SERVER_ADDR'] == "192.168.254.213" ){ 
							// //our configuration differs from production ~JM Edit
							$headers  = 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
							mail($profile['email'],'CONGRATULATIONS! Course Completed - Traffic School',$htmlmessage,$headers);
						} else {
						 $this->email->send();  
						}
						/* End Email Config */
						redirect('course_completed');
					} else {
                    $data['wrong_ids'] = $this->course_content->getFinalWrongAnswer($answer,$this->session->userdata('course_id'));
                    $data['wrong'] = $wrong;
                    $data['total'] = $total;
                    $data['correct'] = $correct;
                    $data['percentage'] = $percentage;
					$data['question'] = $answer;
					 //echo "bbbbbbbbb<pre>";print_r($data); die;
					$i = 0;
					$answerArray = array();
					foreach( $answer as $key=>$value ){
						foreach( $value as $vkey=>$answerID ){
							$answerArray[$key] = $answerID;
						}					
						if( $i == 0 )
							$whereSql = "WHERE finalexam_question_id = '$key'";
						else
							$orSql .= " OR finalexam_question_id = '$key'";
						$i++;
					}
					//echo $finalSql = "SELECT DISTINCT * FROM `finalexam_question` ".$whereSql." ".$orSql; //the sql statement that will grab our required questions
					//$finalSql = "SELECT DISTINCT * FROM `finalexam_question` ".$whereSql." ".$orSql;
					//die;$sql = $this->db->query($finalSql,array($part_id));					
					$data['question'] = $this->course_content->getFinalQuestion($this->session->userdata('course_id'));
					$ser_qst = serialize($answerArray);
					$insertData = array(
						'student_id'		=> $this->session->userdata('student_id'),
						'license_number'	=> $this->session->userdata('license_number'),
						'answer_array'		=> $ser_qst
					);
					//echo "dd".$data['fails']['failed1']."FF".$data['fails']['failed2'];
					/*
					if($data['fails']['failed1'] == 1 && $data['fails']['failed2'] == 0 ){
						$this->user->updateFails($this->session->userdata('student_id'),array('failed1'=>'0','failed2'=>'0','current_chapter_id'=>'1','current_part_id'=>'1','chapter_completed'=>'No'));
						$this->load->view('final_quiz_fail');
						
						
					} else {
					
						$data['fails']['failed1'] = 1;
						$data['fails']['failed2'] = 0;
						$this->user->updateFails($this->session->userdata('student_id'),array('failed1'=>'1'));
					}
					*/
                }
			
				$insert = $this->db->insert_string('finaltest_log', $insertData);
				$this->db->query($insert);
				$data['cObj'] = $this; 
				$data['err'] = '';      
				
            } elseif(array_key_exists('save',$_POST) ){
			   $data['cObj'] = $this; 
				//echo "hirdesh";
				//die;
                $data['err'] = '<div id="errmsg" style="padding-top:30px;">The Option field value is required for all Question</div>';    
				$value=$this->session->userdata('course_id');
				$whereSql ="WHERE course_id = '$value'";
				 $finalSql = "SELECT DISTINCT * FROM `finalexam_question` ".$whereSql." ";
				$sql = $this->db->query($finalSql,array($part_id));
				$data['question'] = $sql->result_array();  
				//print_r($data);              
			}
			//echo $this->session->userdata('student_id');	
				$answer = $this->input->post('answer');		
				$data['wrong_ids'] = $this->course_content->getFinalWrongAnswer($answer,$this->session->userdata('course_id')); 
				//$faild1=$data['fails']['failed1']+1;
				if($data['fails']['failed1']==1){
					$faild2=1;
				} else {
					$faild2=0;
				}
				//echo $faild2;
				//echo $data['fails']['failed1'];die;
				/*
				if(array_key_exists('save',$_POST)){
				 $this->user->updateFails($this->session->userdata('student_id'),array('failed1'=>$data['fails']['failed1'],'failed2'=>$data['fails']['failed2']));	
				 } else {
					$this->user->updateFails($this->session->userdata('student_id'),array('failed1'=>'0','failed2'=>'0'));	
				 }
				 */
				 //die('3');		
				 $this->load->view('final_quiz_dmv',$data);
        } elseif($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'Yes' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE){
            redirect('course_completed');
		} elseif($status['chapter_completed'] == 'Yes' && $order == FALSE){
            redirect('payment');
		} elseif($status['chapter_completed'] == 'No' && $data['fails']['failed1'] == 0 && $data['fails']['failed2']==0){
			$answer = $this->input->post('answer');
			$result = $this->course_content->validateFinalTestQuestion($answer,$this->session->userdata('course_id'));
			$total = $result['total'];
			$correct = $result['correct'];
			$wrong = $result['wrong'];
			$rate = (($correct/$total) * 100);
			$percentage = round($rate);
			$data['wrong'] = $wrong;
			$data['total'] = $total;
			$data['correct'] = $correct;
			$data['percentage'] = $percentage;
			$data['question'] = $answer;
			$this->load->view('final_quiz_failed',$data);
		} else {
            redirect('table_contents');
        }
    }

    function getFinalQuestionOption($question) {       
        return $this->course_content->getFinalQuestionOption($question);
    }
    
}
?>