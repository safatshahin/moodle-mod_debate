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
 * Manage teams form of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/lib/grouplib.php');
require_once($CFG->dirroot . '/lib/datalib.php');

/**
 * Class debate_teams_form.
 * An extension of your usual Moodle form.
 */

class debate_teams_form extends moodleform {

    /**
     * Defines the custom structure_form.
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $courseid = $this->_customdata['courseid'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name','mod_debate'));
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addRule('name',get_string('maximum_character_255', 'mod_debate'), 'maxlength', 255, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'responseallowed', get_string('responseallowed','mod_debate'));
        $mform->addRule('responseallowed', get_string('required'), 'required', null, 'client');
        $mform->addRule('responseallowed',get_string('maximum_character_2', 'mod_debate'), 'maxlength', 2, 'client');
        $mform->setType('responseallowed', PARAM_TEXT);

        $options = array(
            'multiple' => true
        );
        $groups = $DB->get_records('groups', array('courseid' => (int)$courseid));
        $course_groups = array();
        foreach ($groups as $group) {
            $course_groups[$group->id] = $group->name;
        }
        $mform->addElement('autocomplete', 'groupselection', get_string('groupselection', 'mod_debate'), $course_groups, $options);
        $mform->addRule('groupselection', get_string('required'), 'required', null, 'client');
        $mform->setDefault('groupselection', 0);

        $mform->addElement('hidden', 'active');
        $mform->setType('active', PARAM_INT);

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        $this->set_data($data);
    }


    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return string
     */
//    public function export_for_template() {
//        ob_start();
//        $this->display();
//        $formhtml = ob_get_contents();
//        ob_end_clean();
//
//        return $formhtml;
//    }

}
