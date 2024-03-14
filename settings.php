<?php
// This file is part of eMailTest plugin for Moodle - https://moodle.org/
//
// eMailTest is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// eMailTest is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with eMailTest.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Adds eMail Test link to the Site Administration > Server menu. There are no settings for this plugin.
 *
 * @package    local_mailtest
 * @copyright  2015-2024 TNG Consulting Inc. - www.tngconsulting.ca
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    if (!isset($CFG->branch) || $CFG->branch >= 32) { // Moodle 3.2 and later.
        $section = 'localplugins';
    } else { // Up to and including Moodle 3.1.
        $section = 'server';
    }
    $ADMIN->add($section, new admin_externalpage('local_user_queue_mail',
            get_string('pluginname', 'local_user_queue_mail'),
            new moodle_url('/local/user_queue_mail/index.php')
    ));
	
	$ADMIN->add($section, new admin_externalpage('local_user_queue_mail',
            get_string('view_users', 'local_user_queue_mail'),
            new moodle_url('/local/user_queue_mail/view_users.php')
    ));
	$ADMIN->add($section, new admin_externalpage('local_user_queue_mail',
            get_string('send_random_email', 'local_user_queue_mail'),
            new moodle_url('/local/user_queue_mail/send_random_email.php')
    ));
}
