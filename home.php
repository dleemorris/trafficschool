<?php

/**
 * edits uploaded by JM on 1/30/12
 */

class Home extends Controller {
    
    function __construct() {
        parent::Controller();
        $this->load->model('course');
        $this->load->model('user');
        $this->load->helper('cookie');
    }
    
    function index(){       
        if($this->authenticate->is_login()) {
            redirect('table_contents');
        } else { 
            if(get_cookie('license') && get_cookie('st_pass')) {
                $data['license'] = get_cookie('license');
                $data['password'] = get_cookie('st_pass');
            }
           // $data['state'] = $this->course->getState(5);
		    $data['state'] = $this->course->getStateByIds('5, 10, 31, 33');
            $data['css'] = '<link href="'.base_url().'fancybox/fancybox-1.3.4.css" rel="stylesheet" type="text/css" />';
            $data['javascript'] = '<script src="'.base_url().'js/jquery.mousewheel-3.0.4.pack.js" type="text/javascript"></script>
            <script src="'.base_url().'js/jquery.fancybox-1.3.4.pack.js" type="text/javascript"></script>
            <script src="'.base_url().'js/ajax.js" type="text/javascript"></script>
            <script type="text/javascript">
		          $(document).ready(function() {
		              $("#forgot").fancybox();
		          });
            </script>';
            $data['err_msg'] = '';
            if(array_key_exists('login',$_POST)):            
                $this->form_validation->set_error_delimiters('<div id="errmsg" class="left">'.$this->config->item('ErrorImage'), '</div>');
                if($this->form_validation->run('login')== TRUE):
                    $log_data = array('license_number'=>$this->input->post('license'),
                                        'password'=>$this->input->post('password'),
                                        'remember_me'=> $this->input->post('remember_me'));
										
					/*if($this->authenticate->checkUserDQ($log_data))
						$data['err_msg'] = 'Your account has been Disqualified. This can happen if you have either passed the course or failed twice.'; 
					else*/
						if($this->authenticate->validateUser($log_data)):
								redirect('table_contents');
						else:
							$data['err_msg'] = 'Invalid License Number (or) Password';    
						endif;        
                endif;
            endif;                        
            $this->load->view('home',$data);
        }        
    }    
    
    function forgot() {
        $this->load->view('forgot');
    }
    
    function logout(){
		$logtime = $this->session->userdata('paperTime');
		$userID = $this->session->userdata('student_id');
		if($logtime){
			$this->user->logtimeInsert($userID,$logtime);
			$this->session->unset_userdata('paperTime');
		}
        $this->authenticate->logout();
		redirect('home');
    }
    
    function checkLicenseNumber($str) {
        if($str=='License Number'):
            $this->form_validation->set_message('checkLicenseNumber', '%s Required');
            return FALSE;
        else:
            return TRUE;
        endif;
    }    
}
?>