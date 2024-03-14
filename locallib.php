<?php

defined('MOODLE_INTERNAL') || die();

define('UU_USER_ADDNEW', 0);
define('UU_USER_ADDINC', 1);
define('UU_USER_ADD_UPDATE', 2);
define('UU_USER_UPDATE', 3);

define('UU_UPDATE_NOCHANGES', 0);
define('UU_UPDATE_FILEOVERRIDE', 1);
define('UU_UPDATE_ALLOVERRIDE', 2);
define('UU_UPDATE_MISSING', 3);

define('UU_BULK_NONE', 0);
define('UU_BULK_NEW', 1);
define('UU_BULK_UPDATED', 2);
define('UU_BULK_ALL', 3);

define('UU_PWRESET_NONE', 0);
define('UU_PWRESET_WEAK', 1);
define('UU_PWRESET_ALL', 2);

class uu_progress_tracker1 {
    protected $_row;

    public $columns = [];
    protected $headers = [];

    public function __construct() {
        $this->headers = [
            'firstname' => get_string('firstname'),
            'lastname' => get_string('lastname'),
            'email' => get_string('email'),
        ];
        $this->columns = array_keys($this->headers);
    }


    
}

function uu_validate_user_upload_columns1(csv_import_reader $cir, $stdfields, $profilefields, moodle_url $returnurl) {
    $columns = $cir->get_columns();

    if (empty($columns)) {
        $cir->close();
        $cir->cleanup();
        throw new \moodle_exception('cannotreadtmpfile', 'error', $returnurl);
    }
    if (count($columns) < 2) {
        $cir->close();
        $cir->cleanup();
        throw new \moodle_exception('csvfewcolumns', 'error', $returnurl);
    }

    // test columns
    $processed = array();
    $acceptedfields = [
        'category',
        'categoryrole',
        'cohort',
        'course',
        'enrolperiod',
        'enrolstatus',
        'enroltimestart',
        'group',
        'role',
        'sysrole',
        'type',
    ];
    $specialfieldsregex = "/^(" . implode('|', $acceptedfields) . ")\d+$/";

    foreach ($columns as $key=>$unused) {
        $field = $columns[$key];
        $field = trim($field);
        $lcfield = core_text::strtolower($field);
        if (in_array($field, $stdfields) or in_array($lcfield, $stdfields)) {
            // standard fields are only lowercase
            $newfield = $lcfield;

        } else if (in_array($field, $profilefields)) {
            // exact profile field name match - these are case sensitive
            $newfield = $field;

        } else if (in_array($lcfield, $profilefields)) {
            // hack: somebody wrote uppercase in csv file, but the system knows only lowercase profile field
            $newfield = $lcfield;

        } else if (preg_match($specialfieldsregex, $lcfield)) {
            // special fields for enrolments
            $newfield = $lcfield;

        } else {
            $cir->close();
            $cir->cleanup();
            throw new \moodle_exception('invalidfieldname', 'error', $returnurl, $field);
        }
        if (in_array($newfield, $processed)) {
            $cir->close();
            $cir->cleanup();
            throw new \moodle_exception('duplicatefieldname', 'error', $returnurl, $newfield);
        }
        $processed[$key] = $newfield;
    }

    return $processed;
}

