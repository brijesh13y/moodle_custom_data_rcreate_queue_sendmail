<?php 
global $CFG, $DB;
require_once('../../../../config.php');
function executes() {
        echo '*************** Executing Cron Started *****************';
        global $CFG, $DB;
		require_once($CFG->dirroot.'/config.php');
		require_once($CFG->libdir . '/externallib.php');
				$user_email = $DB->get_records_sql('SELECT * FROM {local_user_queue_mail} WHERE email_status=0');
				
				if(empty($user_email)){
					$u_email = "";die("Email should not be empty");
				}else{
				foreach($user_email as $emails){
				if($emails){
					$admin_emails = $DB->get_record('user',array('id'=> 2));
					$mail_data = $DB->get_record('mail_data',array('id'=> 1));


					if(email_to_user($admin_emails,$emails->email, $mail_data->mail_title, $mail_data->mail_content, $mail_data->mail_content)){
						date_default_timezone_set('Asia/Kolkata');
						$curr_time = date("Y-m-d H:i:sa");  
					
						$data = new stdClass();
						$data->id = $emails->id;
						$data->mail_content = $mail_data->mail_content;
						$data->email_status = 1;
						$data->email_delivered_time = $curr_time;
						
						$mail_content = $mail_data->mail_content;
						
						$update_res = $DB->update_record('local_user_queue_mail',$data);
						mtrace("Mail send and records updated:" . $emails->id);
					}else{
						echo "Error !!";
					}	
                }
                }
		}
    

}
executes();