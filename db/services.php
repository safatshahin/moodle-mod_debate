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
 * webservices for mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'mod_debate_add_debate_respose' => array(
        'classname'   => 'mod_debate\webservice\debate_data',
        'methodname'  => 'add_debate_respose',
        'classpath'   => 'mod/debate/classes/webservice/debate_data.php',
        'description' => 'Add debate response',
        'type'        => 'write',
        'ajax'        => true
    ),
    'mod_debate_find_debate_respose' => array(
        'classname'   => 'mod_debate\webservice\debate_data',
        'methodname'  => 'find_debate_respose',
        'classpath'   => 'mod/debate/classes/webservice/debate_data.php',
        'description' => 'Find debate response',
        'type'        => 'read',
        'ajax'        => true
    ),
    'mod_debate_delete_debate_respose' => array(
        'classname'   => 'mod_debate\webservice\debate_data',
        'methodname'  => 'delete_debate_respose',
        'classpath'   => 'mod/debate/classes/webservice/debate_data.php',
        'description' => 'Delete debate response',
        'type'        => 'write',
        'ajax'        => true
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'mod_debate' => array(
        'functions' => array(
            'mod_debate_add_debate_respose',
            'mod_debate_find_debate_respose',
            'mod_debate_delete_debate_respose'
        ),
        'restrictedusers' => 0,
        'enabled'=>1
    )
);
