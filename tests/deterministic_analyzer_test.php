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

namespace qbank_questionaudit;

use advanced_testcase;
use qbank_questionaudit\local\deterministic_analyzer;

/**
 * Tests for deterministic checks.
 *
 * @package    qbank_questionaudit
 * @covers     \qbank_questionaudit\local\deterministic_analyzer
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class deterministic_analyzer_test extends advanced_testcase {
    /**
     * Empty content must be caught before any semantic review.
     */
    public function test_question_without_content(): void {
        $question = $this->base_question();
        $question['questiontext'] = '';
        $question['questiontext_html'] = '';
        $findings = (new deterministic_analyzer())->analyze($question);
        $this->assertContains('empty_questiontext', array_column($findings, 'category'));
    }

    /**
     * Duplicate alternatives, missing correct answer and bad fractions are local findings.
     */
    public function test_answer_rules(): void {
        $question = $this->base_question();
        $question['answers'] = [
            ['text' => 'Same', 'html' => 'Same', 'fraction' => 0.0, 'feedback' => '', 'feedback_html' => ''],
            ['text' => 'Same', 'html' => 'Same', 'fraction' => 0.0, 'feedback' => '', 'feedback_html' => ''],
        ];
        $findings = (new deterministic_analyzer())->analyze($question);
        $categories = array_column($findings, 'category');
        $this->assertContains('duplicate_answer', $categories);
        $this->assertContains('missing_correct_answer', $categories);
    }

    /**
     * Multiple-answer positive fractions should normally add to one.
     */
    public function test_multichoice_fraction_sum(): void {
        $question = $this->base_question();
        $question['settings']['single'] = 0;
        $question['answers'] = [
            ['text' => 'A', 'html' => 'A', 'fraction' => 0.7, 'feedback' => '', 'feedback_html' => ''],
            ['text' => 'B', 'html' => 'B', 'fraction' => 0.7, 'feedback' => '', 'feedback_html' => ''],
            ['text' => 'C', 'html' => 'C', 'fraction' => -0.2, 'feedback' => '', 'feedback_html' => ''],
        ];
        $findings = (new deterministic_analyzer())->analyze($question);
        $this->assertContains('fraction_sum', array_column($findings, 'category'));
    }

    /**
     * A broken HTML nesting pattern is detected conservatively.
     */
    public function test_simple_invalid_html(): void {
        $question = $this->base_question();
        $question['questiontext_html'] = '<p><strong>Broken</p></strong>';
        $findings = (new deterministic_analyzer())->analyze($question);
        $this->assertContains('html_structure', array_column($findings, 'category'));
    }

    /**
     * Build a valid base multiple-choice normalized question.
     *
     * @return array
     */
    private function base_question(): array {
        return [
            'id' => 1,
            'qtype' => 'multichoice',
            'name' => 'Meaningful question name',
            'questiontext' => 'Which option is correct?',
            'questiontext_html' => '<p>Which option is correct?</p>',
            'generalfeedback' => 'Review the concept.',
            'generalfeedback_html' => '<p>Review the concept.</p>',
            'answers' => [
                ['text' => 'Correct', 'html' => 'Correct', 'fraction' => 1.0, 'feedback' => '', 'feedback_html' => ''],
                ['text' => 'Wrong', 'html' => 'Wrong', 'fraction' => 0.0, 'feedback' => '', 'feedback_html' => ''],
            ],
            'matchingpairs' => [],
            'settings' => ['single' => 1],
        ];
    }
}
