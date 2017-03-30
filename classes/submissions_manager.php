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

use time;

/**
 * Class submissions_manager contains methods to keep track of what we have submitted to ODESSA
 */
class submissions_manager {

    public $id;
    public $component;
    public $objecttable;
    public $objectid;
    public $userid;
    public $courseid;
    public $contextid;
    public $contextinstanceid;
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
     * @param $params array like array(
     *      'component' => $component,
     *      'objecttable' => $objecttable,
     *      'objectid' => $objectid,
     *      'userid' => $userid,
     *      'courseid' => $courseid,
     * )
     */
    public function __construct($params) {
        if ($submission = $this->get_existing($params)) {
        } else {
            $submission = $this->create_new($params);
        }
        $this->id = $submission->id;
        $this->component = $submission->component;
        $this->objecttable = $submission->objecttable;
        $this->objectid = $submission->objectid;
        $this->userid = $submission->userid;
        $this->courseid = $submission->courseid;
        $this->status = $submission->status;
        $this->laststatus = $submission->laststatus;
        $this->result = $submission->result;
        $this->timecreated = $submission->timecreated;
        $this->timeupdated = $submission->timeupdated;
    }

    protected function get_existing($params) {
        global $DB;
        return $DB->get_record('odessa_submissions', $params);
    }

    protected function create_new($params) {
        global $DB;
        $newodessasubmission = new \stdClass();
        $newodessasubmission->component = $params['component'];
        $newodessasubmission->objecttable = $params['objecttable'];
        $newodessasubmission->objectid = $params['objectid'];
        $newodessasubmission->userid = $params['userid'];
        $newodessasubmission->courseid = $params['courseid'];
        $newodessasubmission->status = ODESSA_SUBMISSION_STATUS_NEW;
//        $newodessasubmission->laststatus = '';
//        $newodessasubmission->result = '';
        $newodessasubmission->timecreated = time();
        $newodessasubmission->timeupdated = time();
        $newodessasubmission->id = $DB->insert_record('odessa_submissions', $newodessasubmission);

        return $newodessasubmission;
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
     * Get existing file submissions from mod_assign and put references to them
     * in mdl_odessa_submissions it it doesn't exist.
     */
    public static function get_submissions_assignsubmission_file() {
        global $DB;

        $sql = '
SELECT
    ass.id,
    \'assignsubmission_file\' as "component",
    \'assign_submission\' as "objecttable",
    ass.assignment as "objectid",
    ass.userid as "userid",
    a.course as "courseid"
FROM
    {assignsubmission_file} assf
    INNER JOIN {assign_submission} ass ON ass.assignment = assf.assignment AND ass.id = assf.submission
    JOIN {assign} a on a.id = ass.assignment';

        $filesubmissions = $DB->get_records_sql($sql);


        foreach ($filesubmissions as $submission) {
            // Prepare submissions_manager object params.
            $params = array(
                'component' => $submission->component,
                'objecttable' => $submission->objecttable,
                'objectid' => $submission->objectid,
                'userid' => $submission->userid,
                'courseid' => $submission->courseid,
            );
            $odessasubmission = new submissions_manager($params);
        }
    }

    /**
     * Get existing onlinetext submissions from mod_assign and put references to them
     * in mdl_odessa_submissions it it doesn't exist.
     */
    public static function get_submissions_assignsubmission_onlinetext() {
        global $DB;

        $sql = '
SELECT
    ass.id,
    \'assignsubmission_onlinetext\' as "component",
    \'assign_submission\' as "objecttable",
    ass.assignment as "objectid",
    ass.userid as "userid",
    a.course as "courseid"
FROM
    {assignsubmission_onlinetext} asso
    JOIN {assign_submission} ass ON ass.assignment = asso.assignment AND ass.id = asso.submission
    JOIN {assign} a ON a.id = ass.assignment';

        $onlinetextsubmissions = $DB->get_records_sql($sql);

        foreach ($onlinetextsubmissions as $submission) {
            // Prepare submissions_manager object params.
            $params = array(
                'component' => $submission->component,
                'objecttable' => $submission->objecttable,
                'objectid' => $submission->objectid,
                'userid' => $submission->userid,
                'courseid' => $submission->courseid,
            );
            $odessasubmission = new submissions_manager($params);
        }
    }

}
