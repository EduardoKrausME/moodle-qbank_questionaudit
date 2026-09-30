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

use qbank_questionaudit\local\access_manager;

/**
 * Capability tests.
 *
 * @package    qbank_questionaudit
 * @covers     \qbank_questionaudit\local\access_manager
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class access_manager_test extends \advanced_testcase {
    /**
     * Question view access follows Moodle question capabilities.
     */
    public function test_question_view_capability(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $qgenerator->create_question_category();
        $question = $qgenerator->create_question('shortanswer', null, ['category' => $category->id]);

        $this->assertTrue(access_manager::can_view_question((int)$question->id));

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse(access_manager::can_view_question((int)$question->id));
    }
}
