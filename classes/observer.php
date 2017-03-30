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
    public static function callback_upon_assessable_uploaded($event) {
        $eventdata = $event->get_data();

        $params = array(
            'component' => $eventdata['component'],
            'objecttable' => $eventdata['objecttable'],
            'objectid' => $eventdata['objectid'],
            'userid' => $eventdata['userid'],
            'courseid' => $eventdata['courseid'],
        );

        new submissions_manager($params);
    }
}
