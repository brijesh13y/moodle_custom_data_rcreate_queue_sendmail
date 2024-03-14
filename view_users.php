<?php
// This file is part of the Local user_queue_mail plugin
// git testing

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
$context = context_system::instance();
global $DB, $CFG;
require_login();
if (!is_siteadmin()) {
    return '';
}

$PAGE->set_context($context);
$PAGE->set_url('/local/user_queue_mail/view_users.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_user_queue_mail'));
$PAGE->navbar->add(get_string('pluginname', 'local_user_queue_mail'));

core_php_time_limit::raise(60 * 60);
raise_memory_limit(MEMORY_HUGE);

admin_externalpage_setup('local_user_queue_mail');

// Custom profile Fields.

$table = new html_table();
$table->head = array('First Name','Last Name','Email','Mail Content','Email Status','Email Delivered Time');

echo $OUTPUT->header();
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 5, PARAM_INT);        // how many per page
$baseurl = new moodle_url($CFG->wwwroot . "/local/user_queue_mail/view_users.php", array('id' => 'asc','perpage' => $perpage));

$start = ($page) * $perpage;  
$sql= "SELECT * FROM {local_user_queue_mail}";
$myresp = $DB->get_records_sql($sql,array('id'=>'asc'),$start, $perpage);

foreach ($myresp as $data) {
            $table->data[] = array($data->firstname, $data->lastname, $data->email, $data->mail_content, $data->email_status,$data->email_delivered_time);

        }

echo html_writer::table($table);
$count = $DB->count_records_sql("SELECT count(email) as emailcount FROM {local_user_queue_mail}" , array('id' => 'asc'));
echo $OUTPUT->paging_bar($count, $page, $perpage, $baseurl);

echo $OUTPUT->footer();