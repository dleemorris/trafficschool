<?php

class Reenter_course extends Controller {
    
    function __construct() {
        parent::Controller();
    }
    
    function index(){
        
        if($this->authenticate->is_login()) {
            redirect('table_contents');
        } else { 
        
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
                    if($this->authenticate->validateUser($log_data)):
                        redirect('table_contents');
                    else:
                        $data['err_msg'] = 'Invalid License Number (or) Password';    
                    endif;           
                endif;
            endif;        
            
            $this->load->view('reenter_course',$data);
        }
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