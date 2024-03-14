<?php
require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/local/user_queue_mail/locallib.php');
require_once($CFG->dirroot.'/local/user_queue_mail/user_form.php');
global $DB,$CFG;
$iid         = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

core_php_time_limit::raise(60 * 60);
raise_memory_limit(MEMORY_HUGE);

admin_externalpage_setup('local_user_queue_mail');

$returnurl = new moodle_url('/local/user_queue_mail/index.php');
//$bulknurl  = new moodle_url('/local/user_queue_mail/index.php');
//$bulknurl  = new moodle_url('/admin/user/user_bulk.php');

if (empty($iid)) {
    $mform1 = new user_upload_form();

    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');

        $content = $mform1->get_file_content('userfile');

        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        $csvloaderror = $cir->get_error();
        unset($content);

        if (!is_null($csvloaderror)) {
            throw new \moodle_exception('csvloaderror', '', $returnurl, $csvloaderror);
        }

    } else {
        echo $OUTPUT->header();

        echo $OUTPUT->heading_with_help(get_string('uploadusers', 'local_user_queue_mail'), 'uploadusers', 'local_user_queue_mail');

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {
    $cir = new csv_import_reader($iid, 'uploaduser');
}

$process = new \local_user_queue_mail\process($cir);
$filecolumns = $process->get_file_columns();

$mform2 = new user_uploader_form(null,
    ['columns' => $filecolumns, 'data' => ['iid' => $iid, 'previewrows' => $previewrows]]);

if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);

} else if ($formdata = $mform2->get_data()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadusersresult', 'tool_uploaduser'));

    $process->set_form_data($formdata);   
	
	$table = new \local_user_queue_mail\preview($cir, $filecolumns, $previewrows);
	foreach($table->data as $f_data){	
		//$user_exists = $DB->get_record('local_user_queue_mail', array('email' => $f_data['email']));
		$sql = "select id from {local_user_queue_mail} where email='".$f_data['email']."'";
        $user_exists = $DB->get_records_sql($sql);
		if(!empty($user_exists)){
			foreach($user_exists as $user_exists_data){
				$existing_data = $user_exists_data->id;
			}
			}else{
				$existing_data = "";
			}
        if (!empty($existing_data) ){
			update_users($f_data['firstname'],$f_data['lastname'],$f_data['email'],$existing_data);
		}else{
			add_users($f_data['firstname'],$f_data['lastname'],$f_data['email']);
		}
	}	
	redirect($CFG->wwwroot."/local/user_queue_mail/",'Submission successfull');
    echo $OUTPUT->footer();
    die;
}
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('uploaduserspreview', 'local_user_queue_mail'));

$table = new \local_user_queue_mail\preview($cir, $filecolumns, $previewrows);

echo html_writer::tag('div', html_writer::table($table), ['class' => 'flexible-wrap']);

if ($table->get_no_error()) {
	$mform2->display();
}

echo $OUTPUT->footer();
die;

