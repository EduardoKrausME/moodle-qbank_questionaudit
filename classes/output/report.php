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

namespace qbank_questionaudit\output;

use qbank_questionaudit\local\access_manager;
use qbank_questionaudit\local\question_repository;
use renderable;
use renderer_base;
use templatable;

/**
 * Audit report renderable.
 *
 * @package    qbank_questionaudit
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report implements renderable, templatable {
    /**
     * Constructor.
     *
     * @param array $results Audit results.
     * @param \moodle_url $returnurl Question bank return URL.
     */
    public function __construct(
        private readonly array $results,
        private readonly \moodle_url $returnurl
    ) {
    }

    /**
     * Export template data.
     *
     * @param renderer_base $output Renderer.
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $questions = [];
        foreach ($this->results as $result) {
            $question = $result['question'];
            $findings = [];
            foreach ($result['findings'] as $finding) {
                $findings[] = [
                    'severity' => get_string('severity_' . $finding['severity'], 'qbank_questionaudit'),
                    'severitykey' => $finding['severity'],
                    'category' => $finding['category'],
                    'confidence' => get_string('confidence_' . $finding['confidence'], 'qbank_questionaudit'),
                    'evidence' => $finding['evidence'] !== ''
                        ? $finding['evidence']
                        : get_string('emptyevidence', 'qbank_questionaudit'),
                    'explanation' => $finding['explanation'],
                    'suggestion' => $finding['suggestion'],
                    'source' => get_string('source_' . $finding['source'], 'qbank_questionaudit'),
                ];
            }

            $editurl = null;
            if (access_manager::can_edit_question((int)$question['id'])) {
                $editparams = [
                    'id' => (int)$question['id'],
                    'returnurl' => $this->returnurl->out_as_local_url(false),
                ];
                if ($cmid = $this->returnurl->param('cmid')) {
                    $editparams['cmid'] = $cmid;
                } else if ($courseid = $this->returnurl->param('courseid')) {
                    $editparams['courseid'] = $courseid;
                } else {
                    $category = (new question_repository())->category_for_question((int)$question['id']);
                    $context = \context::instance_by_id((int)$category->contextid);
                    if ($context->contextlevel === CONTEXT_MODULE) {
                        $editparams['cmid'] = $context->instanceid;
                    } else if ($context->contextlevel === CONTEXT_COURSE) {
                        $editparams['courseid'] = $context->instanceid;
                    } else {
                        $editparams['courseid'] = SITEID;
                    }
                }
                $editurl = new \moodle_url('/question/bank/editquestion/question.php', $editparams);
            }

            $aierror = (string)$result['aierror'];
            $questions[] = [
                'id' => (int)$question['id'],
                'name' => $question['name'] !== '' ? $question['name'] : '#' . $question['id'],
                'qtype' => $question['qtype'],
                'summary' => $result['summary'] !== ''
                    ? $result['summary']
                    : get_string('deterministicsummary', 'qbank_questionaudit', count($findings)),
                'hasfindings' => !empty($findings),
                'findings' => $findings,
                'aierrormessage' => $aierror !== ''
                    ? get_string('bridgeerror', 'qbank_questionaudit', $aierror)
                    : '',
                'editurl' => $editurl ? $editurl->out(false) : '',
            ];
        }

        return [
            'questions' => $questions,
            'returnurl' => $this->returnurl->out(false),
            'ailimitation' => get_string('ailimitation', 'qbank_questionaudit'),
        ];
    }
}
