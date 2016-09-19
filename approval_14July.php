<?php

class Approval extends Controller {
    
    function __construct() {
        parent::Controller();
        $this->load->model('course');
        $this->load->model('user');
        $this->tbl_notapproved = 'not_approved_court_request';
    }
    
    function index(){
        $state_id = $this->input->post('state');
        $county_id = $this->input->post('county');
        $court_id = $this->input->post('court');
        if($state_id && $county_id && $court_id) {
            $data = array('state_id'=>$state_id,'county_id'=>$county_id,'court_id'=>$court_id);
            $state = $this->course->getState($state_id); 
            $county = $this->course->getCounty($county_id);
            $court = $this->course->getCourt($court_id);
            $course = $this->course->getCourse($court_id);
            
            $data['state'] = $state['state_name']; 
            $data['county'] = $county['county_name'];
            $data['court'] = $court['court_name'];        
            $data['course_name'] = $course['course_name'];
            $data['course_fee'] = $course['course_fee'];
            
            if($this->course->checkCourtApproval($court_id)>0):
                $this->load->view('court_approved',$data);
            else:                
                if(array_key_exists('send',$_POST)):                    
                    $this->form_validation->set_error_delimiters('<div id="errmsg" class="errmsg">'.$this->config->item('ErrorImage'), '</div>');
                    $this->form_validation->set_rules('email', 'Email Address', 'required|trim|valid_email');
                    if($this->form_validation->run() == TRUE):
                        $this->db->insert($this->tbl_notapproved,array('email'=>$this->input->post('email'),
                                        'state_id'=>$state_id,
                                        'county_id'=>$county_id,
                                        'court_id'=>$court_id));
                        $data['flag'] = TRUE;
                    endif;                    
                endif;
                $this->load->view('court_not_approved',$data);
            endif;
         } else {
            redirect('home');
         }                
    }
    
    function about() {
        $state_id = $this->input->post('state');
        $county_id = $this->input->post('county');
        $court_id = $this->input->post('court');
        if($state_id && $county_id && $court_id) {
            $data = array('state_id'=>$state_id,'county_id'=>$county_id,'court_id'=>$court_id);
            $state = $this->course->getState($state_id); 
            $county = $this->course->getCounty($county_id);
            $court = $this->course->getCourt($court_id);
            $course = $this->course->getCourse($court_id);
            
            $data['state'] = $state['state_name']; 
            $data['county'] = $county['county_name'];
            $data['court'] = $court['court_name'];
            $course_id = $course['course_id'];
            
            if(array_key_exists('login',$_POST)) {
                $this->form_validation->set_error_delimiters('<div id="errmsg" class="errmsg">'.$this->config->item('ErrorImage'), '</div>');
                if($this->form_validation->run('register')== TRUE) {
                    $value = array('first_name'=>$this->input->post('first_name'),
                                    'last_name'=>$this->input->post('last_name'),
                                    'license_number'=>$this->input->post('license_number'),
                                    'password'=>base64_encode($this->input->post('password')),
                                    'email'=>$this->input->post('email'));
                    $course_detail = array('state_id'=>$this->input->post('state'),
                                    'county_id'=>$this->input->post('county'),
                                    'court_id'=>$this->input->post('court'),
                                    'course_id'=>$course_id);
                    $this->user->createStudent($value,$course_detail);
                    

                    $maildata = array('LicenseNumber'=>$this->input->post('license_number'),
                                'Password'=>$this->input->post('password'),
                                'StudentName'=>$this->input->post('first_name')." ".$this->input->post('last_name'),
                                'url'=>base_url());
                                
                    /* Parser Email Template */
                    $this->load->library('parser');
                    $htmlmessage = $this->parser->parse('email_template/registrator',$maildata, TRUE);
        
                    /* Email Config */                 
                    $this->load->library('email');
                                
                    $this->email->from('support@702OnlineSchools.com', 'Trafficschool');
                    $this->email->to($this->input->post('email'));
                    
                    $this->email->subject('Log in Information for Traffic School');
                    $this->email->message($htmlmessage);
                    $this->email->send();            
                    /* End Email Config */
                                        
                    if($this->authenticate->validateUser(array('license_number'=>$this->input->post('license_number'),'password'=>$this->input->post('password')))) {
                        redirect('table_contents');
                    }
                }
            }            
            $this->load->view('about_course',$data);
        } else {
            redirect('home'); 
        }        
        
    }
    
    function license_check($str) {        
        if($this->user->checkUserAvailable($str,$this->input->post('password'))):
            return TRUE;
        else:
            $this->form_validation->set_message('license_check', '%s  already exists');
			return FALSE;
        endif;
    }
}


?>