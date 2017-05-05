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

namespace plagiarism_odessa;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

$observers = array (
    // Observers for events in mod_assign.
    array(
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback' => 'plagiarism_odessa\observer::callback_assessable_uploaded_onlinetext',
    ),
    array(
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => 'plagiarism_odessa\observer::callback_assessable_uploaded_file',
    ),
    // Observers for events in mod_workshop.
    array(
        'eventname' => '\mod_workshop\event\assessable_uploaded',
        'callback' => 'plagiarism_odessa\observer::callback_assessable_uploaded_mod_workshop',
    ),
    // Observers for events in mod_forum.
    array(
        'eventname' => '\mod_forum\event\assessable_uploaded',
        'callback' => 'plagiarism_odessa\observer::callback_assessable_uploaded_mod_forum',
    ),
);
