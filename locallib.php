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
 * @package     mod_wordcloud
 * @copyright   2021 Reseau-Canope
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/accesslib.php');

/**
 * Class wordcloud_index.
 *
 *
 * @package     mod_wordcloud
 * @copyright   2021 Reseau-Canope
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wordcloud_index {
    private $context;
    private $course;
    private $output;

    public function __construct($context, $course){
        $this->context = $context;
        $this->course = $course;
    }

    public function view($action = '') {
        if ($action == 'viewcourseindex') {
            return $this->view_course_index();
        }
        return '';
    }

    public function get_course() {
        global $DB;

        if ($this->course && is_object($this->course)) {
            return $this->course;
        }

        if (!$this->context) {
            return null;
        }
        $params = array('id' => $this->get_course_context()->instanceid);
        $this->course = $DB->get_record('course', $params, '*', MUST_EXIST);

        return $this->course;
    }

    public function get_course_context() {
        if (!$this->context && !$this->course) {
            throw new coding_exception('Improper use of the wordcloud class. ' .
                'Cannot load the course context.');
        }
        if ($this->context) {
            return $this->context->get_course_context();
        } else {
            return context_course::instance($this->course->id);
        }
    }

    public function get_renderer() {
        global $PAGE;
        if ($this->output) {
            return $this->output;
        }
        $this->output = $PAGE->get_renderer('mod_wordcloud', null, RENDERER_TARGET_GENERAL);
        return $this->output;
    }

    private function view_course_index(){

        $course = $this->get_course();

        $strsectionname = '';
        $usesections = course_format_uses_sections($course->format);
        $modinfo = get_fast_modinfo($course);

        if ($usesections) {
            $strsectionname = get_string('sectionname', 'format_'.$course->format);
            $sections = $modinfo->get_section_info_all();
        }

        $courseindexsummary = new wordcloud_course_index_summary($usesections, $strsectionname);

        foreach ($modinfo->instances['wordcloud'] as $cm) {
            if (!$cm->uservisible) {
                continue;
            }

            $sectionname = '';
            if ($usesections && $cm->sectionnum) {
                $sectionname = get_section_name($course, $sections[$cm->sectionnum]);
            }

            $courseindexsummary->add_wordcloud_info($cm->id, $cm->get_formatted_name(), $sectionname);
        }

        return $this->get_renderer()->render($courseindexsummary);
    }
}

/**
 * Class wordcloud_course_index_summary
 */
class wordcloud_course_index_summary implements renderable {
    public $wordclouds = array();
    public $usesections = false;
    public $courseformatname = '';

    public function __construct($usesections, $courseformatname) {
        $this->usesections = $usesections;
        $this->courseformatname = $courseformatname;
    }

    public function add_wordcloud_info($cmid, $cmname, $sectionname) {
        $this->wordclouds[] = array('cmid'=>$cmid,
            'cmname' => $cmname,
            'sectionname' => $sectionname
        );
    }
}