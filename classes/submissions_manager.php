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
    /**
     * @var name of the submission plugin.
     */
    public $plugin;
    public $filepathnamehash;
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
     * @param $plugin
     * @param $filepathnamehash
     */
    public function __construct($plugin, $filepathnamehash) {

        if (!$plugin or !$filepathnamehash) {
            error_log('submissions_manager must be initialised with two parameters!');
            return false;
        }

        global $DB;

        $existingodessasubmission = $DB->get_record('odessa_submissions', [
            'plugin' => $plugin,
            'filepathnamehash' => $filepathnamehash,
        ]); // TODO need to create index on this pair.
        if ($existingodessasubmission) {
            // Get a record from table mdl_odessa_submissions
            $this->id = $existingodessasubmission->id;
            $this->plugin = $existingodessasubmission->plugin;
            $this->filepathnamehash = $existingodessasubmission->filepathnamehash;
            $this->status = $existingodessasubmission->status;
            $this->laststatus = $existingodessasubmission->laststatus;
            $this->result = $existingodessasubmission->result;
            $this->timecreated = $existingodessasubmission->timecreated;
            $this->timeupdated = $existingodessasubmission->timeupdated;

            return $this;
        } else {
            // Create a new record.
            $newodessasubmission = new \stdClass();
            $newodessasubmission->plugin = $plugin;
            $newodessasubmission->filepathnamehash = $filepathnamehash;
            $newodessasubmission->status = ODESSA_SUBMISSION_STATUS_NEW;
            $newodessasubmission->timecreated = time();
            $newodessasubmission->timeupdated = time();
            $newodessasubmission->id = $DB->insert_record('odessa_submissions', $newodessasubmission);

            return $newodessasubmission;
        }
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
}
