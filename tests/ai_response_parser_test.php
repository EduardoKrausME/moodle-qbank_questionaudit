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
use invalid_parameter_exception;
use qbank_questionaudit\local\ai_response_parser;

/**
 * Tests for strict AI JSON parsing.
 *
 * @package    qbank_questionaudit
 * @covers     \qbank_questionaudit\local\ai_response_parser
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ai_response_parser_test extends advanced_testcase {
    /**
     * Valid strict JSON is accepted and marked as AI output.
     */
    public function test_valid_json(): void {
        $json = json_encode([
            'summary' => 'The wording may be ambiguous.',
            'findings' => [[
                'severity' => 'warning',
                'category' => 'ambiguity',
                'confidence' => 'medium',
                'evidence' => 'Which option is best?',
                'explanation' => 'The criterion for best is not specified.',
                'suggestion' => 'State the criterion explicitly.',
            ]],
        ]);
        $result = (new ai_response_parser())->parse($json, $this->question());
        $this->assertCount(1, $result['findings']);
        $this->assertSame('ai', $result['findings'][0]['source']);
    }

    /**
     * Invalid AI output is rejected.
     */
    public function test_invalid_json_response(): void {
        $this->expectException(invalid_parameter_exception::class);
        (new ai_response_parser())->parse('```json {"summary":"x"} ```', $this->question());
    }

    /**
     * Extra fields are rejected because the response schema is intentionally exact.
     */
    public function test_extra_json_fields_are_rejected(): void {
        $json = json_encode([
            'summary' => 'No major issue.',
            'findings' => [],
            'unexpected' => true,
        ]);
        $this->expectException(invalid_parameter_exception::class);
        (new ai_response_parser())->parse($json, $this->question());
    }

    /**
     * Empty semantic fields are rejected instead of being shown as a complete finding.
     */
    public function test_empty_explanation_is_rejected(): void {
        $json = json_encode([
            'summary' => 'Finding',
            'findings' => [[
                'severity' => 'warning',
                'category' => 'ambiguity',
                'confidence' => 'high',
                'evidence' => 'Which option is best?',
                'explanation' => '',
                'suggestion' => 'State the criterion.',
            ]],
        ]);
        $this->expectException(invalid_parameter_exception::class);
        (new ai_response_parser())->parse($json, $this->question());
    }

    /**
     * Evidence must be text that actually exists in the question.
     */
    public function test_nonexistent_evidence_is_rejected(): void {
        $json = json_encode([
            'summary' => 'Finding',
            'findings' => [[
                'severity' => 'warning',
                'category' => 'ambiguity',
                'confidence' => 'high',
                'evidence' => 'Text invented by the model',
                'explanation' => 'Explanation',
                'suggestion' => 'Suggestion',
            ]],
        ]);
        $this->expectException(invalid_parameter_exception::class);
        (new ai_response_parser())->parse($json, $this->question());
    }

    /**
     * Base normalized question.
     *
     * @return array
     */
    private function question(): array {
        return [
            'name' => 'Choice quality',
            'questiontext' => 'Which option is best?',
            'generalfeedback' => '',
            'answers' => [
                ['text' => 'Option A', 'feedback' => '', 'fraction' => 1.0],
                ['text' => 'Option B', 'feedback' => '', 'fraction' => 0.0],
            ],
            'matchingpairs' => [],
        ];
    }
}
