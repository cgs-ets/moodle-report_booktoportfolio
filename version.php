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
 * Version details.
 *
 * @package    report
 * @subpackage booktoportfolio
 * @copyright  2022 Veronica Bermegui
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2022112600;       // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2014111100;       // Requires this Moodle version.
$plugin->component = 'report_booktoportfolio'; // Full name of the plugin (used for diagnostics).
$plugin->dependencies = array(
    'mod_book' => ANY_VERSION,   // The assign activity must be present (any version).
    'mod_giportfolio' => ANY_VERSION
);
