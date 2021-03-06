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
use core\session\exception;

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
    public $type;
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
    'type' => "file" or "onlinetext",
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

        $submission = self::get_existing($params);
        if (!$submission) {
            $submission = self::create_new($params);
        }

        $this->id = $submission->id;
        $this->sourcecomponent = $submission->sourcecomponent;
        $this->type = $submission->type;
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

    public static function get_existing($params) {
        global $DB;

        // When checking existing submissions we should not check timecreated.
        unset($params['timecreated']);
        return $DB->get_record('odessa_submissions', $params);
    }

    /**
     * @param $params \stdClass object with properties sourcecomponent, userid, courseid, contextid, pathnamehash, contenthash
     * @return \stdClass
     */
    public static function create_new($params, $checkexists = false) {

        if ($checkexists) {
            $existingrecord = self::get_existing($params);
            if ($existingrecord) {
                return $existingrecord;
            }
        }

        global $DB;
        $submission = new \stdClass();
        $submission->sourcecomponent = $params['sourcecomponent'];
        $submission->type = $params['type'];
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
     */
    public static function get_existing_submissions_mod_assign() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        foreach (get_courses('all') as $course) {
            $coursecontext = \context_course::instance($course->id);
            foreach (get_enrolled_users($coursecontext) as $user) {
                foreach (get_coursemodules_in_course('assign', $course->id) as $coursemodassign) {
                    $coursemodassigncontext = \context_module::instance($coursemodassign->id);
                    $assign = new \assign($coursemodassigncontext, $coursemodassign, $course);
                    $submission = $assign->get_user_submission($user->id, false);
                    if ($submission) {
                        // mod_assign has submission sub-plugins: comments, file, onlinetext.
                        foreach ($assign->get_submission_plugins() as $submissionplugin) {

                            if ($submissionplugin->get_type() == 'onlinetext') {
                                $onlinetext = $submissionplugin->get_editor_text('onlinetext', $submission->id);
                                self::save_onlinetext('assignsubmission_onlinetext', $course->id, $user->id,
                                    $coursemodassigncontext->id, $submission->id, $onlinetext);
                            }

                            if ($submissionplugin->get_type() == 'file') {
                                foreach ($submissionplugin->get_files($submission, $user) as $file) {
                                    // Now need to save obtained files in submissions_manager
                                    $params = array(
                                        'sourcecomponent' => 'assignsubmission_file',
                                        'type' => 'file',
                                        'userid' => $user->id,
                                        'courseid' => $course->id,
                                        'contextid' => $coursemodassigncontext->id,
                                        'pathnamehash' => $file->get_pathnamehash(),
                                        'contenthash' => $file->get_contenthash(),
                                    );

                                    self::create_new($params, true);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Go through the list of all courses, enrolled users, mod_forum instances
     * and record them in odessa submission manager queue.
     */
    public static function get_existing_submissions_mod_forum() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

        foreach (get_courses('all') as $course) {
            $coursecontext = \context_course::instance($course->id);
            foreach (get_enrolled_users($coursecontext) as $user) {
                foreach (get_coursemodules_in_course('forum', $course->id) as $forum) {
                    foreach (forum_get_user_posts($forum->instance, $user->id) as $post) {
                        $text = $post->message;
                        $forumcontext = \context_module::instance($forum->id);
                        // Register post text body in odessa submission manager.
                        self::save_onlinetext('mod_forum', $course->id, $user->id, $forumcontext->id, $post->id, $text);

                        if ($post->attachment) {
                            $fs = get_file_storage();
                            $files = $fs->get_area_files($forumcontext->id, 'mod_forum',
                                'attachment', $post->id, "filename", false);

                            foreach ($files as $file) {
                                // Register post text attachment files in odessa submission manager.
                                $params = array(
                                    'sourcecomponent' => 'mod_forum',
                                    'type' => 'file',
                                    'userid' => $user->id,
                                    'courseid' => $course->id,
                                    'contextid' => $forumcontext->id,
                                    'pathnamehash' => $file->get_pathnamehash(),
                                    'contenthash' => $file->get_contenthash(),
                                );

                                self::create_new($params, true);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Go through the list of all courses, enrolled users, mod_workshop instances
     * and record them in odessa submission manager queue.
     */
    public static function get_existing_submissions_mod_workshop() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/workshop/locallib.php');

        foreach (get_courses('all') as $course) {
            $workshops = get_coursemodules_in_course('workshop', $course->id);
            foreach ($workshops as $workshop) {
                $cm = get_coursemodule_from_id('workshop', $workshop->id);
                $workshop = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
                $workshop = new \workshop($workshop, $cm, $course);
                // TODO:
                // Loop through all workshops and:
                // queue all onlinetext submissions via self::save_onlinetext()
                // queue all file submissions via self::create_new()

                $coursecontext = \context_course::instance($course->id);
                foreach (get_enrolled_users($coursecontext) as $user) {

                }
            }
        }
    }

    /**
     * Save onlinetext submission via Moodle File API.
     * If a moodle file already exists:
     *   add record to mdl_odessa_submissions with existing contenthash
     * If a moodle file doesn't exist:
     *   create a moodle file
     *   add a new record to mdl_odessa_submissions
     *
     * @param $courseid
     * @param $userid
     * @param $coursemodulecontextid
     * @param $submissionid
     * @param $onlinetext
     */
    public static function save_onlinetext($sourcecomponent, $courseid, $userid, $coursemodulecontextid, $submissionid, $onlinetext) {

        $file = self::file_exists_onlinetext($sourcecomponent, $coursemodulecontextid, $userid, $submissionid);

        if (!$file) {
            $file = self::create_file_onlinetext($sourcecomponent, $coursemodulecontextid, $userid, $submissionid, $onlinetext);
        }

        $params = array();
        $params['sourcecomponent'] = $sourcecomponent;
        $params['type'] = 'onlinetext';
        $params['courseid'] = $courseid;
        $params['userid'] = $userid;
        $params['contextid'] = $coursemodulecontextid;
        $params['pathnamehash'] = $file->get_pathnamehash();
        $params['contenthash'] = $file->get_contenthash();

        self::create_new($params, true);
    }

    public static function create_file_onlinetext($sourcecomponent, $contextid, $userid, $itemid, $content) {
        $fs = get_file_storage();

        $filerecord = new \stdClass;
        $filerecord->component = 'odessa_submissions';
        $filerecord->filearea = $sourcecomponent;
        $filerecord->contextid = $contextid;
        $filerecord->userid = $userid;
        $filerecord->itemid = $itemid;
        $filerecord->filename = 'onlinetext.txt';
        $filerecord->filepath = '/';

        $file = $fs->create_file_from_string($filerecord, $content);
        return $file;
    }

    public static function file_exists_onlinetext($sourcecomponent, $contextid, $userid, $submissionid) {
        $fs = get_file_storage();
        $file = $fs->get_file($contextid, 'odessa_submissions', $sourcecomponent, $submissionid, '/', 'onlinetext.txt');

        if ($file and $file->get_userid() == $userid) {
            return $file;
        } else {
            return false;
        }
    }
}