function uu_increment_username1($username) {
    global $DB, $CFG;

    if (!preg_match_all('/(.*?)([0-9]+)$/', $username, $matches)) {
        $username = $username.'2';
    } else {
        $username = $matches[1][0].($matches[2][0]+1);
    }

    if ($DB->record_exists('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
        return uu_increment_username($username);
    } else {
        return $username;
    }
}


function uu_process_template1($template, $user) {
    if (is_array($template)) {
        // hack for for support of text editors with format
        $t = $template['text'];
    } else {
        $t = $template;
    }
    if (strpos($t, '%') === false) {
        return $template;
    }

    $username  = isset($user->username)  ? $user->username  : '';
    $firstname = isset($user->firstname) ? $user->firstname : '';
    $lastname  = isset($user->lastname)  ? $user->lastname  : '';

    $callback = partial('uu_process_template_callback', $username, $firstname, $lastname);

    $result = preg_replace_callback('/(?<!%)%([+-~])?(\d)*([flu])/', $callback, $t);

    if (is_null($result)) {
        return $template; //error during regex processing??
    }

    if (is_array($template)) {
        $template['text'] = $result;
        return $t;
    } else {
        return $result;
    }
}

/**
 * Internal callback function.
 */
function uu_process_template_callback1($username, $firstname, $lastname, $block) {
    switch ($block[3]) {
        case 'u':
            $repl = $username;
            break;
        case 'f':
            $repl = $firstname;
            break;
        case 'l':
            $repl = $lastname;
            break;
        default:
            return $block[0];
    }

    switch ($block[1]) {
        case '+':
            $repl = core_text::strtoupper($repl);
            break;
        case '-':
            $repl = core_text::strtolower($repl);
            break;
        case '~':
            $repl = core_text::strtotitle($repl);
            break;
    }

    if (!empty($block[2])) {
        $repl = core_text::substr($repl, 0 , $block[2]);
    }

    return $repl;
}


function uu_supported_auths1() {
    // Get all the enabled plugins.
    $plugins = get_enabled_auth_plugins();
    $choices = array();
    foreach ($plugins as $plugin) {
        $objplugin = get_auth_plugin($plugin);
        // If the plugin can not be manually set skip it.
        if (!$objplugin->can_be_manually_set()) {
            continue;
        }
        $choices[$plugin] = get_string('pluginname', "auth_{$plugin}");
    }

    return $choices;
}

function uu_allowed_roles1() {
    // let's cheat a bit, frontpage is guaranteed to exist and has the same list of roles ;-)
    $roles = get_assignable_roles(context_course::instance(SITEID), ROLENAME_ORIGINALANDSHORT);
    return array_reverse($roles, true);
}


function uu_allowed_roles_cache1(?int $categoryid = null, ?int $courseid = null): array {
    if (!is_null($categoryid) && !is_null($courseid)) {
        return [];
    } else if (is_null($categoryid) && !is_null($courseid)) {
        $allowedroles = get_assignable_roles(context_course::instance($courseid), ROLENAME_SHORT);
    } else if (is_null($courseid) && !is_null($categoryid)) {
        $allowedroles = get_assignable_roles(context_coursecat::instance($categoryid), ROLENAME_SHORT);
    } else {
        $allowedroles = get_assignable_roles(context_course::instance(SITEID), ROLENAME_SHORT);
    }

    $rolecache = [];
    // A role can be searched for by its ID or by its shortname.
    foreach ($allowedroles as $rid=>$rname) {
        $rolecache[$rid] = new stdClass();
        $rolecache[$rid]->id   = $rid;
        $rolecache[$rid]->name = $rname;
        // Since numeric short names are allowed, to avoid replacement of another role, we only accept non-numeric values.
        if (!is_numeric($rname)) {
            $rolecache[$rname] = new stdClass();
            $rolecache[$rname]->id   = $rid;
            $rolecache[$rname]->name = $rname;
        }
    }
    return $rolecache;
}


function uu_allowed_sysroles_cache1() {
    $allowedroles = get_assignable_roles(context_system::instance(), ROLENAME_SHORT);
    $rolecache = [];
    foreach ($allowedroles as $rid => $rname) {
        $rolecache[$rid] = new stdClass();
        $rolecache[$rid]->id   = $rid;
        $rolecache[$rid]->name = $rname;
        if (!is_numeric($rname)) { // Only non-numeric shortnames are supported!
            $rolecache[$rname] = new stdClass();
            $rolecache[$rname]->id   = $rid;
            $rolecache[$rname]->name = $rname;
        }
    }
    return $rolecache;
}

/**
 * Pre process custom profile data, and update it with corrected value
 *
 * @param stdClass $data user profile data
 * @return stdClass pre-processed custom profile data
 */
function uu_pre_process_custom_profile_data1($data) {
    global $CFG;
    require_once($CFG->dirroot . '/user/profile/lib.php');
    $fields = profile_get_user_fields_with_data(0);

    // find custom profile fields and check if data needs to converted.
    foreach ($data as $key => $value) {
        if (preg_match('/^profile_field_/', $key)) {
            $shortname = str_replace('profile_field_', '', $key);
            if ($fields) {
                foreach ($fields as $formfield) {
                    if ($formfield->get_shortname() === $shortname && method_exists($formfield, 'convert_external_data')) {
                        $data->$key = $formfield->convert_external_data($value);
                    }
                }
            }
        }
    }
    return $data;
}

function uu_check_custom_profile_data1(&$data, array &$profilefieldvalues = []) {
    global $CFG;
    require_once($CFG->dirroot.'/user/profile/lib.php');

    $noerror = true;
    $testuserid = null;

    if (!empty($data['username'])) {
        if (preg_match('/id=(.*)"/i', $data['username'], $result)) {
            $testuserid = $result[1];
        }
    }
    $profilefields = profile_get_user_fields_with_data(0);
    // Find custom profile fields and check if data needs to converted.
    foreach ($data as $key => $value) {
        if (preg_match('/^profile_field_/', $key)) {
            $shortname = str_replace('profile_field_', '', $key);
            foreach ($profilefields as $formfield) {
                if ($formfield->get_shortname() === $shortname) {
                    if (method_exists($formfield, 'convert_external_data') &&
                            is_null($formfield->convert_external_data($value))) {
                        $data['status'][] = get_string('invaliduserfield', 'error', $shortname);
                        $noerror = false;
                    }

                    // Ensure unique field value doesn't already exist in supplied data.
                    $formfieldunique = $formfield->is_unique() && ($value !== '' || $formfield->is_required());
                    if ($formfieldunique && array_key_exists($shortname, $profilefieldvalues) &&
                            (array_search($value, $profilefieldvalues[$shortname]) !== false)) {

                        $data['status'][] = get_string('valuealreadyused') . " ({$key})";
                        $noerror = false;
                    }

                    // Check for duplicate value.
                    if (method_exists($formfield, 'edit_validate_field') ) {
                        $testuser = new stdClass();
                        $testuser->{$key} = $value;
                        $testuser->id = $testuserid;
                        $err = $formfield->edit_validate_field($testuser);
                        if (!empty($err[$key])) {
                            $data['status'][] = $err[$key].' ('.$key.')';
                            $noerror = false;
                        }
                    }

                    // Record value of unique field, so it can be compared for duplicates.
                    if ($formfieldunique) {
                        $profilefieldvalues[$shortname][] = $value;
                    }
                }
            }
        }
    }
    return $noerror;
}
