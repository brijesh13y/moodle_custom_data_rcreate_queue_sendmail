<?php

namespace local_user_queue_mail\local;

defined('MOODLE_INTERNAL') || die();

class field_value_validators {

    protected static $themescache;

    public static function validate_theme($value) {
        global $CFG;

        $status = 'normal';
        $message = '';
        if (!$CFG->allowuserthemes) {
            $status = 'warning';
            $message = get_string('userthemesnotallowed', 'tool_uploaduser');
        } else {
            if (!isset(self::$themescache)) {
                self::$themescache = get_list_of_themes();
            }

            if (empty($value)) {
                $status = 'warning';
                $message = get_string('notheme', 'tool_uploaduser');
            } else if (!isset(self::$themescache[$value])) {
                $status = 'warning';
                $message = get_string('invalidtheme', 'tool_uploaduser', s($value));
            }
        }

        return [$status, $message];
    }
}
