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
 * observer.php - Contains methods which are called by observers from db/events.php
 *
 * @since 3.1
 * @package    plagiarism_odessa
 * @author     Suan Kan <suankan@catalyst-au.net>
 * @copyright  2017 Catalyst IT https://www.catalyst-au.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_odessa;
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Class observers
 * Contains callback methods which are executed upon moodle events.
 * E.g. students upload or update files that need to be passed to plagiarism checker.
 * $observers defined in db/events.php execute methods from this class.
 *
 * @package plariarism_odessa
 */
class observer {

    /**
     * Draft for send updated file to ODESSA to check the API
     * https://docs.google.com/document/d/1-AUwdfauYsE2YtSgZcTblanZHCu00olzdApBNP3wtZA/edit#
     *
     * @param $event
     * @return bool
     * @throws \Exception
     */
    public static function callback_assignsubmission_file_assessable_submitted($event) {

        // Make sure we are processing a correct event.
        if ($event->eventname != '\assignsubmission_file\event\assessable_uploaded') {
            error_log('Unrecognised event');
        }

        $plugin = $event->component;
        $data = $event->get_data();

        $filepathnamehashes = $data['other']['pathnamehashes'];
        $fs = get_file_storage();

        foreach ($filepathnamehashes as $filepathnamehash) {
            // $contents = $filepathnamehash->get_content();
            $file = $fs->get_file_by_hash($filepathnamehash);

            // Mark the odessa_submission as updated in DB.
            $submissionmanager = new \plagiarism_odessa\submissions_manager($plugin, $filepathnamehash);

            // For each file:
            $clientapi = new \plagiarism_odessa\odessa_client_api($filepathnamehash);

            $clientapi->send_metadata_set_endpoint();
            $submissionmanager->set_status(ODESSA_SUBMISSION_STATUS_METADATA_SENT);

            $content = $file->get_content();
            $clientapi->send_file($content);
            $submissionmanager->set_status(ODESSA_SUBMISSION_STATUS_SENT);

            $clientapi->retrieve_file();
            $submissionmanager->set_status(ODESSA_SUBMISSION_STATUS_CHECKED);
        }
    }
}
