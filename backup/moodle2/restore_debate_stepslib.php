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
 * Class for restore
 *
 * @package     mod_debate
 * @copyright   2020 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_debate_activity_structure_step
 */
class restore_debate_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element $structure
     * @throws base_step_exception
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('debate', '/activity/debate');

        if ($userinfo) {
            $paths[] = new restore_path_element('debate_response', '/activity/debate/debate_responses/debate_response');
        }
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a debate restore.
     * @param object $data The data in object form
     * @return void
     */
    protected function process_debate($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
//        $data->timeopen = $this->apply_date_offset($data->timestart);
//        $data->timeclose = $this->apply_date_offset($data->timeend);

        // insert the page record
        $newitemid = $DB->insert_record('debate', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process debate response
     * @param object $data The data in object form
     * @return void
     * @throws dml_exception
     */
    protected function process_debate_response($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->debateid = $this->get_new_parentid('debate');
        $data->courseid = $this->get_courseid();
//        $data->user_id = $this->get_mappingid('user', $data->userid);
//        $data->timestart = $this->apply_date_offset($data->timestart);
//        $data->timeend = $this->apply_date_offset($data->timeend);
//        $data->userid = $this->get_mappingid('userid', $data->userid);
//        var_dump($data);
        $newitemid = $DB->insert_record('debate_response', $data);
        $this->set_mapping('debate_response', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add page related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_debate', 'intro', null);
    }
}
