<?php 
defined('MOODLE_INTERNAL') || die;
function usere_upload_editfields() {
    global $CFG;
    $nameformat = $CFG->fullnamedisplay;
    $necessarynames = array('firstname', 'lastname');
    $languageformat = get_string('fullnamedisplay');
    foreach ($necessarynames as $necessaryname) {
        $pattern = "/$necessaryname\b/";
        if (!preg_match($pattern, $languageformat)) {
            $languageformat = 'firstname lastname';
        }
        if (!preg_match($pattern, $nameformat)) {
            $nameformat = $languageformat;
        }
    }
    $necessarynames = order_in_string($necessarynames, $nameformat);
    return $necessarynames;
}

function add_users($firstname,$lastname,$email){
	global $CFG, $DB;

	$data = new \stdClass();
	$data->firstname = $firstname;
	$data->lastname = $lastname;
	$data->email  = $email;
	$records = $DB->insert_record('local_user_queue_mail',$data);
	return $records->id;
}	

function update_users($firstname,$lastname,$email,$id){
	global $CFG, $DB;
	$data = new \stdClass();
	$data->id = $id;
	$data->firstname = $firstname;
	$data->lastname = $lastname;
	$data->email  = $email;
	
	$update_sql = 'UPDATE {local_user_queue_mail} set firstname="'.$firstname.'",lastname="'.$lastname.'",email="'.$email.'" WHERE id="'.$id.'"';
	$update = $DB->execute($update_sql);
	return $update;
}


