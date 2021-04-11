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
 * mod_debate lib test
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * lib test class for mod_debate
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_debate_lib_testcase extends advanced_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/debate/lib.php');
    }

    /**
     * Test debate_supports
     * @return void
     */
    public function test_debate_supports() {
        $this->assertTrue(debate_supports(FEATURE_MOD_INTRO) == true);
        $this->assertTrue(debate_supports(FEATURE_COMPLETION_TRACKS_VIEWS) == true);
        $this->assertTrue(debate_supports(FEATURE_BACKUP_MOODLE2) == true);
        $this->assertTrue(debate_supports(FEATURE_SHOW_DESCRIPTION) == true);
    }

    /**
     * Test debate_view
     * @return void
     */
    public function test_debate_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        //test data
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $debate = $this->getDataGenerator()->create_module('debate', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($debate->cmid);
        $cm = get_coursemodule_from_instance('debate', $debate->id);

        //event
        $event_link = $this->redirectEvents();

        $this->setAdminUser();
        debate_view($debate, $course, $cm, $context);

        $events = $event_link->get_events();
        //additional events for completion
        $this->assertCount(3, $events);
        $event = array_shift($events);

        //event checking
        $this->assertInstanceOf('\mod_debate\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/debate/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        //completion checking
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);
    }

    /**
     * Test debate_delete_instance
     * @return void
     */
    public function test_debate_delete_instance(){
        global $DB;
        $this->resetAfterTest();

        //test data
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $debate = $this->getDataGenerator()->create_module('debate',
            array('course' => $course->id, 'debateresponsecomcount'),
            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($debate->cmid);
        $cm = get_coursemodule_from_instance('debate', $debate->id);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        //add some response data
        $params1 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'userid' => $user->id,
            'responsetype' => 1
        );
        $this->getDataGenerator()->get_plugin_generator('mod_debate')->add_response($params1);
        $params2 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'userid' => $user->id,
            'responsetype' => 0
        );
        $this->getDataGenerator()->get_plugin_generator('mod_debate')->add_response($params2);
        //calculate completion
        debate_view($debate, $course, $cm, $context);
        //delete instance
        debate_delete_instance($debate->id);
        //check data
        $this->assertEquals(0, $DB->count_records('debate', array('course' => $course->id)));
        $this->assertFalse($DB->record_exists('debate', array('id' => $debate->id)));
        $params = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'userid' => $user->id
        );
        $this->assertEquals(0, $DB->count_records('debate_response', $params));
    }

    /**
     * Test debate_update_instance
     * @return void
     */
    public function test_debate_update_instance() {
        global $DB;
        $this->resetAfterTest();

        //test data
        $course = $this->getDataGenerator()->create_course();
        $debate = $this->getDataGenerator()->create_module('debate', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('debate', $debate->id);
        $moduleinstance = (object) [
            'coursemodule' => $cm->id,
            'instance' => $debate->id,
            'name' => 'change debate name',
            'debate' => 'change debate topic',
            'debateresponsecomcount' => 2
        ];
        debate_update_instance($moduleinstance);
        $this->assertEquals('change debate name',
            $DB->get_field_select('debate', 'name', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals('change debate topic',
            $DB->get_field_select('debate', 'debate', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals(2,
            $DB->get_field_select('debate', 'debateresponsecomcount', 'id = :id', array('id' => $debate->id)));
    }

    /**
     * Test debate_reset_userdata
     * @return void
     */
    public function test_debate_reset_userdata() {
        global $DB;
        $this->resetAfterTest();

        //create test data
        $course = $this->getDataGenerator()->create_course();
        $debate = $this->getDataGenerator()->create_module('debate', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        //add some response data
        $params1 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'userid' => $user->id,
            'responsetype' => 1
        );
        $this->getDataGenerator()->get_plugin_generator('mod_debate')->add_response($params1);
        $params2 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'userid' => $user->id,
            'responsetype' => 0
        );
        $this->getDataGenerator()->get_plugin_generator('mod_debate')->add_response($params2);
        //create group
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $debate_groups = $group1->id.','.$group2->id;
        //create team
        $params3 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'groupselection' => (string) $debate_groups
        );
        $this->getDataGenerator()->get_plugin_generator('mod_debate')->create_team($params3);
        $data = (object) [
            'reset_debate_attempts' => 1,
            'reset_debate_teams' => 1,
            'courseid' => $course->id
        ];
        debate_reset_userdata($data);
        $params4 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id
        );
        $this->assertEquals(0, $DB->count_records('debate_response', $params4));
        $this->assertEquals(0, $DB->count_records('debate_teams', $params4));
    }
}
