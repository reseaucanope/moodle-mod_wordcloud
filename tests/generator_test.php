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
 * PHPUnit data generator tests.
 *
 * @package    mod_wordcloud
 * @category   test
 * @copyright  2021 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * PHPUnit data generator testcase.
 *
 * @package    mod_wordcloud
 * @category   test
 * @copyright  2021 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_wordcloud_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB, $SITE, $CFG;

        $CFG->usetags = false; // Disable tag system.

        $this->resetAfterTest(true);

        // Must be a non-guest user to create wordclouds.
        $this->setAdminUser();

        // There are 0 wordclouds initially.
        $this->assertEquals(0, $DB->count_records('wordcloud'));

        // Create the generator object and do standard checks.
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_wordcloud');
        $this->assertInstanceOf('mod_wordcloud_generator', $generator);
        $this->assertEquals('wordcloud', $generator->get_modulename());

        // Create three instances in the site course.
        $generator->create_instance(array('course' => $SITE->id));
        $generator->create_instance(array('course' => $SITE->id));
        $wordcloud = $generator->create_instance(array('course' => $SITE->id));
        $this->assertEquals(3, $DB->count_records('wordcloud'));

        // Check the course-module is correct.
        $cm = get_coursemodule_from_instance('wordcloud', $wordcloud->id);
        $this->assertEquals($wordcloud->id, $cm->instance);
        $this->assertEquals('wordcloud', $cm->modname);
        $this->assertEquals($SITE->id, $cm->course);

        // Check the context is correct.
        $context = context_module::instance($cm->id);
        $this->assertEquals($wordcloud->cmid, $context->instanceid);
    }
}