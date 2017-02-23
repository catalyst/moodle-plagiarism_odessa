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
 * $observers array attaches callbacks to moodle events.
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$observers = array (
    // Observers for events in mod_assign.
    array(
        'eventname' => '\mod_assign\event\submission_created',
        'callback' => 'plagiarism_odessa_observer::callback_submission_created',
    ),
    array(
        'eventname' => '\mod_assign\event\submission_updated',
        'callback' => 'plagiarism_odessa_observer::callback_submission_updated',
    ),
);
