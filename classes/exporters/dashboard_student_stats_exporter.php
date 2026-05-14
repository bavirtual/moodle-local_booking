<?php
// This file is part of Moodle - http://moodle.org/

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use renderer_base;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\participant\entities\student;

/**
 * Exporter for booking student statistics dashboard
 *
 * @package    local_booking
 * @copyright  2026 BAVirtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_student_stats_exporter extends exporter {

    /**
     * @var subscriber $course The subscribing course.
     */
    protected $course;

    /**
     * @var student $student The student.
     */
    protected $student;

    /**
     * @var array $sectors The sectors.
     */
    protected $sectors;

    /**
     * Constructor.
     *
     * @param mixed $data An array of student data.
     * @param array $related Related objects.
     */
    public function __construct($data, $related)
    {
        $this->course = $related['subscriber'];
        $this->student = $this->course->get_student($data['studentid']);
        $this->sectors = $this->student->get_logbook()->get_logentries();

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
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
            'subscriber' => 'local_booking\local\subscriber\entities\subscriber',
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
                    'status' => ['type' => PARAM_TEXT],
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
            'checklists' => [
                'type' => [
                    'id' => ['type' => PARAM_INT],
                    'name' => ['type' => PARAM_TEXT],
                    'totalitems' => ['type' => PARAM_INT],
                    'outstandingitems' => ['type' => PARAM_INT],
                    'completedcount' => ['type' => PARAM_INT],
                    'percentcomplete' => ['type' => PARAM_INT],
                    'items' => [
                        'type' => [
                            'id' => ['type' => PARAM_INT],
                            'title' => ['type' => PARAM_TEXT],
                            'displaytext' => ['type' => PARAM_RAW],
                            'ischecked' => ['type' => PARAM_BOOL],
                            'statuslabel' => ['type' => PARAM_TEXT],
                            'dateformatted' => ['type' => PARAM_TEXT],
                            'comment' => ['type' => PARAM_TEXT],
                            'statusclass' => ['type' => PARAM_TEXT],
                            'statustext' => ['type' => PARAM_TEXT],
                            'checkedbyname' => ['type' => PARAM_TEXT],
                            'timeupdated' => ['type' => PARAM_INT],
                        ],
                        'multiple' => true,
                    ],
                ],
                'multiple' => true,
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
                    'checklistsupdated' => [
                        'type' => [
                            'checklistname' => ['type' => PARAM_TEXT],
                            'items' => [
                                'type' => [
                                    'title' => ['type' => PARAM_TEXT],
                                    'status' => ['type' => PARAM_TEXT],
                                    'comment' => ['type' => PARAM_TEXT],
                                    'statusclass' => ['type' => PARAM_TEXT],
                                    'statustext' => ['type' => PARAM_TEXT],
                                ],
                                'multiple' => true,
                            ],
                        ],
                        'multiple' => true,
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
        $student = $this->student;
        $sessions = $this->student->get_bookings();
        $competencies = $this->student->get_competencies();
        $checklists = $this->student->get_checklists();
        $currentsector = count($this->sectors) . "–" . (count($this->sectors) + 1);
        $primaryinstructor = $this->student->get_primary_instructor();

        // Format student data
        $studentdata = [
            'fullname' => $this->student->get_name(),
            'vatsimcid' => $this->student->get_profile_field('vatsimid') ?? get_string('statusna'),
            'stage' => $this->course->get_exercise($this->student->get_last_booking()->get_exercise_id())->name,
            'sectors' => $currentsector,
            'status' => $this->student->status ?? 'Active',
            'instructorname' => $primaryinstructor ? $primaryinstructor->get_name() : get_string('noprimaryinstructor', 'local_booking'),
            'instructorcallsign' => $primaryinstructor ? $primaryinstructor->get_callsign() : '',
            'lastsessiondate' => userdate($student->get_last_session_date(), '%d %b %Y'),
        ];

        // Calculate KPIs
        $kpis = $this->calculate_kpis($sessions);

        // Format competencies
        $competenciesdata = $this->format_competencies($competencies);

        // Format checklists
        $checklistsdata = $this->format_checklists($checklists);

        // Format sessions
        $sessionsdata = $this->format_sessions($sessions);

        return [
            'student' => $studentdata,
            'kpis' => $kpis,
            'competencies' => $competenciesdata,
            'checklists' => $checklistsdata,
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
        // KPI calculation
        $summary = (object) $this->student->get_logbook()->get_summary();
        $studentlandings = $summary->totallandingsday + $summary->totallandingsnight;
        $instructorlandings = count($this->sectors) - $studentlandings;
        return [
            'sessions' => count($sessions),
            'sectors' => count($this->sectors),
            'totalflight' => $summary->totalflighttime,
            'pftime' => $summary->totalpftime,
            'pmtime' => $summary->totalpmtime,
            'studentlandings' => $studentlandings,
            'instructorlandings' => $instructorlandings,
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
        if ($last < $avg) {
            return ['text' => 'Improving', 'improving' => true, 'flat' => false, 'worsening' => false];
        } else if ($last > $avg) {
            return ['text' => 'Worsening', 'improving' => false, 'flat' => false, 'worsening' => true];
        } else {
            return ['text' => 'Stable', 'improving' => false, 'flat' => true, 'worsening' => false];
        }
    }

    /**
     * Format checklists data with all items and their status
     *
     * @param array $checklists
     * @return array
     */
    private function format_checklists($checklists) {
        $formatted = [];

        foreach ($checklists as $checklist) {
            $items = [];
            $outstandingcount = 0;
            $completedcount = 0;

            foreach ($checklist->items as $item) {
                $ischecked = !empty($item->usertimestamp);

                if ($ischecked) {
                    $completedcount++;
                    $statuslabel = 'Signed off';
                    $statustext = 'Signed off';
                    $statusclass = 'done';
                    $dateformatted = userdate($item->usertimestamp, '%d %b %Y');
                } else {
                    $outstandingcount++;
                    // Determine status based on item data
                    if (!empty($item->teachermark) && $item->teachermark == 2) {
                        $statuslabel = 'Needs review';
                        $statustext = 'Needs review';
                        $statusclass = 'pending';
                        $dateformatted = !empty($item->teachertimestamp) ?
                            userdate($item->teachertimestamp, '%d %b %Y') : 'Not yet reviewed';
                    } else {
                        $statuslabel = 'Pending';
                        $statustext = 'Pending';
                        $statusclass = 'pending';
                        $dateformatted = 'Planned';
                    }
                }

                $items[] = [
                    'id' => $item->id,
                    'title' => $item->displaytext,
                    'displaytext' => format_text($item->displaytext, FORMAT_HTML),
                    'ischecked' => $ischecked,
                    'statuslabel' => $statuslabel,
                    'dateformatted' => $dateformatted,
                    'comment' => $item->teachermark_comment ?? '',
                    'statusclass' => $statusclass,
                    'statustext' => $statustext,
                    'checkedbyname' => !empty($item->teachername) ? $item->teachername : '',
                    'timeupdated' => $item->usertimestamp ?? 0,
                ];
            }

            $totalitems = count($checklist->items);
            $percentcomplete = $totalitems > 0 ? round(($completedcount / $totalitems) * 100) : 0;

            $formatted[] = [
                'id' => $checklist->id,
                'name' => $checklist->name,
                'totalitems' => $totalitems,
                'outstandingitems' => $outstandingcount,
                'completedcount' => $completedcount,
                'percentcomplete' => $percentcomplete,
                'items' => $items,
            ];
        }

        return $formatted;
    }

    /**
     * Format sessions data
     *
     * @param array $sessions
     * @return array
     */
    private function format_sessions($sessions) {
        $formatted = [];

        foreach ($sessions as $index => $session) {
            // Get checklist updates for this session
            $checklistsupdated = [];
            if (!empty($session->checklistupdates)) {
                foreach ($session->checklistupdates as $checklistupdate) {
                    $checklistitems = [];
                    foreach ($checklistupdate->items as $item) {
                        $checklistitems[] = [
                            'title' => $item->title,
                            'status' => $item->status,
                            'comment' => $item->comment,
                            'statusclass' => $item->statusclass,
                            'statustext' => $item->statustext,
                        ];
                    }

                    $checklistsupdated[] = [
                        'checklistname' => $checklistupdate->name,
                        'items' => $checklistitems,
                    ];
                }
            }

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
                'islatest' => ($index === 0),
                'pftime' => $session->pftime,
                'pmtime' => $session->pmtime,
                'totalstudentlandings' => $session->studentlandings,
                'totalinstructorlandings' => $session->instructorlandings,
                'hassignoffs' => !empty($checklistsupdated),
                'sectors' => $session->sectors ?? [],
                'checklistsupdated' => $checklistsupdated,
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
