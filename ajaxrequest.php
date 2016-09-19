<?php

/**
 * All the Ajax Request call landing this page only * 
 */

class Ajaxrequest extends Controller {
	
	function __construct() {
		parent::Controller();               
	}
    
    function forgot_password() {
        $license_number = $_POST['license'];        
        if($this->authenticate->checkPassword($license_number)):
            $userinfo = $this->authenticate->getUserInfo($license_number);
            $data = array('LicenseNumber'=>$userinfo['license_number'],
                        'Password'=>base64_decode($userinfo['password']),
                        'StudentName'=>$userinfo['first_name']." ".$userinfo['last_name'],
                        'url'=>base_url());
                        
            // Parser Email Template 
            $this->load->library('parser');
            $htmlmessage = $this->parser->parse('email_template/forgot_password',$data, TRUE);

			if($this->config->item('WORKING') == 'LIVE') {
				// Email Config
				$this->load->library('email');
							
				$this->email->from($this->config->item('OwnerEmail'), $this->config->item('OwnerName'));
				$this->email->to($userinfo['email']);
				
				$this->email->subject('Your log in password for Traffic School');
				$this->email->message($htmlmessage);
				$this->email->send();            
				// End Email Config 
			}
            
            $data['message'] = 'Success';
            $this->load->view('ajaxrequest',$data);
        else:
            $data['message'] = 'Fail';
            $this->load->view('ajaxrequest',$data);
        endif;   
    }
 }
?>