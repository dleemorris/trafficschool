<?php

/**
 * @Description Student Profile Page
 * @author Balaganesh 
 * @copyright 2010
 */
class Profile extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
        $this->load->model('course');
    }
    
    function index() {
        $data['profile'] = $this->user->getProfile($this->session->userdata('student_id'));
        $data['state'] = $this->course->getAllState();
        $data['days'] = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20',
        '21','22','23','24','25','26','27','28','29','30','31');        
        $data['month'] = array('01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug',
        '09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec');        
        $data['css'] = '<link href="'.base_url().'datepicker/jquery-datepicker-ui.css" rel="stylesheet" type="text/css" />';
        $data['javascript'] = '<script src="'.base_url().'js/jquery-datepicker.min.js"></script>
        <script>
        	$(function() {        	   
        		$( "#datepicker" ).datepicker({
        		    showOn: "button",
        			buttonImage: "'.base_url().'images/icn_calendar.gif",
        			buttonImageOnly: true,                    
        			changeMonth: true,
        			changeYear: true,
                    minDate: 0
        		});
        	});
	</script>';        
        if(array_key_exists('save',$_POST)) {
            $this->form_validation->set_error_delimiters('<div id="errmsg" class="errmsg">'.$this->config->item('ErrorImage'), '</div>');
            if($this->form_validation->run('profile')== TRUE):
                list($due_month,$due_date,$due_year) = explode("-",$this->input->post('due_date'));                
                $value = array('first_name'=>$this->input->post('first_name'),'last_name'=>$this->input->post('last_name'),
                            'address'=>$this->input->post('address'),'city'=>$this->input->post('city'),
                            'state_id'=>$this->input->post('state'),'zipcode'=>$this->input->post('zipcode'),
                            'phone'=>$this->input->post('phone'),'email'=>$this->input->post('email'),
                            'gender'=>$this->input->post('gender'),'dob'=>$this->input->post('dob_year').'-'.$this->input->post('dob_month').'-'.$this->input->post('dob_day'),
                            'due_date'=>$due_year.'-'.$due_month.'-'.$due_date,'docket_number'=>$this->input->post('docket_number'));
                $this->user->updateProfile($this->session->userdata('student_id'),$value);
                
                $this->session->set_userdata('student_name',$value['first_name']." ".$value['last_name']);
                if($this->user->checkUserAQuestionSet($this->session->userdata('student_id'))==FALSE):
                    redirect("authentication_questions");
                endif;  
                redirect("table_contents");                             
            endif;
        }        
        $this->load->view('profile',$data);       
    }
    
    function validateDOB() {
        $month = $this->input->post('dob_month');
        $day = $this->input->post('dob_day');
        $year = $this->input->post('dob_year');
        
        if($month && $day && $year) :
            if(!checkdate($month,$day,$year)):
                $this->form_validation->set_message('validateDOB', '%s value is Invalide');
                return FALSE;
            else:
                return TRUE;
            endif;
        else:
            $this->form_validation->set_message('validateDOB', '%s Required');
            return FALSE;
        endif;        
    }
    
}
?>