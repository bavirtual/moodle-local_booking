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
 * Class for displaying checklists to be signed-off.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (mustafa.hajjar)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use renderer_base;
use moodle_url;

/**
 * Exporter for checklist grading interface
 */
class checklist_grading_exporter extends exporter {

    protected static function define_properties() {
        return [
            'courseid' => ['type' => PARAM_INT],
            'studentid' => ['type' => PARAM_INT],
            'bookingid' => ['type' => PARAM_INT],
            'hasstudent' => ['type' => PARAM_BOOL],
            'isinstructor' => ['type' => PARAM_BOOL],
        ];
    }

    protected static function define_related() {
        return [
            'context' => 'context',
            'subscriber' => 'local_booking\local\subscriber\entities\subscriber',
            'student' => 'local_booking\local\participant\entities\student',
            'checklists' => 'object[]', // Array of objects containing checklist and its items
        ];
    }

    protected static function define_other_properties() {
        return [
            'checklists' => [
                'type' => [
                    'id' => ['type' => PARAM_INT],
                    'cmid' => ['type' => PARAM_INT],
                    'name' => ['type' => PARAM_TEXT],
                    'intro' => ['type' => PARAM_RAW],
                    'viewurl' => ['type' => PARAM_URL],
                    'itemcount' => ['type' => PARAM_INT],
                    'checkedcount' => ['type' => PARAM_INT],
                    'progress' => ['type' => PARAM_INT],
                    'items' => [
                        'type' => [
                            'id' => ['type' => PARAM_INT],
                            'displaytext' => ['type' => PARAM_RAW],
                            'indent' => ['type' => PARAM_INT],
                            'isheading' => ['type' => PARAM_BOOL],
                            'teacherchecked' => ['type' => PARAM_INT], // 0=undecided, 1=yes, 2=no
                            'teachertimestamp' => ['type' => PARAM_TEXT],
                            'optional' => ['type' => PARAM_BOOL],
                            'hidden' => ['type' => PARAM_BOOL],
                            'checkid' => ['type' => PARAM_INT],
                        ],
                        'multiple' => true,
                    ],
                ],
                'multiple' => true,
            ],
            'showstudentselector' => ['type' => PARAM_BOOL],
            'students' => [
                'type' => [
                    'id' => ['type' => PARAM_INT],
                    'fullname' => ['type' => PARAM_TEXT],
                    'picture' => ['type' => PARAM_RAW],
                    'selected' => ['type' => PARAM_BOOL],
                ],
                'multiple' => true,
                'optional' => true,
            ],
        ];
    }

    protected function get_other_values(renderer_base $output) {
        global $DB;

        $checklistsdata = $this->related['checklists'];

        $data = [
            'checklists' => [],
            'showstudentselector' => !$this->data['hasstudent'],
        ];

        // Format checklists
        foreach ($checklistsdata as $checklist) {
            $items = $checklist->items;

            $checkedcount = 0;
            $itemcount = 0;
            $formattedItems = [];

            foreach ($items as $item) {
                // Skip hidden items for students
                if ($item->hidden) {
                    continue;
                }

                $isheading = ($item->itemoptional == CHECKLIST_OPTIONAL_HEADING);

                if (!$isheading) {
                    $itemcount++;
                    if ($item->teachercheck == CHECKLIST_TEACHERMARK_YES) {
                        $checkedcount++;
                    }
                }

                $formattedItems[] = [
                    'id' => $item->id,
                    'displaytext' => format_text($item->displaytext, FORMAT_HTML),
                    'indent' => $item->indent,
                    'isheading' => $isheading,
                    'teacherchecked' => $item->teachercheck, // 0=no, 1=yes
                    'teachertimestamp' => $item->teachertimestamp ?
                        userdate($item->teachertimestamp, '%d %b %Y, %H:%M') : '',
                    'optional' => ($item->itemoptional == CHECKLIST_OPTIONAL_YES),
                    'hidden' => !empty($item->hidden),
                    'checkid' => $item->checkid ?? 0,
                ];
            }

            $progress = $itemcount > 0 ? round(($checkedcount / $itemcount) * 100) : 0;

            $data['checklists'][] = [
                'id' => $checklist->id,
                'cmid' => $checklist->cmid,
                'name' => format_string($checklist->name),
                'intro' => format_module_intro('checklist', $checklist, $checklist->cmid),
                'viewurl' => (new moodle_url('/mod/checklist/view.php', [
                    'id' => $checklist->cmid
                ]))->out(false),
                'itemcount' => $itemcount,
                'checkedcount' => $checkedcount,
                'progress' => $progress,
                'items' => $formattedItems,
            ];
        }

        // Add students list if no student selected
        if (!$this->data['hasstudent']) {
            $data['students'] = $this->get_course_students();
        }

        return $data;
    }

    /**
     * Get list of students in course
     */
    private function get_course_students() {
        global $OUTPUT;

        $students = $this->related['subscriber']->get_students();

        $result = [];
        foreach ($students as $student) {
            $result[] = [
                'id' => $student->id,
                'fullname' => fullname($student),
                'picture' => $OUTPUT->user_picture($student, [
                    'size' => 50,
                    'class' => 'studentpicture'
                ]),
                'selected' => false,
            ];
        }

        return $result;
    }
}
