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
 * Define all the backup steps that will be used by the backup_debate_activity_task.
 *
 * @package     mod_debate
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete debate structure for backup, with file and id annotations
 */
class backup_debate_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $debate = new backup_nested_element('debate', array('id'),
                                              array('course',
                                                    'name',
                                                    'debate',
                                                    'debateformat',
                                                    'responsetype',
                                                    'intro',
                                                    'introformat',
                                                    'timecreated',
                                                    'timemodified'));
        $debate->set_source_table('debate', array('id' => backup::VAR_ACTIVITYID));

        $debateresponse = new backup_nested_element('debate_response', array('id'),
                                                        array('courseid',
                                                            'debateid',
                                                            'cmid',
                                                            'userid',
                                                            'response',
                                                            'responsetype',
                                                            'timecreated',
                                                            'timemodified'));
        // All the rest of elements only happen if we are including user info.
        $debate->add_child($debateresponse);

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $debateresponse->set_source_table('debate_response', array('debateid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $debateresponse->annotate_ids('user', 'userid');

        // Define file annotations
        $debate->annotate_files('mod_debate', 'intro', null); // This file areas haven't itemid.

        // Return the root element (debate), wrapped into standard activity structure.
        return $this->prepare_activity_structure($debate);

    }
}
