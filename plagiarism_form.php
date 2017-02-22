<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class plagiarism_setup_form extends moodleform {

/// Define the form
    function definition () {
        global $CFG;

        $mform =& $this->_form;
        $choices = array('No','Yes');
        $mform->addElement('html', get_string('odessaexplain', 'plagiarism_odessa'));
        $mform->addElement('checkbox', 'odessa_use', get_string('useodessa', 'plagiarism_odessa'));

        $mform->addElement('textarea', 'odessa_student_disclosure', get_string('studentdisclosure','plagiarism_odessa'),'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('odessa_student_disclosure', 'studentdisclosure', 'plagiarism_odessa');
        $mform->setDefault('odessa_student_disclosure', get_string('studentdisclosuredefault','plagiarism_odessa'));

        $this->add_action_buttons(true);
    }
}

