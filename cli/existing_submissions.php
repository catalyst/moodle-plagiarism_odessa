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

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
global $CFG;
require_once($CFG->libdir.'/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'plugin' => false,
        'help' => false
    ),
    array(
        'h' => 'help'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!$options['plugin']) {
    $help ="Create alternative component cache file

Options:
-h, --help            Print out this help
--plugin=mod_assign   Get existing submissions in specified plugin

Example:
\$ php plagiarism/odessa/cli/existing_submissions.php --plugin=mod_assign
";

    echo $help;
    exit(0);
} elseif ($options['plugin'] == 'mod_assign') {
    submissions_manager::get_submissions_assignsubmission_file();
    submissions_manager::get_submissions_assignsubmission_onlinetext();

    echo "End" . PHP_EOL;
}
