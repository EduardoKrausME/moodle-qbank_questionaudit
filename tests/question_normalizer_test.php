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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace qbank_questionaudit;

use qbank_questionaudit\local\question_normalizer;

/**
 * Tests for question normalization.
 *
 * @package    qbank_questionaudit
 * @covers     \qbank_questionaudit\local\question_normalizer
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class question_normalizer_test extends \advanced_testcase {
    /**
     * All initially supported qtypes are declared explicitly.
     */
    public function test_supported_types(): void {
        foreach (['multichoice', 'truefalse', 'shortanswer', 'numerical', 'essay', 'match'] as $qtype) {
            $this->assertTrue(question_normalizer::is_supported($qtype), $qtype);
        }
        $this->assertFalse(question_normalizer::is_supported('calculated'));
    }

    /**
     * Core generators for every supported qtype can be loaded and normalized.
     *
     * @dataProvider supported_qtypes_provider
     * @param string $qtype Question type.
     */
    public function test_supported_core_question_can_be_normalized(string $qtype): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $generator->create_question_category();
        $created = $generator->create_question($qtype, null, ['category' => $category->id]);
        $loaded = \question_bank::load_question_data((int)$created->id);

        $normalized = question_normalizer::normalize($loaded);
        $this->assertSame($qtype, $normalized['qtype']);
        $this->assertSame((int)$created->id, $normalized['id']);
    }

    /**
     * Supported qtypes.
     *
     * @return array
     */
    public static function supported_qtypes_provider(): array {
        return [
            'multichoice' => ['multichoice'],
            'truefalse' => ['truefalse'],
            'shortanswer' => ['shortanswer'],
            'numerical' => ['numerical'],
            'essay' => ['essay'],
            'match' => ['match'],
        ];
    }

    /**
     * Rich question data is normalized without leaking Moodle objects into the AI layer.
     */
    public function test_extraction_and_normalization(): void {
        $question = (object)[
            'id' => 42,
            'qtype' => 'multichoice',
            'name' => '  Capital <b>question</b> ',
            'questiontext' => '<p>What is the <strong>capital</strong> of France?</p>',
            'generalfeedback' => '<p>Paris is the capital.</p>',
            'defaultmark' => 1,
            'penalty' => 0.3333333,
            'options' => (object)[
                'single' => 1,
                'shuffleanswers' => 1,
                'answers' => [
                    (object)['id' => 1, 'answer' => '<p>Paris</p>', 'fraction' => 1, 'feedback' => '<p>Correct</p>'],
                    (object)['id' => 2, 'answer' => '<p>Lyon</p>', 'fraction' => 0, 'feedback' => ''],
                ],
            ],
        ];

        $result = question_normalizer::normalize($question);
        $this->assertSame('Capital question', $result['name']);
        $this->assertSame('What is the capital of France?', $result['questiontext']);
        $this->assertCount(2, $result['answers']);
        $this->assertSame('Paris', $result['answers'][0]['text']);
        $this->assertSame(1.0, $result['answers'][0]['fraction']);
        $this->assertSame(1, $result['settings']['single']);
        $this->assertStringContainsString('Paris', question_normalizer::evidence_corpus($result));
    }

    /**
     * Matching subquestions are normalized as pairs.
     */
    public function test_matching_pairs_are_extracted(): void {
        $question = (object)[
            'id' => 7,
            'qtype' => 'match',
            'name' => 'Match capitals',
            'questiontext' => 'Match each country.',
            'options' => (object)[
                'subquestions' => [
                    (object)['id' => 1, 'questiontext' => '<p>France</p>', 'answertext' => '<p>Paris</p>'],
                    (object)['id' => 2, 'questiontext' => '<p>Brazil</p>', 'answertext' => '<p>Brasília</p>'],
                ],
            ],
        ];
        $result = question_normalizer::normalize($question);
        $this->assertCount(2, $result['matchingpairs']);
        $this->assertSame('France', $result['matchingpairs'][0]['question']);
        $this->assertSame('Paris', $result['matchingpairs'][0]['answer']);
    }
}
