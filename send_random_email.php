<?php
// This file is part of the Local user_queue_mail plugin

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/user_queue_mail/user_form.php');
$context = context_system::instance();
global $DB, $CFG;
require_login();
if (!is_siteadmin()) {
    return '';
}

$PAGE->set_context($context);
$PAGE->set_url('/local/user_queue_mail/send_random_email.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_user_queue_mail'));
$PAGE->navbar->add(get_string('pluginname', 'local_user_queue_mail'));

core_php_time_limit::raise(60 * 60);
raise_memory_limit(MEMORY_HUGE);

admin_externalpage_setup('local_user_queue_mail');
$returnurl = new moodle_url($CFG->wwwroot . "/local/user_queue_mail/send_random_email.php");
echo $OUTPUT->header();
    $mform1 = new user_mailitem_form();
	if ($formdata = $mform1->is_cancelled()) {
		redirect($returnurl);

	}else if ($formdata = $mform1->get_data()) {
		
		$result = $DB->get_record('mail_data',array('id' => 1));
		if(empty($result)){
			$data = new stdClass();
			$data->mail_title = $formdata->mail_title;
			$data->mail_content = $formdata->mail_content['text'];
			$insert_status = $DB->insert_record('mail_data',$data);
			redirect($returnurl,'Mail sending started!');
		}else{
			$data = new stdClass();
			$data->id = $result->id;
			$data->mail_title = $formdata->mail_title;
			$data->mail_title = $formdata->mail_title;
			$data->mail_content = $formdata->mail_content['text'];
			$update_status = $DB->update_record('mail_data',$data);
			redirect($returnurl,'Mail sending started!');
		}
    } else {
        $mform1->display();
        
    }
echo $OUTPUT->footer();


