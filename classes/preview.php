<?php

namespace local_user_queue_mail;

defined('MOODLE_INTERNAL') || die();

use local_user_queue_mail\local\field_value_validators;

require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/local/user_queue_mail/locallib.php');

class preview extends \html_table {
    protected $cir;
    /** @var array */
    protected $filecolumns;
    /** @var int */
    protected $previewrows;
    /** @var bool */
    protected $noerror = true; // Keep status of any error.

    public function __construct(\csv_import_reader $cir, array $filecolumns, int $previewrows) {
        parent::__construct();
        $this->cir = $cir;
        $this->filecolumns = $filecolumns;
        $this->previewrows = $previewrows;

        $this->id = "uupreview";
        $this->attributes['class'] = 'generaltable';
        $this->tablealign = 'center';
        $this->summary = get_string('uploaduserspreview', 'local_user_queue_mail');
        $this->head = array();
        $this->data = $this->read_data();

        $this->head[] = get_string('uucsvline', 'local_user_queue_mail');
        foreach ($filecolumns as $column) {
            $this->head[] = $column;
        }

    }

    public function read_data() {
        global $DB, $CFG;
        $profilefieldvalues = [];

        $data = array();
        $this->cir->init();
        $linenum = 1; // Column header is first line.
        while ($linenum <= $this->previewrows and $fields = $this->cir->next()) {
            $linenum++;
            $rowcols = array();
            $rowcols['line'] = $linenum;
            foreach ($fields as $key => $field) {
                $rowcols[$this->filecolumns[$key]] = s(trim($field));
            }
            $rowcols['status'] = array();


            if (isset($rowcols['email'])) {
                if (!validate_email($rowcols['email'])) {
                    $rowcols['status'][] = get_string('invalidemail');
                }

                $select = $DB->sql_like('email', ':email', false, true, false, '|');
                $params = array('email' => $DB->sql_like_escape($rowcols['email'], '|'));
                if ($DB->record_exists_select('user', $select , $params)) {
                    $rowcols['status'][] = get_string('useremailduplicate', 'error');
                }
            }

            if (isset($rowcols['theme'])) {
                list($status, $message) = field_value_validators::validate_theme($rowcols['theme']);
                if ($status !== 'normal' && !empty($message)) {
                    $rowcols['status'][] = $message;
                }
            }

            // Check if rowcols have custom profile field with correct data and update error state.
            $this->noerror = uu_check_custom_profile_data($rowcols, $profilefieldvalues) && $this->noerror;
            $rowcols['status'] = implode('<br />', $rowcols['status']);
            $data[] = $rowcols;
        }
        if ($fields = $this->cir->next()) {
            $data[] = array_fill(0, count($fields) + 2, '...');
        }
        $this->cir->close();

        return $data;
    }

    public function get_no_error() {
        return $this->noerror;
    }
}