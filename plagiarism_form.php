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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');
require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class plagiarism_setup_form is added to the plugin config settings.php
 */
class plagiarism_setup_form extends moodleform {
    public function definition () {

        global $CFG;

        $mform =& $this->_form;
        $choices = array('No','Yes');
        $mform->addElement('html', get_string('odessaexplain', 'plagiarism_odessa'));
        $mform->addElement('checkbox', 'odessa_use', get_string('useodessa', 'plagiarism_odessa'));

        $mform->addElement('textarea', 'odessa_student_disclosure', get_string('studentdisclosure', 'plagiarism_odessa'),
            'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('odessa_student_disclosure', 'studentdisclosure', 'plagiarism_odessa');
        $mform->setDefault('odessa_student_disclosure', get_string('studentdisclosuredefault', 'plagiarism_odessa'));

        $this->add_action_buttons(true);
    }
}

