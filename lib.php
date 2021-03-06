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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 *
 * @since 3.1
 * @package    plagiarism_odessa
 * @subpackage plagiarism
 * @copyright  2017 Catalyst IT https://www.catalyst-au.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

global $CFG;
require_once($CFG->dirroot.'/plagiarism/lib.php');
require_once('locallib.php');

class plagiarism_plugin_odessa extends plagiarism_plugin {
    /**
     * hook to allow plagiarism specific information to be displayed beside a submission
     * @param array  $linkarraycontains all relevant information for the plugin to generate a link
     * @return string
     */
    public function get_links($linkarray) {

        // when $linkarray['content'] is set this is assignsubmission_onlinetext.
        if (array_key_exists('content', $linkarray)) {
            $params = array(
                'component' => 'assignsubmission_onlinetext',
                'objecttable' => 'assign_submission',
                'objectid' => $linkarray['assignment'],
                'userid' => $linkarray['userid'],
                'courseid' => $linkarray['course'],
            );
        }

        // when $linkarray['file'] is set then this is assignsubmission_file
        if (array_key_exists('file', $linkarray)) {
            $context = context_module::instance($linkarray['cmid']);
            /*
            $context->
            $params = array(
                'component' => 'assignsubmission_file',
                'objecttable' => 'assign_submission',
                'objectid' => $linkarray['assignment'],
                'userid' => $linkarray['userid'],
                'courseid' => $linkarray['course'],
            );
            */
        }

        // $submission = new \plagiarism_odessa\submissions_manager($params);

        $output = 'ODESSA score: 20 ' . PHP_EOL;
        // Add link/information about this file to $output.

        return $output;
    }

    /**
     * hook to save plagiarism specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    public function save_form_elements($data) {

    }

    /**
     * hook to add plagiarism specific settings to a module settings page
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */
    public function get_form_elements_module($mform, $context, $modulename = "") {
        // Add elements to form using standard mform like:
        // $mform->addElement('hidden', $element);
        // $mform->disabledIf('plagiarism_draft_submit', 'var4', 'eq', 0);

    }

    /**
     * hook to allow a disclosure to be printed notifying users what will happen with their submission
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
        global $OUTPUT;
        $plagiarismsettings = (array)get_config('plagiarism');
        // TODO: check if this cmid has plagiarism enabled.
        echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        echo format_text($plagiarismsettings['odessa_student_disclosure'], FORMAT_MOODLE, $formatoptions);
        echo $OUTPUT->box_end();
    }

    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     */
    public function update_status($course, $cm) {
        return "return update_status qweqwe";
        // Called at top of submissions/grading pages - allows printing of admin style links or updating status.
    }

    /**
     * called by admin/cron.php
     */
    public function cron() {
        // Do any scheduled task stuff.
    }
}
