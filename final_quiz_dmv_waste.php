<?php

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
        $this->load->model('course');        
    }
    
    function index() {
        $status = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));  
                    
		$profile = $this->user->getProfile($this->session->userdata('student_id'));
			
		if( $profile['city']== "Testtown" && $profile['zipcode'] == "97777" )
			$data['reviewer'] = true;
						
		$data['fails']= $this->user->getFails($this->session->userdata('student_id'));

        if($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'No' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE):

			if( !array_key_exists('save',$_POST) ): //
             
           		$data['question'] = $this->course_content->getFinalQuestionDMV($this->session->userdata('course_id'));
				

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
		
		foreach($res as $key=>$value)
			$prevQnC = unserialize($value['answer_array']);
			
		$q = 0; //question and choice array from the database is formatted as follows: ['question']=>'choice' so let's re-format it
		foreach( $prevQnC as $user_question => $user_choice ){
			$prevAnswers[$q] = $user_question;
			$q++;
		}
		
		//print_r($prevAnswers);

		$qstn = "";
		$qstn = Array();
		$answer = Array();
		
		$chap3Pool = array(); //will hold the chapter 3 questions
		$chap6Pool = array(); //will hold the chapter 6 questions
		$chap8Pool = array(); //will hold the chapter 8 questions
		$chap9Pool = array(); //will hold the chapter 9 questions
		$mainPool = array(); //will hold all the rest of the questions
		
		foreach( $data['question'] as $key => $question ){
		
			$chap = $question['chap'];
			$qid = $question['finalexam_question_id'];
			
			if( ($chap == 3) && !in_array($qid, $prevAnswers) )
				array_push($chap3Pool, $qid); //jump in the pool
			elseif( ($chap == 6) && !in_array($qid, $prevAnswers) )
				array_push($chap6Pool, $qid); //jump in the pool
			elseif( ($chap == 8) && !in_array($qid, $prevAnswers) )
				array_push($chap8Pool, $qid); //jump in the pool
			elseif( ($chap == 9) && !in_array($qid, $prevAnswers) )
				array_push($chap9Pool, $qid); //jump in the pool
			else
				if( !in_array($qid, $prevAnswers) )
					array_push($mainPool, $qid); //sloppy fifths
		}
		$chap3Key = array();
		$chap6Key = array();
		$chap8Key = array();
		$chap9Key = array();
		if(count($chap3Pool)>0){
			$chap3Key = array_rand($chap3Pool, 1); //randomly grab a key
		}
		if(count($chap6Pool)>0){
			$chap6Key = array_rand($chap6Pool, 1); //randomly grab a key
		}
		if(count($chap8Pool)>0){
			$chap8Key = array_rand($chap8Pool, 1); //randomly grab a key
		}
		if(count($chap9Pool)>0){
			$chap9Key = array_rand($chap9Pool, 1); //randomly grab a key
		}
		/*
		$chap3Key = array_rand($chap3Pool, 1); //randomly grab a key
		$chap6Key = array_rand($chap6Pool, 1); //randomly grab a key
		$chap8Key = array_rand($chap8Pool, 1); //randomly grab a key
		$chap9Key = array_rand($chap9Pool, 1); //randomly grab a key
		*/
		$keyTotals = count($chap3Key) + count($chap6Key) + count($chap8Key) + count($chap9Key); //if this doesn't equal 4 for some reason, mainPool will make up for it below
		shuffle($mainPool); //do the shuffle
		
		while( count($mainPool) > (25 - $keyTotals) ) //we want to be left with 25 values
			array_pop($mainPool);
		if(count($chap3Pool)>0 && isset($chap3Pool[$chap3Key]) && count($chap6Pool)>0 && isset($chap6Pool[$chap6Key]) && count($chap8Pool)>0 && isset($chap8Pool[$chap8Key]) && count($chap9Pool)>0 && isset($chap9Pool[$chap9Key])){
			array_push($mainPool, $chap3Pool[$chap3Key], $chap6Pool[$chap6Key], $chap8Pool[$chap8Key], $chap9Pool[$chap9Key]);
		}
		
		
		shuffle($mainPool); //one last shuffle //$mainPool is the random (leftover) array of questions we should pull from the db
		
		//print_r($mainPool);

		$data['question'] = array(); //reset this array
		
		$i = 0;
		
		foreach( $mainPool as $key=>$value ){
			if( $i == 0 )
				$whereSql = "WHERE finalexam_question_id = '$value'";
			else
				$orSql .= " OR finalexam_question_id = '$value'";
				
			$i++;
		}
		if($wheresql!="" || $orSql!=""){
			$finalSql = "SELECT DISTINCT * FROM `finalexam_question_dmv` ".$whereSql." ".$orSql; //the sql statement that will grab our required questions
		} else {
			$finalSql = "SELECT DISTINCT * FROM `finalexam_question_dmv` WHERE course_id='".$this->session->userdata('course_id')."' order by rand() LIMIT 0,25";
		}
		
		$sql = $this->db->query($finalSql,array($part_id));
		
		$data['question'] = $sql->result_array();

