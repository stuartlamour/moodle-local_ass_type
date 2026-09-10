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

namespace local_assess_type;

/**
 * PHPUnit tests for local_assess_type.
 *
 * @package    local_assess_type
 * @copyright  2024 onwards University College London {@link https://www.ucl.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Leon Stringer <leon.stringer@ucl.ac.uk>
 */
final class assess_type_test extends \advanced_testcase {
    /**
     * Test strings returned by assess_type::get_type_name() are the expected
     * values.
     * @covers \local_assess_type\assess_type::get_type_name
     * @dataProvider assess_type_strings
     * @param int $assesstype ASSESS_TYPE_FORMATIVE, ASSESS_TYPE_SUMMATIVE,
     * etc.
     * @param string $name "Formative", "Summative", etc.
     */
    public function test_get_type_name(int $assesstype, string $name): void {
        $this->resetAfterTest(true);

        // Make a course.
        $course = $this->getDataGenerator()->create_course();

        // Make a quiz and add it to the course.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(['course' => $course->id, 'questionsperpage' => 0,
            'grade' => 100.0, 'sumgrades' => 2, 'preferredbehaviour' => 'immediatefeedback']);

        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id);
        assess_type::update_type($course->id, $assesstype, $cm->id);
        $this->assertEquals(assess_type::get_type_name($cm->id), $name);
    }

    /**
     * Test the configured LTI types that may be summative.
     *
     * @covers \local_assess_type\assess_type::canbesummative
     */
    public function test_canbesummative_lti_types(): void {
        $this->resetAfterTest(true);

        set_config('enabled', 1, 'local_assess_type');
        set_config('ltitypes', '42,99', 'local_assess_type');
        config::instance(true);

        $this->assertTrue(assess_type::canbesummative('lti', 42));
        $this->assertTrue(assess_type::canbesummative('lti', 99));
        $this->assertFalse(assess_type::canbesummative('lti', 7));
        $this->assertFalse(assess_type::canbesummative('lti'));
    }

    /**
     * Data provider.
     */
    public static function assess_type_strings(): array {
        return [
            'formative' => [
                'assesstype' => assess_type::ASSESS_TYPE_FORMATIVE,
                'name' => get_string('formative', 'local_assess_type'),
            ],
            'summative' => [
                'assesstype' => assess_type::ASSESS_TYPE_SUMMATIVE,
                'name' => get_string('summative', 'local_assess_type'),
            ],
            'dummy' => [
                'assesstype' => assess_type::ASSESS_TYPE_DUMMY,
                'name' => get_string('dummy', 'local_assess_type'),
            ],
        ];
    }
}
