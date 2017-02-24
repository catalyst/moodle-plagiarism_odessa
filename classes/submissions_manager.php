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
/**
 * Class submissions_manager contains methods to keep track of what we have submitted to ODESSA
 */
class submissions_manager {
    public static function record_file_submittion() {
        // TODO keep track of what files have been submitted to ODESSA in special DB table.
        // E.g. which user, which course, which topic, when, etc.
        return true;
    }

    public static function record_check_file_submittion() {
        // TODO.
    }

    public static function record_file_metadata_submittion() {
        // TODO.
    }
}
