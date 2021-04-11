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
 * mod_debate data generator test
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * data generator test class for mod_debate
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_debate_generator_testcase extends advanced_testcase {

    /**
     * Test generator
     * @return void
     */
    public function test_generator() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        //create course
        $course = $this->getDataGenerator()->create_course();
        $this->assertFalse($DB->record_exists('debate', array('course' => $course->id)));
        // test create instance
        $debate = $this->getDataGenerator()->create_module('debate', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('debate', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('debate', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('debate', array('id' => $debate->id)));
        //test instance specific data
        $params = array('course' => $course->id,
            'name' => 'Debate generator test',
            'debate'=>'Debate generator test topic',
            'debateresponsecomcount'=> 1);
        $debate = $this->getDataGenerator()->create_module('debate', $params);
        $this->assertEquals(2, $DB->count_records('debate', array('course' => $course->id)));
        $this->assertEquals('Debate generator test',
            $DB->get_field_select('debate', 'name', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals('Debate generator test topic',
            $DB->get_field_select('debate', 'debate', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals(0,
            $DB->get_field_select('debate', 'debateformat', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals(0,
            $DB->get_field_select('debate', 'responsetype', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals('debate description',
            $DB->get_field_select('debate', 'intro', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals(1,
            $DB->get_field_select('debate', 'introformat', 'id = :id', array('id' => $debate->id)));
        $this->assertEquals(1,
            $DB->get_field_select('debate', 'debateresponsecomcount', 'id = :id', array('id' => $debate->id)));
    }

    /**
     * Test add_response
     * @return void
     */
    public function test_add_response() {
        global $DB;
        $this->resetAfterTest();
        //create course
        $course = $this->getDataGenerator()->create_course();
        $this->assertFalse($DB->record_exists('debate', array('course' => $course->id)));
        //create instance
        $debate = $this->getDataGenerator()->create_module('debate', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('debate', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('debate', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('debate', array('id' => $debate->id)));
        //create user
        $user = $this->getDataGenerator()->create_user();
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
        //check the number of responses create successfully
        $params = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'userid' => $user->id
        );
        $this->assertEquals(2, $DB->count_records('debate_response', $params));
    }

    /**
     * Test create_team
     * @return void
     */
    public function test_create_team() {
        global $DB;
        $this->resetAfterTest();

        //create course
        $course = $this->getDataGenerator()->create_course();
        //create instance
        $debate = $this->getDataGenerator()->create_module('debate', array('course' => $course->id));
        //create group
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $debate_groups = $group1->id.','.$group2->id;
        //create team
        $params = array(
            'courseid' => $course->id,
            'debateid' => $debate->id,
            'groupselection' => (string) $debate_groups
        );
        $this->getDataGenerator()->get_plugin_generator('mod_debate')->create_team($params);
        //check data
        $params2 = array(
            'courseid' => $course->id,
            'debateid' => $debate->id
        );
        $this->assertEquals(1, $DB->count_records('debate_teams', $params2));
    }
}

