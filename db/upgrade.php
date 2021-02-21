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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_debate
 * @category    upgrade
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_debate upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_debate_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2021020802) {
        $table = new xmldb_table('debate');
        $field = new xmldb_field('debateresponsecomcount',
            XMLDB_TYPE_INTEGER,
            '4',
            null,
            true,
            false,
            '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2021022100) {
        $table = new xmldb_table('debate_teams');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false);
        $table->add_field('debateid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false);
        $table->add_field('responsetype', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, false, '0');
        $table->add_field('responseallowed', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, false, '0');
        $table->add_field('userselectiontype', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, false, '0');
        $table->add_field('userselection', XMLDB_TYPE_TEXT, null, null, false, false);
        $table->add_field('userviewtype', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, false, '0');
        $table->add_field('userviewselection', XMLDB_TYPE_TEXT, null, null, false, false);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, '0');

        // Adding keys to table debate_teams.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', 'id');
        $table->add_key('fk_debateid', XMLDB_KEY_FOREIGN, array('debateid'), 'debate', 'id');
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    return true;
}
