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
 * Manage teams table of mod_debate.
 *
 * @package     mod_debate
 * @copyright   2021 Safat Shahin <safatshahin@yahoo.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debate\output\tables;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/tablelib.php');

use DateTime;
use moodle_url;
use table_sql;

/**
 * Class debate_teams_table.
 *
 * An extension of your regular Moodle table.
 */
class debate_teams_table extends table_sql {

    /**
     * Search string.
     *
     * @var string
     */
    public $search = '';

    /**
     * Course module id.
     *
     * @var int
     */
    public $cmid = 0;

    /**
     * debate_teams_table constructor.
     * Sets the SQL for the table and the pagination.
     * @param string $uniqueid
     * @param int $response
     * @param int $debateid
     * @param int $cmid
     */
    public function __construct(string $uniqueid, int $response, int $debateid, int $cmid) {
        global $PAGE;
        $this->cmid = $cmid;
        parent::__construct($uniqueid);

        $columns = array('id', 'name', 'active', 'timemodified', 'actions');
        $headers = array(
            get_string('id', 'mod_debate'),
            get_string('name', 'mod_debate'),
            get_string('status', 'mod_debate'),
            get_string('timemodified', 'mod_debate'),
            get_string('actions', 'mod_debate')
        );
        $this->no_sorting('actions');
        $this->no_sorting('samplerequest');
        $this->is_collapsible = false;
        $this->define_columns($columns);
        $this->define_headers($headers);
        $fields = "id,
        name,
        active,
        responsetype,
        timemodified,
        '' AS actions";
        $from = "{debate_teams}";
        $where = 'id > 0 AND responsetype='.$response.' AND debateid='.$debateid;
        $params = array();
        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(id) FROM " . $from . " WHERE " . $where, $params);
        $this->define_baseurl($PAGE->url);
    }

    /**
     * Name column.
     *
     * @param \stdClass $values
     * @return string
     */
    public function col_name(\stdClass $values): string {
        $urlparams = array('id' => $values->id, 'cmid' => $this->cmid,
                'response' => $values->responsetype, 'sesskey' => sesskey());
        $editurl = new moodle_url('/mod/debate/debate_teams_form_page.php', $urlparams);
        return \html_writer::tag('a', $values->name, array('href' => $editurl));
    }

    /**
     * Active/inactive column.
     *
     * @param \stdClass $values
     * @return string
     */
    public function col_active(\stdClass $values): string {
        $status = get_string('active', 'mod_debate');
        $css = 'text-success';
        if (!$values->active) {
            $status = get_string('inactive', 'mod_debate');
            $css = 'text-danger';
        }
        $icon = \html_writer::tag('i', '', array('class' => 'fa fa-circle'));
        return \html_writer::tag('div', $icon . ' ' . $status, array('class' => $css));
    }

    /**
     * convert invalid to '-'.
     *
     * @param \stdClass $values
     * @return string
     */
    public function col_timemodified(\stdClass $values): string {
        if (!empty($values->timemodified)) {
            $dt = new DateTime("@$values->timemodified");  // Convert UNIX timestamp to PHP DateTime.
            $result = $dt->format('d/m/Y H:i:s'); // Output = 2017-01-01 00:00:00.
        } else {
            $result = '-';
        }
        return $result;
    }

    /**
     * Action column.
     *
     * @param \stdClass $values
     * @return string Renderer template
     */
    public function col_actions(\stdClass $values): string {
        global $PAGE;

        $urlparams = array('id' => $values->id, 'response' => $values->responsetype, 'cmid' => $this->cmid, 'sesskey' => sesskey());
        $editurl = new moodle_url('/mod/debate/debate_teams_form_page.php', $urlparams);
        $deleteurl = new moodle_url('/mod/debate/debate_teams_page.php', $urlparams + array('action' => 'delete'));
        // Decide to activate or deactivate.
        if ($values->active) {
            $toggleurl = new moodle_url('/mod/debate/debate_teams_page.php', $urlparams + array('action' => 'hide'));
            $togglename = get_string('inactive', 'mod_debate');
            $toggleicon = 'fa fa-eye';
        } else {
            $toggleurl = new moodle_url('/mod/debate/debate_teams_page.php', $urlparams + array('action' => 'show'));
            $togglename = get_string('active', 'mod_debate');
            $toggleicon = 'fa fa-eye-slash';
        }

        $params = array(
            'id' => $values->id,
            'buttons' => array(
                array(
                    'name' => get_string('edit'),
                    'icon' => 'fa fa-edit',
                    'url' => $editurl
                ),
                array(
                    'name' => get_string('delete'),
                    'icon' => 'fa fa-trash',
                    'url' => $deleteurl
                ),
                array(
                    'name' => $togglename,
                    'icon' => $toggleicon,
                    'url' => $toggleurl
                )
            )
        );

        return $PAGE->get_renderer('mod_debate')->render_action_buttons($params);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return string
     */
    public function export_for_template(): string {
        ob_start();
        $this->out(20, true);
        $tablehtml = ob_get_contents();
        ob_end_clean();
        return $tablehtml;
    }

}

