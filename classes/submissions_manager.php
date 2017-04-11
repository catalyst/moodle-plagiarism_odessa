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
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

// Odessa submission statuses.
define('ODESSA_SUBMISSION_STATUS_NEW', 0);
define('ODESSA_SUBMISSION_STATUS_METADATA_SENT', 1);
define('ODESSA_SUBMISSION_STATUS_SENT', 2);
define('ODESSA_SUBMISSION_STATUS_CHECKED', 3);
define('ODESSA_SUBMISSION_STATUS_PROCESSED', 4);

/**
 * Class submissions_manager contains methods to keep track of what we have submitted to ODESSA
 */
class submissions_manager {

    public $id;
    public $sourcecomponent;
    public $userid;
    public $courseid;
    public $contextid;
    public $pathnamehashe;
    public $contenthash;
    public $status;
    public $laststatus;
    public $result;
    public $timecreated;
    public $timeupdated;

    /**
     * submissions_manager constructor.
     * Keeping track of odessa submissions.
     * Load existing record from odessa_submissions if it exists otherwise create a new one.
     *
     * @param $params = array(
    'sourcecomponent' => $eventdata['component'],
    'userid' => $eventdata['userid'],
    'courseid' => $eventdata['courseid'],
    'contextid' => $eventdata['contextid'],
    'pathnamehashe' => $file->get_pathnamehash(),
    'contenthash' => $file->get_contenthash(),
    'timecreated' => $eventdata['timecreated'],
    );
     * )
     */
    public function __construct($params) {
        if ($submission = $this->get_existing($params)) {
        } else {
            $submission = $this->create_new($params);
        }
        $this->id = $submission->id;
        $this->sourcecomponent = $submission->sourcecomponent;
        $this->userid = $submission->userid;
        $this->courseid = $submission->courseid;
        $this->contextid = $submission->contextid;
        $this->pathnamehash = $submission->pathnamehash;
        $this->contenthash = $submission->contenthash;
        $this->status = $submission->status;
        $this->laststatus = $submission->laststatus;
        $this->result = $submission->result;
        $this->timecreated = $submission->timecreated;
        $this->timeupdated = $submission->timeupdated;
    }

    protected function get_existing($params) {
        global $DB;

        // When checking existing submissions we should not check timecreated.
        unset($params['timecreated']);
        return $DB->get_record('odessa_submissions', $params);
    }

    protected function create_new($params) {
        global $DB;
        $submission = new \stdClass();
        $submission->sourcecomponent = $params['sourcecomponent'];
        $submission->userid = $params['userid'];
        $submission->courseid = $params['courseid'];
        $submission->contextid = $params['contextid'];
        $submission->pathnamehash = $params['pathnamehash'];
        $submission->contenthash = $params['contenthash'];
        $submission->status = ODESSA_SUBMISSION_STATUS_NEW;
        $submission->laststatus = '';
        $submission->result = '';
        $submission->timecreated = time();
        $submission->timeupdated = time();
        $submission->id = $DB->insert_record('odessa_submissions', $submission);

        return $submission;
    }

    /**
     * Update status of the odessa_submission
     *
     * @param $status
     * @return bool
     */
    public function set_status($status) {
        global $DB;
        $this->laststatus = $this->status;
        $this->status = $status;
        $this->timeupdated = time();
        if ($DB->update_record('odessa_submissions', $this)) {
            return true;
        }
    }

    /**
     * Go through the list of all courses, enrolled users, assignment submissions modules
     * currently assignsubmission_onlinetext and assignsubmission_file
     * and return them.
     *
     * @param string $modulename
     */
    public static function get_existing_submissions($modulename = 'assign') {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $existingsubmissions = array();

        foreach (get_courses('all') as $course) {
            $coursecontext = \context_course::instance($course->id);
            foreach (get_enrolled_users($coursecontext) as $user) {
                foreach (get_coursemodules_in_course($modulename, $course->id) as $coursemodule) {
                    $coursemodulecontext = \context_module::instance($coursemodule->id);
                    $assign = new \assign($coursemodulecontext, $coursemodule, $course);
                    foreach ($assign->get_submission_plugins() as $submissionplugin) {
                        if ($submissionplugin->get_type() == 'onlinetext') {
                            $submission = $assign->get_user_submission($user->id, false);
                            if ($submission) {
                                $onlinetext = $submissionplugin->get_editor_text('onlinetext', $submission->id);
                                self::save_onlinetext($course->id, $coursemodulecontext->id, $user->id, $submission->id, $onlinetext);
                            }
                        }

                        if ($submissionplugin->get_type() == 'file') {
                            // Fetch file obejcts
                            $submission = $assign->get_user_submission($user->id, false);
                            if ($submission) {
                                self::save_files($submissionplugin, $user, $submission, $course->id, $coursemodulecontext->id);
                            }
                        }
                    }
                }
            }
        }

        return $existingsubmissions;
    }

    public static function save_onlinetext($courseid, $coursemodulecontextid, $userid, $submissionid, $onlinetext) {
        $contenthash = sha1($onlinetext);

        $fs = get_file_storage();

        $filerecord = new \stdClass;
        $filerecord->component = 'odessa_submissions';
        $filerecord->filearea = 'assignsubmission_onlinetext';
        $filerecord->contextid = $coursemodulecontextid;
        $filerecord->userid = $userid;
        $filerecord->itemid = $submissionid;
        $filerecord->filename = 'onlinetext.txt';
        $filerecord->filepath = '/';

        // If file with this contenthash already exists we get reference to that file.
        if ($fs->content_exists($contenthash)) {
            $file = $fs->get_file_instance($filerecord);
        } else { // If it doesn't exist then we create it.
            $file = $fs->create_file_from_string($filerecord, $onlinetext);
        }

        $params = array(
            'component' => 'assignsubmission_onlinetext',
            'userid' => $userid,
            'courseid' => $courseid,
            'contextid' => $coursemodulecontextid,
            'pathnamehash' => $file->get_pathnamehash(),
            'contenthash' => $file->get_contenthash(),
            'timecreated' => time(),
        );

        new self($params);
    }

    public static function save_files($submissionplugin, $user, $submission, $courseid, $coursemodulecontextid) {
        foreach ($submissionplugin->get_files($submission, $user) as $file) {
            // Now need to save obtained files in submissions_manager
            $params = array(
                'component' => 'assignsubmission_file',
                'userid' => $user->id,
                'courseid' => $courseid,
                'contextid' => $coursemodulecontextid,
                'pathnamehash' => $file->get_pathnamehash(),
                'contenthash' => $file->get_contenthash(),
                'timecreated' => time(),
            );

            new self($params);
        }
    }

}
