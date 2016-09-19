<?php

/**
 * @Class User Model Class 
 * Description: Student wise fetch the information from db 
 */
 
/**
 * edits uploaded by JM on 11/29/11
 */
 
/**
 * updated By Bala on 15 Jan 2012
 */
 
 class Course_content extends Model {
    
    function __construct() {
        parent::Model();
        $this->tbl_chapter = 'chapter';
        $this->tbl_parts = 'parts';
        $this->tbl_part_question = 'part_question';
        $this->tbl_part_question_option = 'part_question_option';
        $this->tbl_final_question = 'finalexam_question';
        $this->tbl_final_question_option = 'finalexam_question_option';   
        $this->tbl_final_question_dmv = 'finalexam_question_dmv'; // ~JM Edit
        $this->tbl_final_question_option_dmv = 'finalexam_question_option_dmv';  // ~JM Edit
        $this->tbl_final_log = 'finaltest_log'; // ~JM Edit
    }   

    function getChapter($course) {
        $this->db->select('chapter_id, chapter_name, chapter_no');
        $this->db->where('course_id',$course);
        $this->db->order_by('chapter_no','asc');
        $qry = $this->db->get($this->tbl_chapter);
        return $qry->result_array();
    }
    
    function getFirstPartID($chapter_id) {
        $this->db->select('part_id');
        $this->db->where('chapter_id',$chapter_id);
        $this->db->order_by('part_no','asc');
        $this->db->limit(1,0);        
        $qry = $this->db->get($this->tbl_parts);
        $row = $qry->row_array();
        return $row['part_id'];
    }
    
    function getChapterContent($chapter_id) {        
        $this->db->where('chapter_id',$chapter_id);        
        $qry = $this->db->get($this->tbl_chapter);
        return $qry->row_array();
    }
    
    function getPartContent($part_id) {
        $this->db->where('part_id',$part_id);
        $qry = $this->db->get($this->tbl_parts);
        return $qry->row_array();
    }
    
    function getPartQuestion($part_id) {
        $this->db->where('part_id',$part_id);
        $qry = $this->db->get($this->tbl_part_question);
        return $qry->result_array();        
    }
    
    function getQuestionOption($question_id) {
        $this->db->where('part_question_id',$question_id);
        $qry = $this->db->get($this->tbl_part_question_option);
        return $qry->result_array();
    }
    
    function validateTestQuestion($answer=array(),$part_id) {
        $flag = 1;        
        if(count($answer)>0) {
            $qry = "select a.part_question_id, b.part_question_option_id, b.answer From part_question as a, part_question_option as b 
            where b.answer = 'Yes' AND b.part_question_id = a.part_question_id AND a.part_id = ? ";
            $sql = $this->db->query($qry,array($part_id));
            $res = $sql->result_array();
            $default_answer = array();
            foreach($res as $key=>$value):
                $default_answer[$value['part_question_id']][$value['part_question_option_id']] = $value['answer'];       
            endforeach;               
            
            foreach($answer as $qst_id=>$qvalue):                
                if($default_answer[$qst_id][$qvalue[0]] != 'Yes') {
                    $flag = 0;
                }
            endforeach;
            if($flag): return TRUE; else: return FALSE; endif;           
        } else {
            return FALSE;
        }
    }
    
    function getWrongAnswer($answer=array(),$part_id) {
        
        $qry = "select a.part_question_id, b.part_question_option_id, b.answer From part_question as a, part_question_option as b 
        where b.answer = 'Yes' AND b.part_question_id = a.part_question_id AND a.part_id = ? ";
        $sql = $this->db->query($qry,array($part_id));
        $res = $sql->result_array();
        
        foreach($res as $key=>$value):
            $default_answer[$value['part_question_id']][$value['part_question_option_id']] = $value['answer'];       
        endforeach;               
        $res = array();
        foreach($answer as $qst_id=>$qvalue):                
            if($default_answer[$qst_id][$qvalue[0]] != 'Yes') {
                array_push($res,$qst_id);
            }
        endforeach;
        return $res;
    }
		 
		
	
	
    /** Final Quiz Function ***/
    
    function getFinalQuestion($course_id) {
        /*$this->db->where('course_id',$course_id);
        $qry = $this->db->get($this->tbl_final_question);*/
		
		$qry = "Select * From ".$this->tbl_final_question." Where course_id = ? order by rand() Limit 0, 25 ";
		$sql = $this->db->query($qry,array($course_id));
        return $sql->result_array();        
    }
    
    function getFinalQuestionDMV($course_id) { // ~JM Edit
        $this->db->where('course_id',$course_id);
        $qry = $this->db->get($this->tbl_final_question_dmv);
        return $qry->result_array();        
    }
    
    function getFinalQuestionOption($question_id) {
        $this->db->where('finalexam_question_id',$question_id);
        $qry = $this->db->get($this->tbl_final_question_option);
        return $qry->result_array();
    }
    
    function getFinalQuestionOptionDMV($question_id) { // ~JM Edit
        $this->db->where('finalexam_question_id',$question_id);
        $qry = $this->db->get($this->tbl_final_question_option_dmv);
        return $qry->result_array();
    }    
	
	
    
    /* Updated by Bala - 15 Jan 2012
	function validateFinalTestQuestion($answer=array(),$total_qust,$course_id) {

        $correct = 0; 
        $wrong = 0;
        $total = $total_qust; //count($answer);		
            
        if(count($answer)>0) {
		
            $qry = "Select a.finalexam_question_id, b.finalexam_question_option_id, b.answer 
							From ".$this->tbl_final_question." as a, ".$this->tbl_final_question_option." as b 
	            			Where 
								b.answer = 'Yes' AND 
								b.finalexam_question_id = a.finalexam_question_id AND 
								a.course_id = ? ";
								
            $sql = $this->db->query($qry,array($course_id));
            $res = $sql->result_array();
            $default_answer = array();
			
            foreach($res as $key=>$value):
                $default_answer[$value['finalexam_question_id']][$value['finalexam_question_option_id']] = $value['answer'];       
            endforeach;               
            
            foreach($answer as $qst_id=>$qvalue):                
                if($default_answer[$qst_id][$qvalue[0]] == 'Yes') {
                    $correct++;
                } else {
                    $wrong++;
                }
            endforeach;			
			$wrong = $total - $correct;
            return array('total'=>$total,'correct'=>$correct,'wrong'=>$wrong);
        } else {
            return array('total'=>$total,'correct'=>0,'wrong'=>$total);
        }
    } */ 	
	
	function validateFinalTestQuestion($answer,$course_id) {

        $correct = 0; 
        $wrong = 0;
        $total = count($answer);
            
        if(count($answer)>0) {
            $qry = "select a.finalexam_question_id, b.finalexam_question_option_id, b.answer From finalexam_question as a, finalexam_question_option as b 
            where b.answer = 'Yes' AND b.finalexam_question_id = a.finalexam_question_id AND a.course_id = ? ";
            $sql = $this->db->query($qry,array($course_id));
            $res = $sql->result_array();
            $default_answer = array();
            foreach($res as $key=>$value):
                $default_answer[$value['finalexam_question_id']][$value['finalexam_question_option_id']] = $value['answer'];       
            endforeach;               
            
            foreach($answer as $qst_id=>$qvalue):                
                if($default_answer[$qst_id][$qvalue[0]] == 'Yes') {
                    $correct++;
                } else {
                    $wrong++;
                }
            endforeach;            
            return array('total'=>$total,'correct'=>$correct,'wrong'=>$wrong);
        } else {
            return array('total'=>$total,'correct'=>0,'wrong'=>0);
        }
    } 
    
    function  validateFinalTestQuestionDMV($answer,$course_id) { // ~JM Edit

        $correct = 0; 
        $wrong = 0;
        $total = count($answer);
            
        if(count($answer)>0) {
            $qry = "select a.finalexam_question_id, b.finalexam_question_option_id, b.answer From finalexam_question_dmv as a, finalexam_question_option_dmv as b 
            where b.answer = 'Yes' AND b.finalexam_question_id = a.finalexam_question_id AND a.course_id = ? ";
            $sql = $this->db->query($qry,array($course_id));
            $res = $sql->result_array();
            $default_answer = array();
            foreach($res as $key=>$value):
                $default_answer[$value['finalexam_question_id']][$value['finalexam_question_option_id']] = $value['answer'];       
            endforeach;               
            
            foreach($answer as $qst_id=>$qvalue):                
                if($default_answer[$qst_id][$qvalue[0]] == 'Yes') {
                    $correct++;
                } else {
                    $wrong++;
                }
            endforeach;            
            return array('total'=>$total,'correct'=>$correct,'wrong'=>$wrong);
        } else {
            return array('total'=>$total,'correct'=>0,'wrong'=>0);
        }
    }  
    
    function getFinalWrongAnswer($answer,$course_id) {
        
        $qry = "select a.finalexam_question_id, b.finalexam_question_option_id, b.answer From finalexam_question as a, finalexam_question_option as b 
        where b.answer = 'Yes' AND b.finalexam_question_id = a.finalexam_question_id AND a.course_id = ? ";
        $sql = $this->db->query($qry,array($course_id));
        $res = $sql->result_array();
        
        foreach($res as $key=>$value):
            $default_answer[$value['finalexam_question_id']][$value['finalexam_question_option_id']] = $value['answer'];       
        endforeach;               
        $res = array();
        foreach($answer as $qst_id=>$qvalue):                
            if($default_answer[$qst_id][$qvalue[0]] != 'Yes') {
                array_push($res,$qst_id);
            }
        endforeach;
        return $res;
    }  
    
    function getFinalWrongAnswerDMV($answer,$course_id) { // ~JM Edit
        
        $qry = "select a.finalexam_question_id, b.finalexam_question_option_id, b.answer From finalexam_question_dmv as a, finalexam_question_option_dmv as b 
        where b.answer = 'Yes' AND b.finalexam_question_id = a.finalexam_question_id AND a.course_id = ? ";

        $sql = $this->db->query($qry,array($course_id));
        $res = $sql->result_array();
        
        foreach($res as $key=>$value):
            $default_answer[$value['finalexam_question_id']][$value['finalexam_question_option_id']] = $value['answer'];       
        endforeach;               
        $res = array();
        foreach($answer as $qst_id=>$qvalue):                
            if($default_answer[$qst_id][$qvalue[0]] != 'Yes') {
                array_push($res,$qst_id);
            }
        endforeach;
        return $res;
    }  
    
}
?>