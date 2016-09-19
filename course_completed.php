<?php

/**
 * @author MESMERiZE
 * @copyright 2010
 */

/**
 * edits uploaded by JM on 11/29/11
 * edits uploaded by JM on 1/30/12 
 */

class Course_completed extends Controller {
    
    function __construct() {
        parent::Controller();
        if(!$this->authenticate->is_login()) {
            redirect('home');
        }
        $this->load->model('user');
    }
    
    function index() {
        $status = $this->user->getUserCurrentStatus($this->session->userdata('student_id'));        
		$profile = $this->user->getProfile($this->session->userdata('student_id')); //load email for receipt page ~JM
		
		$data = array('email'=>$profile['email'],'first_name'=>$profile['first_name'],'last_name'=>$profile['last_name'],'license_number'=>$profile['license_number']);     
        //$data['fails']= $this->user->getFails($this->session->userdata('student_id'));
        if($status['chapter_completed'] == 'Yes' && $status['final_quiz'] == 'Yes' && $this->user->checkUserOrder($this->session->userdata('student_id'))== TRUE): 
		$finalscore=$profile['percentage'];
		if($finalscore >=70){
		$name =$profile['first_name']." ".$profile['last_name'];
		$drivers_license =$profile['license_number'];
		$birth=$profile['dob'];
		$email=$profile['email'];
        $address=$profile['address'];
        
        $court_name=$profile['court_name'];
		$county_name=$profile['county_name'];
		$docket_number=$profile['docket_number'];
		$cdate=date('m-d-Y');
		$id=$this->session->userdata('student_id');
		$pdfname=$id.$profile['first_name'].$profile['last_name'];
		$this->load->library('pdf');
		$this->pdf->SetAuthor('Author');
		$this->pdf->SetTitle('Title');
		$this->pdf->SetSubject('Subject');
		$this->pdf->SetKeywords('keywords');
		$this->pdf->SetFont('helvetica', 'A', 13);
		$this->pdf->AddPage();
		$tbl = <<<EOD
		<style>
		body {
	margin: 0px;
	padding: 0px;
	font:13px Arial, Helvetica, sans-serif;
}

.wrapper {
	margin:0 auto;
	width:850px;
}


.header {
	background:url("../images/header.jpg") no-repeat 0 0;
	width:850px;
	height:187px;
}

.header h1.logo {
	margin:0;
	padding:0;
}

.header h1.logo a {
    float: left;
    height: 91px;
    margin: 0 0 0 270px;
    width: 312px;
}

.footer {
	background:url(../images/footer.jpg) no-repeat 0 0;
	height:30px;
	width:850px;
	margin-top:-5px;
}

.container {
	padding:5px 0;
	text-align:center;
}
		
		</style>
   <div class="wrapper">


<div class="header"><h1 class="logo"><a href="#"><img src="http://702duischool.com/images/header.jpg" width="800" ></a></h1></div>

<div class="container" style="margin-top:-5px;">
    <h1>$name</h1>  
    <h3>Certificate of Course Completion $cdate</h3>
    <h3>$county_name,$court_name</h3>
    <h3>Date of Birth: $birth</h3>
    <h3>Drivers License Number: $drivers_license</h3>
     <h3>Address: $address</h3>
     <h3>Final Test Score: $finalscore</h3>
    <h3>Docket Number: $docket_number </h3>
<p>Student Signature :______________________ Date _______________________ </p>
    <p>Congratulations, you have successfully completed the 8 hour Level 1 DUI course at 702duischool.com</p>
    <p>AA Approved Traffic and DUI School</p>
	<p>(888) 609-5505</p>
	<p>702 Onlinne Schools, LLC</p>
	<p>3722 Las Vegas Blvd.# 1211 Las Vegas, NV.89158</p>
	<p>DUI000041129</p>
</div>

<div class="footer" style="margin-top:-5px;"><img src="http://702duischool.com/images/footer.jpg" width="800" ></div>
	

</div>
EOD;

		$this->pdf->writeHTML($tbl, true, false, false, false, '');
	    $pdf =	$this->pdf->Output(''.$pdfname.'.pdf', 'F');
	    $path=$id.$data[first_name].$data[last_name];
 
	     $config = array();
                 $config['mailtype'] = 'html';
                 $this->load->library('email', $config);  

				$this->email->from('info@702duischool.com', '702duischool.com');
				$this->email->to(''.$email.'');		
				$this->email->subject('** Your Certificate of Completion from 702duischool.com **
		');
		         $this->email->attach(''.$path.'.pdf');
					
					$msg = "<p>Congratulations on completing your course!. Attached is your certificate of completion.  Please print out and take with you to court.  If you have any questions feel free to give us a call at the school office number listed below.</p>

					<p>Best,<br>
					702 DUI School</p>



					<p>702 DUI School <br>
					www.702duischool.com <br>
					www.702trafficschool.com <br>
					info@702duischool.com <br>
					8275 S Eastern Ave, Ste 200-945,<br>
					Las Vegas, NV 89123</p>

					<p>We are a DMV licensed and court approved online course <br>
					DUI000041129 <br>
					TSS000041522 </p>";
			$this->email->message(''.$msg.'');
							
          

			$this->email->send();
										
             }
		
            $this->load->view('course_completed',$data);

        else:
            redirect('table_contents');
        endif;
    }
}
?>