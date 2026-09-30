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

namespace qbank_questionaudit\local;

/**
 * Semantic question reviewer backed exclusively by local_ai_bridge.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_reviewer {
    /** Purpose configured in local_ai_bridge. */
    public const PURPOSE = 'questionaudit-review';

    /**
     * Review one normalized question.
     *
     * @param array $question Normalized question.
     * @return array Parsed AI result.
     */
    public function review(array $question): array {
        if (!class_exists('\\local_ai_bridge\\api')) {
            throw new \moodle_exception('bridgeunavailable', 'qbank_questionaudit');
        }

        $payload = $this->ai_payload($question);
        $messages = [[
            'role' => 'user',
            'content' => $this->instruction() . "\n\nQUESTION_DATA:\n" . json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ),
        ]];

        $response = \local_ai_bridge\api::generate(self::PURPOSE, $messages);
        $parser = new ai_response_parser();
        return $parser->parse($response->text, $question);
    }

    /**
     * Build an intentionally explicit prompt because the returned structure is security-sensitive UI input.
     *
     * @return string
     */
    private function instruction(): string {
        return <<<'PROMPT'
You are auditing an existing Moodle question. You are not generating a new question.
Do not claim certainty about pedagogical judgments.

Review only the supplied QUESTION_DATA and look for:
- ambiguous wording or more than one defensible answer;
- wording that reveals the correct answer, implausible distractors, grammatical clues, or unusually different alternative lengths;
- confusing negation or double negation;
- approximate cognitive level and clarity;
- unnecessary bias or missing context;
- incompatibility between the statement and the marked correct answer;
- contradictory feedback;
- possible factual obsolescence.

Return STRICT JSON only, with no Markdown, comments or surrounding text, using exactly this top-level shape:
{
  "summary": "...",
  "findings": [
    {
      "severity": "warning",
      "category": "ambiguity",
      "confidence": "high",
      "evidence": "...",
      "explanation": "...",
      "suggestion": "..."
    }
  ]
}

Rules:
- severity must be one of: error, warning, suggestion, informational.
- confidence must be one of: low, medium, high.
- category must be a short lowercase identifier using letters, numbers, underscore or hyphen.
- Every finding must include evidence copied verbatim from visible textual content in QUESTION_DATA.
- Do not invent evidence and do not use fraction values or internal field names as evidence.
- Treat conclusions as review hypotheses, not absolute truth.
- Do not propose automatic changes and do not rewrite the entire question.
- If there is no defensible semantic finding, return an empty findings array.
PROMPT;
    }

    /**
     * Remove fields not needed for semantic review and cap individual values to keep requests bounded.
     *
     * @param array $question Normalized question.
     * @return array
     */
    private function ai_payload(array $question): array {
        $payload = [
            'qtype' => (string)($question['qtype'] ?? ''),
            'name' => $this->limit((string)($question['name'] ?? '')),
            'questiontext' => $this->limit((string)($question['questiontext'] ?? '')),
            'generalfeedback' => $this->limit((string)($question['generalfeedback'] ?? '')),
            'answers' => [],
            'matchingpairs' => [],
            'settings' => $question['settings'] ?? [],
        ];
        foreach ($question['answers'] ?? [] as $answer) {
            $payload['answers'][] = [
                'text' => $this->limit((string)($answer['text'] ?? '')),
                'fraction' => $answer['fraction'],
                'feedback' => $this->limit((string)($answer['feedback'] ?? '')),
            ];
        }
        foreach ($question['matchingpairs'] ?? [] as $pair) {
            $payload['matchingpairs'][] = [
                'question' => $this->limit((string)($pair['question'] ?? '')),
                'answer' => $this->limit((string)($pair['answer'] ?? '')),
            ];
        }
        return $payload;
    }

    /**
     * Limit a single text field without changing normal short questions.
     *
     * @param string $value Value.
     * @return string
     */
    private function limit(string $value): string {
        if (\core_text::strlen($value) <= 12000) {
            return $value;
        }
        return \core_text::substr($value, 0, 12000) . '…';
    }
}
