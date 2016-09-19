<?php

/**
 * @class Table of Contents
 * @copyright 2010
 */

/**
 * edits uploaded by JM on 11/29/11
 */
class Question extends Controller {

    function __construct() {
        parent::Controller();
        if (!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course_content');
    }

    function index() {
	
	 /*index($chapter,$part)echo $chapter;
	 echo $part;
	 die;*/
        if ($this->input->post('chapter') && $this->input->post('part')):
            
			
			$chapter = $this->input->post('chapter');
            $part = $this->input->post('part');
            $profile = $this->user->getProfile($this->session->userdata('student_id')); //added to pull city and zip ~JM
            if ($this->user->ValidateUserCourse($this->session->userdata('course_id'), $chapter, $part)) {
                $data['chapter'] = $this->course_content->getChapterContent($chapter);
                $data['parts'] = $this->course_content->getPartContent($part);
                $data['question'] = $this->course_content->getPartQuestion($part);
                $data['cObj'] = $this;
                $data['err'] = '';

                if ($profile['city'] == "Testtown" && $profile['zipcode'] == "97777")
                    $data['reviewer'] = true;
					
				  //print_r($data['question']);
					//echo count($data['question']);
					//die;

                if (count($data['question']) == 0) {

                    $level = $this->user->getNextPart($this->session->userdata('student_id'), $this->session->userdata('course_id'), $chapter, $part);
                    $currentstatus = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));
					//echo $currentstatus['current_chapter_id'];
					//echo "current_chapter_id<br/>";
					//echo $currentstatus['current_part_id'];

                    if ($this->user->findProfileUpdate($this->session->userdata('student_id'), $this->session->userdata('course_id'), $currentstatus['current_chapter_id']) == FALSE) {
                        redirect('profile');
                    } else if ($currentstatus['chapter_completed'] == 'Yes') {
                        redirect('payment');
                    } else {
					//echo "renubbb"; die;
                        redirect('table_contents/content/' . $currentstatus['current_chapter_id'] . '/' . $currentstatus['current_part_id']);
                    }
                }
                 if (array_key_exists('save', $_POST)):
                    $answer = $this->input->post('answer');
				   //echo "<pre>";
				  // print_r($answer);
				  $hh = count($answer);
				  $data['unchecked'] = $hh;
				 $data['uncheck'] = count($data['question']) - $data['unchecked'];
				
					$this->session->set_userdata('answer', $answer);
					//	echo "<pre>";print_r($this->session);
				//	echo $this->course_content->validateTestQuestion($answer, $part);
					
					
					//die;
                  
					 if ($this->course_content->validateTestQuestion($answer, $part) == FALSE ) {
					//echo "renu"; die;
                        $data['wrong_ids'] = $this->course_content->getWrongAnswer($answer, $part);
                       $correctQuestion = count($data['question']) - count($data['wrong_ids']) - $data['uncheck'];
                        $seventyPersent = (count($data['question'])) * (70 / 100);
					
                        if ($correctQuestion >= $seventyPersent) {
                            $level = $this->user->getNextPart($this->session->userdata('student_id'), $this->session->userdata('course_id'), $chapter, $part);
                            if ($level == 'Next Chapter' || $level == 'Next Part' || $level == 'Completed') {
                                $this->session->set_userdata('complete_chapter', $chapter);
								$this->session->set_userdata('answer', $answer);
                                $this->session->set_userdata('complete_part', $part);
								switch($level){
									case 'Next Chapter':
									case 'Completed':
										$this->course_content->deleteEntry($this->session->userdata('student_id'));
									break;
								}
								
								$this->session->unset_userdata('paperTime');
                                redirect('question_success');
								$goal = array('goal' => '');
									$this->session->unset_userdata($goal);
                            }
                        }
                    }
					
					
					  elseif ($hh) {
					//echo "renu"; die;
                        $data['wrong_ids'] = $this->course_content->getWrongAnswer($answer, $part);
                       $correctQuestion = count($data['question']) - count($data['wrong_ids']) - $data['uncheck'];
                        $seventyPersent = (count($data['question'])) * (70 / 100);
					
                        if ($correctQuestion >= $seventyPersent) {
                            $level = $this->user->getNextPart($this->session->userdata('student_id'), $this->session->userdata('course_id'), $chapter, $part);
                            if ($level == 'Next Chapter' || $level == 'Next Part' || $level == 'Completed') {
                                $this->session->set_userdata('complete_chapter', $chapter);
								$this->session->set_userdata('answer', $answer);
                                $this->session->set_userdata('complete_part', $part);
								switch($level){
									case 'Next Chapter':
									case 'Completed':
										$this->course_content->deleteEntry($this->session->userdata('student_id'));
									break;
								}
								$this->session->unset_userdata('paperTime');
                                redirect('question_success');
								$goal = array('goal' => '');
									$this->session->unset_userdata($goal);
                            }
                        }
                    }
					
					
					 else {
					//echo "renu1"; die;
                        $level = $this->user->getNextPart($this->session->userdata('student_id'), $this->session->userdata('course_id'), $chapter, $part);
                        if ($level == 'Next Chapter' || $level == 'Next Part' || $level == 'Completed') {
						 	$this->session->set_userdata('answer', $answer);
                            $this->session->set_userdata('complete_chapter', $chapter);
                            $this->session->set_userdata('complete_part', $part);
							
							
							switch($level){
									case 'Next Chapter':
									case 'Completed':
										$this->course_content->deleteEntry($this->session->userdata('student_id'));
									break;
								}
							$this->session->unset_userdata('paperTime');
                            redirect('question_success');
                        } /* else if($level == 'Completed') {//This Student is Complete the all the chapter and parts. Next to Final Test
                          redirect('payment');
                          } */
                    }
					
					 elseif (array_key_exists('save', $_POST)):
                    $data['err'] = '<div id="errmsg" style="padding-top:30px;">All Questions should be answered</div>';
                endif;
				$getUserActTimer = $this->user->checkActivateTimmer();
				if(count($getUserActTimer)>0 && $getUserActTimer['timer']==1){
					if($this->session->userdata('paperTime')==true){
						$getData = $this->session->userdata('paperTime');
						if($getData=='00:00'){						
							$this->load->view('question', $data);
						} else {
							redirect('table_contents/content/' . $chapter . '/' .  $part);
						}
					}
				}else{
					$this->load->view('question', $data);
				}
				
            } else {
			//echo  'vvvvvvvvvvvvv';
			//echo "renu2"; die;
                redirect('table_contents');
            }
        else:
		//echo "renu4"; die;
           redirect('table_contents');
        endif;
    }

    function getPartQuestionOption($question) {
        return $this->course_content->getQuestionOption($question);
    }

}

?>