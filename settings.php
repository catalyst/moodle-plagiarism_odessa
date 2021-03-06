<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * plagiarism.php - allows the admin to configure plagiarism stuff
 *
 * @package   plagiarism_turnitin
 * @author    Dan Marsden <dan@danmarsden.com>
 * @copyright 2017 Catalyst IT https://www.catalyst-au.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/odessa/lib.php');
require_once($CFG->dirroot.'/plagiarism/odessa/plagiarism_form.php');

require_login();
admin_externalpage_setup('plagiarismodessa');

$context = context_system::instance();

// submissions = submissions_mod_assign::get_file_submissions();

require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

require_once('plagiarism_form.php');
$mform = new plagiarism_setup_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/plagiarism.php'));
}

echo $OUTPUT->header();

if (($data = $mform->get_data()) && confirm_sesskey()) {
    if (!isset($data->odessa_use)) {
        $data->odessa_use = 0;
    }
    if (!isset($data->odessa_mod_assign)) {
        $data->odessa_mod_assign = 0;
    }
    if (!isset($data->odessa_mod_forum)) {
        $data->odessa_mod_forum = 0;
    }
    if (!isset($data->odessa_mod_workshop)) {
        $data->odessa_mod_workshop = 0;
    }
    foreach ($data as $field => $value) {
        if (strpos($field, 'odessa') === 0) {
            set_config($field, $value, 'plagiarism');
        }
    }
    echo $OUTPUT->notification(get_string('savedconfigsuccess', 'plagiarism_odessa'), 'notifysuccess');
}
$plagiarismsettings = (array)get_config('plagiarism');
$mform->set_data($plagiarismsettings);

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
