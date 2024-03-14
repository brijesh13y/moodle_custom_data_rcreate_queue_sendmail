<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';
require_once($CFG->dirroot . '/local/user_queue_mail/lib.php');
class user_upload_form extends moodleform {
    function definition () {
        $mform = $this->_form;
        global $CFG;
        $linkcontent = '<ul id="stats">
        <li><a class="btn btn-secondary" href="'.$CFG->wwwroot.'/local/user_queue_mail/index.php">Uploads CSV</a></li>
        <li><a class="btn btn-secondary" href="'.$CFG->wwwroot.'/local/user_queue_mail/view_users.php">View Queue Users</a></li>
        <li><a class="btn btn-secondary" href="'.$CFG->wwwroot.'/local/user_queue_mail/send_random_email.php">Send random email to users</a></li>
        </ul>';

        $mform->addElement('html', $linkcontent);

        $mform->addElement('header', 'settingsheader', get_string('upload', 'local_user_queue_mail'));

        $url = new moodle_url('samplefile.csv');
        $link = html_writer::link($url, 'samplefile.csv');
        $mform->addElement('static', 'samplefile', get_string('samplefile', 'local_user_queue_mail'), $link);
        $mform->addHelpButton('samplefile', 'samplefile', 'local_user_queue_mail');

        $mform->addElement('filepicker', 'userfile', get_string('file'));
        $mform->addRule('userfile', null, 'required');

        $this->add_action_buttons(false, get_string('uploadusers', 'local_user_queue_mail'));
    }

    
}
class user_uploader_form extends moodleform {
    function definition () {
        global $CFG, $USER;

        $mform   = $this->_form;
        $columns = $this->_customdata['columns'];
        $data    = $this->_customdata['data'];
        $templateuser = $USER;
        profile_definition($mform);
        $mform->addElement('hidden', 'iid');
        $mform->setType('iid', PARAM_INT);
        $mform->addElement('hidden', 'previewrows');
        $mform->setType('previewrows', PARAM_INT);
        $this->add_action_buttons(true, get_string('uploadusers', 'tool_uploaduser'));
        $this->set_data($data);
    }
	
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $columns = $this->_customdata['columns'];
        $optype  = $data['uutype'];
        $updatetype = $data['uuupdatetype'];

        if (!empty($updatetype) && ($optype == UU_USER_ADDNEW || $optype == UU_USER_ADDINC)) {
            $errors['uuupdatetype'] = get_string('invalidupdatetype', 'tool_uploaduser');
        }

        if ($optype != UU_USER_UPDATE) {
            $requiredusernames = usere_upload_editfields();
            $missing = array();
            foreach ($requiredusernames as $requiredusername) {
                if (!in_array($requiredusername, $columns)) {
                    $missing[] = get_string('missingfield', 'error', $requiredusername);;
                }
            }
            if ($missing) {
                $errors['uutype'] = implode('<br />',  $missing);
            }
            if (!in_array('email', $columns) and empty($data['email'])) {
                $errors['email'] = get_string('requiredtemplate', 'tool_uploaduser');
            }
        }
        return $errors;
    }
	 
	
    function get_data() {
        $data = parent::get_data();

        if ($data !== null and isset($data->description)) {
            $data->descriptionformat = $data->description['format'];
            $data->description = $data->description['text'];
        }

        return $data;
    }
}

class user_mailitem_form extends moodleform {
	function definition () {
        $mform = $this->_form;
        global $CFG;
        $linkcontent = '<ul id="stats">
        <li><a class="btn btn-secondary" href="'.$CFG->wwwroot.'/local/user_queue_mail/index.php">Uploads CSV</a></li>
        <li><a class="btn btn-secondary" href="'.$CFG->wwwroot.'/local/user_queue_mail/view_users.php">View Queue Users</a></li>
        <li><a class="btn btn-secondary" href="'.$CFG->wwwroot.'/local/user_queue_mail/send_random_email.php">Send random email to users</a></li>
        </ul>';

        $mform->addElement('html', $linkcontent);
        $mform->addElement('header', 'setting_mail_header', get_string('mail_content_header', 'local_user_queue_mail'));
		
		$options = array(
			'1' => 'All Users',
		);
		$mform->addElement('select', 'type', get_string('slect_user', 'local_user_queue_mail'), $options);
		
		$mform->addElement('text', 'mail_title', get_string('mail_title','local_user_queue_mail'));
		$mform->addRule('mail_title', get_string('missing_title','local_user_queue_mail'), 'required', null, 'server');
		
		$mform->addElement('editor', 'mail_content', get_string('mail_content', 'local_user_queue_mail'));
		$mform->addRule('mail_content', get_string('missing_mail_content','local_user_queue_mail'), 'required', null, 'server');
		$mform->setType('mail_content', PARAM_RAW);
		
		$this->add_action_buttons(false, get_string('send_email', 'local_user_queue_mail'));
}
}


?>
<style>
    #stats li
{
  display: inline;
  list-style-type: none;
  padding-right: 20px;
}
</style>