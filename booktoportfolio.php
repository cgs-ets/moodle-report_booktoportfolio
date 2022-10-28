<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    report_booktoportfolio
 * @copyright  2022 Veronica Bermegui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../config.php');
require_once($CFG->dirroot.'/report/booktoportfolio/locallib.php');

$id  = required_param('id', PARAM_INT);  // Course id.
$modid = required_param('modid', PARAM_INT); // Module id.

$params = ['id' => $id, 'modid' => $modid];

$PAGE->set_url('/report/booktoportfolio/index.php', $params);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$modinfo = get_fast_modinfo($course->id);
$cm = $modinfo->get_cm($modid);

$modcontext = context_module::instance($cm->id);

require_capability('mod/giportfolio:gradegiportfolios', $modcontext);


$data = report_booktoportfolio_convert($cm, $modcontext);
