<?php

/**
 * @class Table of Contents
 * @copyright 2010
 */

class Table_contents extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course_content');
    }
    
    function index() {  
	
	//echo $this->session->userdata('course_id');
		$data['chapter'] = $this->course_content->getChapter($this->session->userdata('course_id'));
        $currentstatus = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));
		$chapter = $this->course_content->getChapterContent($currentstatus['current_chapter_id']);
		
		$data['currentstatus'] = $currentstatus;
		$data['cur_chapter_no'] = $chapter['chapter_no'];
		
        $data['myClass'] = $this;
		/*
		echo $this->session->userdata('student_id');
		die;
		
		echo "<pre>";
		print_r($data['currentstatus']);
		print_r($data['cur_chapter_no']);
		die;
		*/
        $this->load->view('table_contents',$data);       
    }
    function getFormCurrentTime(){	
		$formSession = $this->session->userdata('paperTime');
		if($formSession==false){
			$getData = $this->user->getLogTimeData($this->session->userdata('student_id'));

			if(count($getData)==0){
				$this->session->set_userdata('paperTime','60:00');
			} else {
				if($getData['time_log']!=""){
					$this->session->set_userdata('paperTime',$getData['time_log']);
				} else {
					$this->session->set_userdata('paperTime','60:00');
				}
			}
		}
		if($formSession!="00:00"){
			$currentTime = explode(':',$formSession);
			if(count($currentTime)==2){
				if((int)$currentTime[0]==00 && $currentTime[1]==01){
					$this->session->set_userdata('paperTime','00:00');
				} else {
					if($currentTime[1]==60){
						$currentTime[0] = $currentTime[0]-1;
						$currentTime[1] = '59';
					} else{
						if($currentTime[1]==00){
							$currentTime[1] = 59;
							$currentTime[0] = (int)$currentTime[0]-1;
						} else {
							$currentTime[1] = (int)$currentTime[1]-1;
						}	
					} 
					if(strlen($currentTime[0])==1){
						$currentTime[0] = '0'.$currentTime[0];
					}
					if(strlen($currentTime[1])==1){
						$currentTime[1] = '0'.$currentTime[1];
					}
					
					$currentTime = implode(':',$currentTime);
					$this->session->set_userdata('paperTime',$currentTime);
				}
			}
		}
	if ($this->session->userdata('paperTime')=='00:00'){
		echo "You can Move on Now!";
		}
		else {
		echo "Time: ".$this->session->userdata('paperTime');
		}
	}
    function content($chapter,$part) {
	 
	  // echo $chapter;
	  // echo $part;die;
	  /*
		if($this->session->userdata('paperTime')==false){
			$this->session->set_userdata('paperTime','60:00');
		}
		*/
	    $data['goal'] = $this->session->userdata('goal');
        if($this->user->ValidateUserCourse($this->session->userdata('course_id'),$chapter,$part)){
			$data['chapter'] = $this->course_content->getChapterContent($chapter);
           
            $data['parts'] = $this->course_content->getPartContent($part);
			//echo "<pre>";print_r($data['parts']); 
			//echo $this->session->userdata('student_id');
            $currentstatus = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));	
			//print_r($data);	
			$cur_chapter_detail = $this->course_content->getChapterContent($currentstatus['current_chapter_id']);
			
			//echo "<pre>";print_r($cur_chapter_detail);die;		
			$data['cur_chapter_no'] = $cur_chapter_detail['chapter_no'];
			$data['currentstatus'] = $currentstatus;
			$getUserActTimer = $this->user->checkActivateTimmer();
			
			$data['activateTimmer'] = $getUserActTimer;
			$data['formSession'] = $this->session->userdata('paperTime');
			
            if($this->user->findProfileUpdate($this->session->userdata('student_id'),$this->session->userdata('course_id'),$data['currentstatus']['current_chapter_id'])== FALSE)
			{
               redirect('profile');
            } 
			else
			{
			
					//$this->load->view('course_content',$data);
				//echo $this->user->askAQuestion($this->session->userdata('student_id'));
				//echo 'vvv'.$this->user->checkUserAQuestionSet($this->session->userdata('student_id'));
				//die;
			
			
			   
               if($this->user->checkUserAQuestionSet($this->session->userdata('student_id')) == TRUE && $this->user->askAQuestion($this->session->userdata('student_id'))==TRUE):                    
                    $this->session->set_userdata('redirect_url',$_SERVER['QUERY_STRING' ]);
                    $this->user->setAuthenticationFailed($this->session->userdata('student_id'));                    
                    redirect('ask_authentication');
                else:
				$this->load->view('course_content',$data);
                endif;
				
            }
        } else {
		      
            redirect ('table_contents');
        }            
    }     
    
    function getFirstPartID($chapter_id) {
        
        return $this->course_content->getFirstPartID($chapter_id);
    } 
	
	/*** New Function for DMV Course - Review the completed chapters/parts content ***/
	
	function goNextCourseContent($chapter,$part) {
	
		$ret = $this->user->getNextMove($this->session->userdata('course_id'),$chapter,$part);		
		$currentstatus = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));
		
		$detail = @explode("||",$ret);
		
		if(count($detail)>0) {
		
			if($currentstatus['chapter_completed'] == 'No' && $currentstatus['current_chapter_id'] == $detail[1]) {
				redirect ('table_contents');
			} elseif($detail[0] == 'Next Part' || $detail[0] == 'Next Chapter') {
				redirect ('table_contents/content/'.$detail[1].'/'.$detail[2]);
			} else {
				redirect ('table_contents');
			}		
		} else {
			redirect ('table_contents');
		}
	}	  
}
?>
