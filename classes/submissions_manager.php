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
 * submissions_manager.php - Contains methods to keep track of what we have submitted to ODESSA
 *
 * @since 3.1
 * @package    plagiarism_odessa
 * @author     Suan Kan <suankan@catalyst-au.net>
 * @copyright  2017 Catalyst IT https://www.catalyst-au.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_odessa;
/**
 * Class submissions_manager contains methods to keep track of what we have submitted to ODESSA
 */
class submissions_manager {

    public $id;
    public $userid;
    public $courseid;
    public $plugin;
    public $filename;
    public $status;
    public $event;
    public $assign;
    public $context;

    public function __construct($event) {
        global $DB;
        // TODO get a record in table mdl_odessa_submissions or create a new one if it doesn't exist.

        $this->event = $event->get_data();
        $this->userid = $event->userid;
        $this->courseid = $event->courseid;
        $this->context = $event->get_context();
        $this->component = $event->component;
        // Each event (derived from \mod_assign\events\base) must have assign property:
        // $this->plugin = $event->get_assign()->get_course_module()->get_module_type_name();
        $this->plugin = $event->component;
        $this->filename = $event->;

        // Retrieve the file from the Files API.
        // /$contextid/$component/$filearea/$itemid".$filepath.$filename
        $fs = get_file_storage();
        $file = $fs->get_file($event->contextid, $event->component, $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false; // The file does not exist.
        }
//
//        $this->context = $event->get_context();
//
//        $assign = $event->get_assign();
        $data = $event->component;
//        $cm_info = $assign->get_course_module();
//        $module = $cm_info->get_module_type_name();
        $conditions = [
            'userid' => $event->userid,
            'courseid' => $event->courseid,
            'plugin' => $this->plugin,
//            'filename' => $this->get_filename(),
        ];


        $this->id = $DB->get_record('odessa_submissions', $conditions);

    }

    /**
     * Retrieve content of the submission.
     */
    public function get_content() {
        // TODO. Get the content of the submission. It might be different for each Moodle plugin.
        // Retrieve the file from the Files API.
        $fs = get_file_storage();
        $file = $fs->get_file($this->event->contextid, $this->event->component, $filearea, $itemid, $filepath, $filename);
        if (!$file) {
            return false; // The file does not exist.
        }
    }
    /**
     * Return submission_id.
     * Get an existing one or
     *
     * @param $event
     */
    private function get_submission_id($event) {

    }



    public function record_file_submittion($event) {
        // TODO keep track of what files have been submitted to ODESSA in special DB table.
        // E.g. which user, which course, which topic, when, etc.
        return true;
    }

    public function record_check_file_submittion($event) {
        // TODO.
    }

    public function record_file_metadata_submittion($event) {
        // TODO.
    }
}
