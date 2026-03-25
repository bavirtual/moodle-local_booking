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
 * Session Booking Plugin
 * Class for displaying students progression and instructor active bookings.
 *
 * @package    local_booking
 * @author     Mustafa Hajjar (captainmoose)
 * @copyright  BAVirtual.co.uk © 2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use renderer_base;

/**
 * Exporter for booking student statistics dashboard
 *
 * @package    local_booking
 * @copyright  2024 BAVirtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_student_stats_exporter extends exporter {

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'bookingid' => [
                'type' => PARAM_INT,
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
            'studentid' => [
                'type' => PARAM_INT,
            ],
            'lastupdated' => [
                'type' => PARAM_TEXT,
            ],
        ];
    }

    /**
     * Returns a list of objects that are related.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'context' => 'context',
            'student' => 'stdClass',
            'booking' => 'stdClass',
            'sessions' => 'stdClass[]',
            'competencies' => 'stdClass[]',
            'signoffs' => 'stdClass',
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'student' => [
                'type' => [
                    'fullname' => ['type' => PARAM_TEXT],
                    'vatsimcid' => ['type' => PARAM_TEXT],
                    'stage' => ['type' => PARAM_TEXT],
                    'sectors' => ['type' => PARAM_TEXT],
                    'instructorname' => ['type' => PARAM_TEXT],
                    'instructorcallsign' => ['type' => PARAM_TEXT],
                    'status' => ['type' => PARAM_TEXT],
                    'uptodate' => ['type' => PARAM_BOOL],
                    'haspending' => ['type' => PARAM_BOOL],
                    'lastsessiondate' => ['type' => PARAM_TEXT],
                ],
            ],
            'kpis' => [
                'type' => [
                    'sessions' => ['type' => PARAM_INT],
                    'sectors' => ['type' => PARAM_INT],
                    'totalflight' => ['type' => PARAM_TEXT],
                    'pftime' => ['type' => PARAM_TEXT],
                    'pmtime' => ['type' => PARAM_TEXT],
                    'studentlandings' => ['type' => PARAM_INT],
                    'instructorlandings' => ['type' => PARAM_INT],
                ],
            ],
            'competencies' => [
                'type' => [
                    'name' => ['type' => PARAM_TEXT],
                    'avggrade' => ['type' => PARAM_INT],
                    'lastgrade' => ['type' => PARAM_INT],
                    'trendtext' => ['type' => PARAM_TEXT],
                    'isimproving' => ['type' => PARAM_BOOL],
                    'isflat' => ['type' => PARAM_BOOL],
                    'isworsening' => ['type' => PARAM_BOOL],
                    'notes' => ['type' => PARAM_TEXT],
                ],
                'multiple' => true,
            ],
            'signoffs' => [
                'type' => [
                    'discussionoutstanding' => ['type' => PARAM_INT],
                    'routeoutstanding' => ['type' => PARAM_INT],
                    'discussionitems' => [
                        'type' => [
                            'title' => ['type' => PARAM_TEXT],
                            'statuslabel' => ['type' => PARAM_TEXT],
                            'dateformatted' => ['type' => PARAM_TEXT],
                            'comment' => ['type' => PARAM_TEXT],
                            'statusclass' => ['type' => PARAM_TEXT],
                            'statustext' => ['type' => PARAM_TEXT],
                        ],
                        'multiple' => true,
                    ],
                    'routeitems' => [
                        'type' => [
                            'title' => ['type' => PARAM_TEXT],
                            'statuslabel' => ['type' => PARAM_TEXT],
                            'dateformatted' => ['type' => PARAM_TEXT],
                            'comment' => ['type' => PARAM_TEXT],
                            'statusclass' => ['type' => PARAM_TEXT],
                            'statustext' => ['type' => PARAM_TEXT],
                        ],
                        'multiple' => true,
                    ],
                ],
            ],
            'sessions' => [
                'type' => [
                    'number' => ['type' => PARAM_INT],
                    'date' => ['type' => PARAM_TEXT],
                    'instructorcallsign' => ['type' => PARAM_TEXT],
                    'instructorname' => ['type' => PARAM_TEXT],
                    'route' => ['type' => PARAM_TEXT],
                    'keynotes' => ['type' => PARAM_TEXT],
                    'totaltime' => ['type' => PARAM_TEXT],
                    'overallgrade' => ['type' => PARAM_INT],
                    'gradetext' => ['type' => PARAM_TEXT],
                    'sectornumbers' => ['type' => PARAM_TEXT],
                    'islatest' => ['type' => PARAM_BOOL],
                    'pftime' => ['type' => PARAM_TEXT],
                    'pmtime' => ['type' => PARAM_TEXT],
                    'totalstudentlandings' => ['type' => PARAM_INT],
                    'totalinstructorlandings' => ['type' => PARAM_INT],
                    'hassignoffs' => ['type' => PARAM_BOOL],
                    'discussioncount' => ['type' => PARAM_INT],
                    'routecount' => ['type' => PARAM_INT],
                    'sectors' => [
                        'type' => [
                            'number' => ['type' => PARAM_INT],
                            'departure' => ['type' => PARAM_TEXT],
                            'destination' => ['type' => PARAM_TEXT],
                            'aircraft' => ['type' => PARAM_TEXT],
                            'flighttime' => ['type' => PARAM_TEXT],
                            'role' => ['type' => PARAM_TEXT],
                            'studentlandings' => ['type' => PARAM_INT],
                            'instructorlandings' => ['type' => PARAM_INT],
                        ],
                        'multiple' => true,
                    ],
                    'discussionitems' => [
                        'type' => [
                            'title' => ['type' => PARAM_TEXT],
                            'status' => ['type' => PARAM_TEXT],
                            'comment' => ['type' => PARAM_TEXT],
                            'statusclass' => ['type' => PARAM_TEXT],
                            'statustext' => ['type' => PARAM_TEXT],
                        ],
                        'multiple' => true,
                        'optional' => true,
                    ],
                    'routeitems' => [
                        'type' => [
                            'title' => ['type' => PARAM_TEXT],
                            'status' => ['type' => PARAM_TEXT],
                            'comment' => ['type' => PARAM_TEXT],
                            'statusclass' => ['type' => PARAM_TEXT],
                            'statustext' => ['type' => PARAM_TEXT],
                        ],
                        'multiple' => true,
                        'optional' => true,
                    ],
                ],
                'multiple' => true,
            ],
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output
     * @return array
     */
    protected function get_other_values(renderer_base $output) {
        $student = $this->related['student'];
        $sessions = $this->related['sessions'];
        $competencies = $this->related['competencies'];
        $signoffs = $this->related['signoffs'];

        // Format student data
        $studentdata = [
            'fullname' => fullname($student),
            'vatsimcid' => $student->vatsimcid ?? 'N/A',
            'stage' => 'Line Training',
            'sectors' => '5–6',
            'instructorname' => $student->instructorname ?? 'N/A',
            'instructorcallsign' => $student->instructorcallsign ?? 'N/A',
            'status' => $student->status ?? 'Active',
            'uptodate' => !empty($student->uptodate),
            'haspending' => !empty($student->haspending),
            'lastsessiondate' => !empty($student->lastsession) ?
                userdate($student->lastsession, '%d %b %Y') : 'N/A',
        ];

        // Calculate KPIs
        $kpis = $this->calculate_kpis($sessions);

        // Format competencies
        $competenciesdata = $this->format_competencies($competencies);

        // Format sign-offs
        $signoffsdata = $this->format_signoffs($signoffs);

        // Format sessions
        $sessionsdata = $this->format_sessions($sessions);

        return [
            'student' => $studentdata,
            'kpis' => $kpis,
            'competencies' => $competenciesdata,
            'signoffs' => $signoffsdata,
            'sessions' => $sessionsdata,
        ];
    }

    /**
     * Calculate KPIs from sessions
     *
     * @param array $sessions
     * @return array
     */
    private function calculate_kpis($sessions) {
        // Implement your KPI calculation logic here
        return [
            'sessions' => count($sessions),
            'sectors' => 10, // Calculate from sessions
            'totalflight' => '14:22',
            'pftime' => '7:55',
            'pmtime' => '6:27',
            'studentlandings' => 6,
            'instructorlandings' => 2,
        ];
    }

    /**
     * Format competencies data
     *
     * @param array $competencies
     * @return array
     */
    private function format_competencies($competencies) {
        $formatted = [];
        foreach ($competencies as $comp) {
            $trend = $this->calculate_trend($comp->avggrade, $comp->lastgrade);
            $formatted[] = [
                'name' => $comp->name,
                'avggrade' => $comp->avggrade,
                'lastgrade' => $comp->lastgrade,
                'trendtext' => $trend['text'],
                'isimproving' => $trend['improving'],
                'isflat' => $trend['flat'],
                'isworsening' => $trend['worsening'],
                'notes' => $comp->notes,
            ];
        }
        return $formatted;
    }

    /**
     * Calculate trend between grades
     *
     * @param int $avg
     * @param int $last
     * @return array
     */
    private function calculate_trend($avg, $last) {
        // Lower grades are better (1 is best, 5 is worst)
        if ($last < $avg) {
            return ['text' => 'Improving', 'improving' => true, 'flat' => false, 'worsening' => false];
        } else if ($last > $avg) {
            return ['text' => 'Worsening', 'improving' => false, 'flat' => false, 'worsening' => true];
        } else {
            return ['text' => 'Stable', 'improving' => false, 'flat' => true, 'worsening' => false];
        }
    }

    /**
     * Format sign-offs data
     *
     * @param stdClass $signoffs
     * @return array
     */
    private function format_signoffs($signoffs) {
        // Implement your sign-offs formatting logic
        return [
            'discussionoutstanding' => $signoffs->discussionoutstanding ?? 0,
            'routeoutstanding' => $signoffs->routeoutstanding ?? 0,
            'discussionitems' => $signoffs->discussionitems ?? [],
            'routeitems' => $signoffs->routeitems ?? [],
        ];
    }

    /**
     * Format sessions data
     *
     * @param array $sessions
     * @return array
     */
    private function format_sessions($sessions) {
        $formatted = [];
        $count = count($sessions);

        foreach ($sessions as $index => $session) {
            $formatted[] = [
                'number' => $session->number,
                'date' => userdate($session->timestart, '%d %b %Y'),
                'instructorcallsign' => $session->instructorcallsign,
                'instructorname' => $session->instructorname,
                'route' => $session->route,
                'keynotes' => $session->notes,
                'totaltime' => $session->totaltime,
                'overallgrade' => $session->overallgrade,
                'gradetext' => $this->get_grade_text($session->overallgrade),
                'sectornumbers' => $session->sectornumbers,
                'islatest' => ($index === 0), // First is latest
                'pftime' => $session->pftime,
                'pmtime' => $session->pmtime,
                'totalstudentlandings' => $session->studentlandings,
                'totalinstructorlandings' => $session->instructorlandings,
                'hassignoffs' => !empty($session->signoffs),
                'discussioncount' => count($session->discussionitems ?? []),
                'routecount' => count($session->routeitems ?? []),
                'sectors' => $session->sectors ?? [],
                'discussionitems' => $session->discussionitems ?? [],
                'routeitems' => $session->routeitems ?? [],
            ];
        }

        return $formatted;
    }

    /**
     * Get grade text from numeric grade
     *
     * @param int $grade
     * @return string
     */
    private function get_grade_text($grade) {
        $grades = [
            1 => 'Excellent',
            2 => 'Good',
            3 => 'At standard',
            4 => 'Below standard',
            5 => 'Unsatisfactory',
        ];
        return $grades[$grade] ?? 'Unknown';
    }
}
