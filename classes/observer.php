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
     * Record a submission from assignsubmission_onlinetext event 'assessable_uploaded'
     * in odessa submissions manager.
     *
     * @param $event object expected event is object of extension of class \core\event\assessable_uploaded, e.g.
     * \assignsubmission_onlinetext\event\assessable_uploaded
     * \assignsubmission_file\event\assessable_uploaded
     * \mod_workshop\event\assessable_uploaded
     * \mod_forum\event\assessable_uploaded
     */
    public static function callback_assessable_uploaded_file($event) {
        $eventdata = $event->get_data();
        $filepathnamehashes = $eventdata['other']['pathnamehashes'];

        $fs = get_file_storage();
        foreach ($filepathnamehashes as $pathnamehash) {
            $file = $fs->get_file_by_hash($pathnamehash);
            $params = array(
                'sourcecomponent' => $eventdata['component'],
                'type' => 'file',
                'userid' => $eventdata['userid'],
                'courseid' => $eventdata['courseid'],
                'contextid' => $eventdata['contextid'],
                'pathnamehash' => $pathnamehash,
                'contenthash' => $file->get_contenthash(),
                'timecreated' => $eventdata['timecreated'],
            );

            submissions_manager::create_new($params, true);
        }
    }

    public static function callback_assessable_uploaded_onlinetext($event) {
        $eventdata = $event->get_data();
        $onlinetext = $eventdata['other']['content'];

        submissions_manager::save_onlinetext($eventdata['courseid'], $eventdata['userid'], $eventdata['contextinstanceid'],
            $eventdata['objectid'], $onlinetext);

    }

    /**
     * Retrieves the submission data from the event and registers it in odessa submission manager queue.
     * Submission data here could be a combination of onlinetext (forum post text) and attached files.
     *
     * @param $event \mod_forum\event\assessable_uploaded event.
     */
    public static function callback_assessable_uploaded_mod_forum($event) {
        $eventdata = $event->get_data();

        // Record files from the forum post in odessa submission manager queue.
        $filepathnamehashes = $eventdata['other']['pathnamehashes'];

        $fs = get_file_storage();
        foreach ($filepathnamehashes as $pathnamehash) {
            $file = $fs->get_file_by_hash($pathnamehash);
            $params = array(
                'sourcecomponent' => 'mod_forum',
                'type' => 'file',
                'userid' => $eventdata['userid'],
                'courseid' => $eventdata['courseid'],
                'contextid' => $eventdata['contextid'],
                'pathnamehash' => $pathnamehash,
                'contenthash' => $file->get_contenthash(),
                'timecreated' => $eventdata['timecreated'],
            );

            submissions_manager::create_new($params, true);
        }

        // Record onlinetext (forum post text) in odessa submission manager queue.
        $onlinetext = $eventdata['other']['content'];
        submissions_manager::save_onlinetext('mod_forum', $eventdata['courseid'], $eventdata['userid'],
            $eventdata['contextid'], $eventdata['objectid'], $onlinetext);
    }

    public static function callback_assessable_uploaded_mod_workshop($event) {
        // TODO.
        //$eventdata = $event->get_data();
    }
}
