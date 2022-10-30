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

require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

function report_booktoportfolio_convert($cm, $modcontext) {
    global $DB;

    $book = report_booktoportfolio_get_book($cm);
    list($bookchapters, $chapternumber) = report_booktoportfolio_get_chapters($book->id);

    $portfolio                  = new stdClass();
    $portfolio->course          = $cm->course;
    $portfolio->name            = $book->name;
    $portfolio->intro           = $book->intro;
    $portfolio->introformat     = 1;
    $portfolio->intronumbering  = 1;
    $portfolio->revision        = 1;
    $portfolio->timecreated     = $book->timecreated;
    $portfolio->timemodified    = $book->timemodified;
    $portfolio->chapternumber   = $chapternumber;

    // Insert giportfolio.
    $newportfolioid     = $DB->insert_record('giportfolio', $portfolio, true, true);
    $chapteridsmap   = []; //
    // Insert chapters to portfolio.
    foreach ($bookchapters as $chapter) {
        $frombook               = new stdClass();
        $frombook->bookid       = $chapter->bookid;
        $chapter->importsrc     = json_encode($frombook);
        $chapter->giportfolioid = $newportfolioid;
        $bookchapterid          = $chapter->id;
        unset($chapter->bookid);
        unset($chapter->id);

        $chapteridsmap[$bookchapterid] = $DB->insert_record('giportfolio_chapters', $chapter, true, true);
    }

    // We need to create the entry to the course_module table.
    list($cmid, $section) = report_booktoportfolio_set_new_portfolio_course_module($cm, $book->id, $newportfolioid);

    // Copy images.
    report_booktoportfolio_get_images($modcontext, $chapteridsmap, $cm->course, $newportfolioid, $cmid);

    // Add the portfolio to the section.
    course_add_cm_to_section($cm->course, $cmid, $section, $cm->id);

    redirect(course_get_url($cm->course, $cm->sectionnum, array('sr' => $section)));
}

function report_booktoportfolio_set_new_portfolio_course_module($cm, $bookid, $portfolioinstance) {
    global $DB;

    $sql = "SELECT *
            FROM mdl_course_modules  cm
            WHERE cm.id = ? AND cm.course = ? AND cm.instance = ?";

    $params   = ['id' => $cm->id, "course" => $cm->course, "instance" => $bookid];
    $cmrecord = $DB->get_record_sql($sql, $params);

    $portfoliomoduleid  = report_booktoportfolio_get_portfolio_module_id();
    $cmrecord->module   = $portfoliomoduleid;
    $cmrecord->instance = $portfolioinstance;
    $cmid               = $DB->insert_record('course_modules', $cmrecord, true, true);
    $section            = report_booktoportfolio_set_new_portfolio_module_section($cmrecord->section, $cm->course);

    return [$cmid, $section];

}

function report_booktoportfolio_set_new_portfolio_module_section($sectionid, $courseid) {
    global $DB;

    $sql     = "SELECT section FROM mdl_course_sections where id = ? AND course = ?";
    $params  = ['id' => $sectionid, 'course' => $courseid];
    $section = $DB->get_record_sql($sql, $params);
    return $section->section;
}

function report_booktoportfolio_get_portfolio_module_id() {
    global $DB;

    $sql    = "SELECT id FROM mdl_modules WHERE name like '" . 'giportfolio' . "'";
    $module = $DB->get_record_sql($sql);

    return $module->id;
}


function report_booktoportfolio_get_book($cm) {
    global $DB;
    $sql    = "SELECT book.*
                FROM mdl_course_modules  cm
                JOIN mdl_book book ON cm.instance = book.id
                WHERE cm.id = ? AND cm.course = ?";

    $params = ['id' => $cm->id, "course" => $cm->course];
    $book   = $DB->get_record_sql($sql, $params);

    return $book;

}

function report_booktoportfolio_get_chapters($bookid) {
    global $DB;

    $sql      = "SELECT * FROM mdl_book_chapters where bookid = ?";
    $params   = ['bookid' => $bookid];
    $chapters = $DB->get_records_sql($sql, $params);
    $subchapters = 0;
    foreach ($chapters as $chapter) {
        if ($chapter->subchapter) {
            $subchapters++;
        }
    }
    $numberofchapters = count($chapters) - $subchapters;
    return [$chapters, $numberofchapters];

}

/**
 * @context : book context
 */
function report_booktoportfolio_get_images($context, $chapteridsmap, $courseid, $newportfolioid, $porfoliocmid) {
    $fs = get_file_storage();
    $portfoliocontext = context_module::instance($porfoliocmid);
    // Get the intro files first.
    if ($files = $fs->get_area_files($context->id, 'mod_book', 'intro', 0, true)) {

        foreach ($files as $file) {
            $newfile = new stdClass();
            $newfile->contextid = $portfoliocontext->id;
            $newfile->component = 'mod_giportfolio';
            $newfile->filearea = 'intro';
            $fs->create_file_from_storedfile($newfile, $file);
        }
    }

    foreach ($chapteridsmap as $bookchapterid => $portfoliochapterid) {
        if ($files = $fs->get_area_files($context->id, 'mod_book', 'chapter', $bookchapterid, "filename", true)) {
            foreach ($files as $file) {
                $newrecord = new \stdClass();
                $newrecord->itemid = $portfoliochapterid;
                $newrecord->component = 'mod_giportfolio';
                $newrecord->contextid = $portfoliocontext->id;
                $newrecord->filearea = 'chapter';
                $fs->create_file_from_storedfile($newrecord, $file);
            }
        }

    }

}