/**
 * end formulae for determining the 25 questions the student will see ~JM Edit
 */

            $data['cObj'] = $this; 
            $data['err'] = '';            
 
            elseif(array_key_exists('save',$_POST) && count($_POST['answer']) == 25):
			
                $answer = $this->input->post('answer');
                $result = $this->course_content->validateFinalTestQuestionDMV($answer,$this->session->userdata('course_id'));                                        
                $total = $result['total'];
                $correct = $result['correct'];
                $wrong = $result['wrong'];
                
                $rate = (($correct/$total) * 100);
                $percentage = round($rate);

                if($percentage >= 70):	
					$completionDate = date('Y-m-d H:i:s',strtotime('now'));
                    $value = array('final_quiz'=>'Yes','disqualified'=>'0','percentage'=>$percentage,'quiz_completed_date'=>$completionDate); 
                    $this->user->updateStudentCourse($this->session->userdata('student_id'),$this->session->userdata('course_id'),$value);
					$getCourtName = $this->course->getCourt($status['court_id']); 
					$getStateName = $this->course->getState($profile['state_id']);
                    $data = array('LicenseNumber'=>$profile['license_number'],
                            'Password'=>base64_decode($profile['password']),
                            'StudentName'=>$profile['first_name']." ".$profile['last_name'],
                            'url'=>base_url(),'docketNumber'=>$profile['docket_number'],'dob'=>$profile['dob'],'completionDate'=>$completionDate,'county'=>$getCourtName['county_name'],'court'=>$getCourtName['court_name']);
					
                    /* Parser Email Template */
                    $this->load->library('parser');
                    $htmlmessage = $this->parser->parse('email_template/course_complete',$data, TRUE);
					$this->load->helper(array('dompdf', 'file'));
                    $this->load->helper('file'); 
                    $html = $this->load->view('pdfile', $data, true);
                 
					$data = pdf_create($html, '', false);
					
					write_file("certificate/".$profile['license_number'].".pdf", $data);
                    /* Email Config */                 
                    $this->load->library('email');
                                
                    $this->email->from('support@702OnlineSchools.com', 'Trafficschool');
                    $this->email->to($profile['email']);
                    /*$this->email->to('avistcengg@gmail.com');*/
                    $this->email->subject('CONGRATULATIONS! Course Completed - Traffic School');
                    $this->email->message($htmlmessage);
                    $this->email->attach("certificate/".$profile['license_number'].".pdf");
					if( $_SERVER['SERVER_NAME'] == "bsd-staging" || $_SERVER['SERVER_ADDR'] == "192.168.254.213" ){ //our configuration differs from production ~JM Edit
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						mail($profile['email'],'CONGRATULATIONS! Course Completed - Traffic School',$htmlmessage,$headers);
					}else{
                    	$this->email->send(); 
						}
					if($profile['doc_option']=='1'):
						$this->load->library('word');
						//our docx will have 'lanscape' paper orientation
						$section = $this->word->createSection(array('orientation'=>null,'marginTop'=>100,'marginLeft'=>100,'marginRight'=>100,'marginBottom'=>0 ));
						$this->word->setDefaultFontName('Arial');
						$this->word->setDefaultFontSize(10);
						//$section->addImage(FCPATH.'/images/logo.jpg',array( 'align'=>'left','width'=>'100','height'=>'100' ) );
						/*
						$header = $section->createHeader();
						$header->addImage(FCPATH.'/images/dmv.jpg',array( 'align'=>'left','width'=>'800','height'=>'100' ) );
						*/
						$styleFont = array('bold'=>true, 'size'=>15);
						$ownStyle = array('bold'=>true,'size'=>10,'padding'=>0);
						$ownStyle1 = array('size'=>10,'paddingTop'=>0,'paddingBottom'=>0,'marginTop'=>0,'marginBottom'=>0);
						$ownStyle2 = array('size'=>10,'paddingTop'=>0,'paddingBottom'=>0,'marginTop'=>0,'marginBottom'=>0);
						$normalStyle = array('size'=>9);
						$styleParagraph = array('align'=>'center');
						$section->addImage(FCPATH.'/images/dmv.jpg',array( 'align'=>'left','width'=>'800','height'=>'100' ));
						$section->addText('TRAFFIC SAFETY SCHOOL COMPLETION NOTICE', $styleFont, $styleParagraph);					
						$section->addText('       Student Name:      '.$profile['first_name']." ".$profile['last_name'],$ownStyle);
						$section->addText('       Student Address:  '.$profile['address'].'   '.$profile['city'].' '.$getStateName['state_name'].'-'.$profile['zipcode'],$ownStyle);
						$section->addText('                                             		Street Address            City              State-Zip',$normalStyle);
						$section->addText('       Driving License Number:    '.$profile['license_number'].'  Date Of Birth  '.$profile['dob'],$ownStyle);
						$section->addText('       A:    I have Traffic violations depending during my enrollment in this course.',$ownStyle1);
						$section->addText('                         []   Yes                      [] No',$ownStyle1);
						$section->addText('       B:    The court is reducing or dismissing my ticket upon completion of traffic school.',$ownStyle1);
						$section->addText('                         [] Yes                        [] No',$ownStyle1);
						$section->addText('       C:    I have completed a traffic safety course for credit within the past 12 months period.',$ownStyle1);
						$section->addText('                         [] Yes                     [] No',$ownStyle1);
						$section->addText('       D:    Number of traffic violations in the past 12 months          ________________________',$ownStyle1);
						$section->addText('       I hereby certify that all statements on this form are true. ',$ownStyle1);
						$section->addText('       I agree and understand that:',$ownStyle1);
						$section->addText('       		1. No demerit points may be deleted from or credited to my demerit record if my enrollment is in',$ownStyle1);
						$section->addText('                    conjunction with a plea agreement or was a condition of sentencing, or if there are more than ',$ownStyle1);
						$section->addText('                    11 demerits on my drive record.',$ownStyle1);
						$section->addText('            	2. I will not be eligible for the deletion of demerit points and may not otherwise receive credit for',$ownStyle1);
						$section->addText('                 completing a traffic safety course if I received credit for a course within the past 12-month period.',$ownStyle1 );
						$section->addText('     	  _____________________________     DATE    __________________________________',$ownStyle2);
						$section->addText('             	 STUDENT SIGNATURE',$ownStyle2);

						$section->addText('     	TO BE COMPLETED BY SCHOOL OFFICIAL:',$ownStyle);						
						$section->addText('     	SCHOOL NAME    A Approved Traffic School        SCHOOL LICENSE#  TSS000041522                  ',$normalStyle);			
						$section->addText('     	COURSE ATTENDED   Nevada Traffic School                                         ',$normalStyle);			
						$section->addText('     	HOURS OF INSTRUCTION  			5		     DATE COMPLETED  	'.date('M/d/Y',strtotime($completionDate)),$normalStyle);				
						$section->addText('     	TEST SCORE:  '.$percentage,$normalStyle);						
						$section->addText('     	INSTRUCTOR NAME:  702 DUI School ',$normalStyle);				
						$section->addText('     	INSTRUCTOR SIGNATURE:  ',$normalStyle);			
						$section->addImage(FCPATH.'/images/signature.jpg',array( 'align'=>'center','width'=>'150','height'=>'50' ));
						$section->addText('      	Mail form to: Department of Motor Vehicles, Central Services and Record Division, 555 Wright Way Carson City, Nevada 89711,',$normalStyle);	
						$section->addText('      	Attention: Data Integrity',$normalStyle);	 						

						$objWriter = PHPWord_IOFactory::createWriter($this->word, 'Word2007');
						$filenameDOCX=$profile['license_number'].'.docx';
						$objWriter->save("certificate/".$filenameDOCX);
						$docForm = array('StudentName'=>$profile['first_name']." ".$profile['last_name'] ,'url'=>base_url());
						 $this->load->library('parser');
						 $htmlmessage = $this->parser->parse('email_template/doc_form',$docForm, TRUE);
						 $this->email->from('support@702OnlineSchools.com', 'Trafficschool');
						$this->email->to($profile['email']);
						/*$this->email->to('avistcengg@gmail.com');*/
						$this->email->subject('** Important Message from 702TrafficSchool.com **');
						$this->email->message($htmlmessage);
						$this->email->attach("certificate/".$filenameDOCX);
						if( $_SERVER['SERVER_NAME'] == "bsd-staging" || $_SERVER['SERVER_ADDR'] == "192.168.254.213" ){ //our configuration differs from production ~JM Edit
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						mail($profile['email'],'CONGRATULATIONS! Course Completed - Traffic School',$htmlmessage,$headers);
						}else{
                    	$this->email->send(); 
						}
					/*End Attached Word File*/
					endif;
					/*
					if( $_SERVER['SERVER_NAME'] == "bsd-staging" || $_SERVER['SERVER_ADDR'] == "192.168.254.213" ){ //our configuration differs from production ~JM Edit
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						mail($profile['email'],'CONGRATULATIONS! Course Completed - Traffic School',$htmlmessage,$headers);
					}else
                    	$this->email->send();       
                      */
                    /* End Email Config */
                    
                    redirect('course_completed');
                else:
                    $data['wrong_ids'] = $this->course_content->getFinalWrongAnswerDMV($answer,$this->session->userdata('course_id'));
                    $data['wrong'] = $wrong;
                    $data['total'] = $total;
                    $data['correct'] = $correct;
                    $data['percentage'] = $percentage;
					$data['question'] = $answer;

					$i = 0;
					
					$answerArray = array();
						
					foreach( $answer as $key=>$value ){
						
						foreach( $value as $vkey=>$answerID )
							$answerArray[$key] = $answerID;
					
						if( $i == 0 )
							$whereSql = "WHERE finalexam_question_id = '$key'";
						else
							$orSql .= " OR finalexam_question_id = '$key'";
							
						$i++;
					}
					
					$finalSql = "SELECT DISTINCT * FROM `finalexam_question_dmv` ".$whereSql." ".$orSql; //the sql statement that will grab our required questions
					
					$sql = $this->db->query($finalSql,array($part_id));
					
					$data['question'] = $sql->result_array();
					
					$ser_qst = serialize($answerArray);
									
					$insertData = array(
						'student_id'		=> $this->session->userdata('student_id'),
						'license_number'	=> $this->session->userdata('license_number'),
						'answer_array'		=> $ser_qst
					);
				
					if( $data['fails']['failed1'] == 1 && $data['fails']['failed2'] == 0 ){
						$this->user->updateFails($this->session->userdata('student_id'),array('failed1'=>'0','failed2'=>'0','current_chapter_id'=>'1','current_part_id'=>'1','chapter_completed'=>'No'));
					}else
						$this->user->updateFails($this->session->userdata('student_id'),array('failed1'=>'1'));
					
                endif;
	
				$insert = $this->db->insert_string('finaltest_log', $insertData);
				$this->db->query($insert);

            $data['cObj'] = $this; 
            $data['err'] = '';      
				
            elseif(array_key_exists('save',$_POST)):
            $answer = $this->input->post('answer');
                $result = $this->course_content->validateFinalTestQuestionDMV($answer,$this->session->userdata('course_id'));                                        
                $total = $result['total'];
                $correct = $result['correct'];
                $wrong = $result['wrong'];
				$data['wrong_ids'] = $this->course_content->getFinalWrongAnswerDMV($answer,$this->session->userdata('course_id'));
                    $data['wrong'] = $wrong;
                    $data['total'] = $total;
                    $data['correct'] = $correct;
                    $data['percentage'] = $percentage;
					$data['question'] = $answer;

					$i = 0;
					
					$answerArray = array();
						
					foreach( $answer as $key=>$value ){
						
						foreach( $value as $vkey=>$answerID )
							$answerArray[$key] = $answerID;
					
						if( $i == 0 )
							$whereSql = "WHERE finalexam_question_id = '$key'";
						else
							$orSql .= " OR finalexam_question_id = '$key'";
							
						$i++;
					}
					
					$finalSql = "SELECT DISTINCT * FROM `finalexam_question_dmv` ".$whereSql." ".$orSql; //the sql statement that will grab our required questions
					
					$sql = $this->db->query($finalSql,array($part_id));
					
					$data['question'] = $sql->result_array();
					
					$ser_qst = serialize($answerArray);
				 $data['cObj'] = $this; 
                $data['err'] = '<div id="errmsg" style="padding-top:30px;">The Option field value is required for all Question</div>';                                                        
            endif;  
			
            $this->load->view('final_quiz_dmv',$data);
            
        elseif($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'Yes' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE):
            redirect('course_completed');
        elseif($status['chapter_completed'] == 'Yes' && $order == FALSE):
            redirect('payment');
        else:
            redirect('table_contents');
        endif;
    }

    function getFinalQuestionOptionDMV($question) {       
        return $this->course_content->getFinalQuestionOptionDMV($question);
    }
    
}
?>
