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
 * Checklist external API class
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use local_booking\local\checklist\entities\checklist;

class update_checklist_item extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'studentid' => new external_value(PARAM_INT, 'Student user ID'),
            'itemid' => new external_value(PARAM_INT, 'Checklist item ID'),
            'state' => new external_value(PARAM_INT, 'Check state (0=undecided, 1=no, 2=yes)'),
        ]);
    }

    public static function execute($courseid, $studentid, $itemid, $state) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'studentid' => $studentid,
            'itemid' => $itemid,
            'state' => $state,
        ]);

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('mod/checklist:updateother', $context);

        require_once(__DIR__ . '/../../lib.php');

        $success = checklist::update_checklist_item(
            $params['itemid'],
            $params['studentid'],
            $USER->id,
            $params['state']
        );

        return [
            'success' => $success,
            'message' => $success ? 'Updated successfully' : 'Update failed',
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Response message'),
        ]);
    }
}
