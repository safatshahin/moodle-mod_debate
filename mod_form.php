<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_debate configuration form.
 *
 * @package     mod_debate
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/debate/locallib.php');
require_once($CFG->libdir.'/filelib.php');
require_once("$CFG->libdir/formslib.php");

class mod_debate_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('debatename', 'debate'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'debate', get_string('debate_topic', 'debate'), array('size' => '64'));
        $mform->setType('debate', PARAM_RAW);
        $mform->addRule('debate', get_string('required'), 'required', null, 'client');

        $this->standard_intro_elements();
        $mform->addElement('advcheckbox', 'debateformat', get_string('showinmodule', 'mod_debate'));

        $response_type = array(
            '0' => get_string('unlimited_response', 'mod_debate'),
            '1' => get_string('one_response', 'mod_debate'),
            '2' => get_string('two_response', 'mod_debate')
        );
        $mform->addElement('select', 'responsetype', get_string('user_response', 'mod_debate'), $response_type);
        $mform->setDefault('responsetype', 0);


        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}
