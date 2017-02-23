<?php

/**
 * $observers array attaches callbacks to moodle events.
 */

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
