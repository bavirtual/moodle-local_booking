<?php
// This file is part of Moodle - http://moodle.org/

namespace local_booking\exporters;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use core\external\exporter;
use moodle_url;
use local_booking\local\subscriber\entities\subscriber;
use local_booking\local\participant\entities\student;
use local_booking\local\logbook\entities\logbook;

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
     * @var logbook $logbook The student's logbook.
     */
    protected $logbook;

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
        $this->logbook = $this->student->get_logbook();
        $this->sectors = $this->logbook->get_logentries();
        $data['ato'] = get_config('local_booking', 'atoname');
        $data['coursename'] = $this->course->get_fullname();
        $data['continueurl'] = new moodle_url('/local/booking/view.php', ['courseid' => $this->course->get_id()]);

        parent::__construct($data, $related);
    }

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'ato' => [
                'type' => PARAM_TEXT,
            ],
            'coursename' => [
                'type' => PARAM_TEXT,
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
            'continueurl' => ['type' => PARAM_TEXT],
            'student' => [
                'type' => [
                    'fullname' => ['type' => PARAM_TEXT],
                    'vatsimcid' => ['type' => PARAM_TEXT],
                    'stage' => ['type' => PARAM_TEXT],
                    'newlyenrolled' => ['type' => PARAM_BOOL],
                    'sectors' => ['type' => PARAM_TEXT],
                    'status' => ['type' => PARAM_TEXT],
                    'lastsessiondate' => ['type' => PARAM_TEXT],
                    'hassessions' => ['type' => PARAM_BOOL],
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
                    'instructorassigned' => ['type' => PARAM_BOOL],
                    'instructorusername' => ['type' => PARAM_TEXT],
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
                    'hassectors' => ['type' => PARAM_BOOL],
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
        $bookings = $this->student->get_bookings();
        // Pop the last booking if it is active
        if (!empty($bookings) && end($bookings)->active()) {
            $bookings = array_slice($bookings, 0, -1);
        }
        $grades = $student->get_exercise_grades(true, true);
        $competencies = $this->student->get_competencies();
        $checklists = $this->course->get_checklists(true, true, $this->data['studentid']);

        // Get student summary data
        // Get stage
        $lastbooking = $this->student->get_last_booking();
        $newlyenrolled = empty($lastbooking);
        $stage = $newlyenrolled ? get_string('newlyenrolled', 'local_booking') : $this->course->get_exercise($lastbooking->get_exercise_id())->name;
        // Get primary instructor information
        $primaryinstructor = $this->student->get_primary_instructor();
        // Get pending checklists status
        $checkliststatus = $this->course->get_pending_checklists($this->data['studentid']);

        // Format student data
        $studentdata = [
            'fullname' => $this->student->get_name(false),
            'vatsimcid' => !empty($this->student->get_profile_field('vatsimid')) ? $this->student->get_profile_field('vatsimid') : get_string('vatsimidmissing', 'local_booking'),
            'stage' => $stage,
            'newlyenrolled' => $newlyenrolled,
            'status' => $checkliststatus ? $checkliststatus : get_string('allitemscomplete', 'local_booking'),
            'instructorassigned' => !empty($primaryinstructor),
            'instructorname' => $primaryinstructor ? $primaryinstructor->get_name() : get_string('noprimaryinstructor', 'local_booking'),
            'instructorusername' => $primaryinstructor ? $primaryinstructor->get_name(false, 'alternate') : '',
            'lastsessiondate' => userdate($student->get_last_session_date(true), '%d %b %Y'),
            'hassessions' => !empty($grades) && count($grades) > 0,
        ];

        // Calculate KPIs
        $kpis = $this->calculate_kpis($grades);

        // Format competencies
        $competenciesdata = $this->format_competencies($competencies);

        // Format checklists
        $checklistsdata = $this->format_checklists($checklists);

        // Format sessions
        $sessionsdata = $this->format_sessions($bookings, $grades, $checklists);

        // Get current sector based on the formatted sessions
        $lastsession = reset($sessionsdata);
        $studentdata['sectors'] = isset($lastsession['sectornumbers']) ? $lastsession['sectornumbers'] : get_string('sectormissing', 'local_booking');

        return [
            'student' => $studentdata,
            'kpis' => $kpis,
            'competencies' => $competenciesdata,
            'checklists' => $checklistsdata,
            'sessions' => $sessionsdata,
        ];
    }

    /**
     * Calculate KPIs from booking sessions
     *
     * @param array $grades
     * @return array
     */
    private function calculate_kpis(array $grades) {
        // KPI calculation
        $summary = (object) $this->student->get_logbook()->get_summary(true);
        $studentlandings = $summary->totalpftime;
        $instructorlandings = count($this->sectors) - $studentlandings;
        $sessionscount = 0;
        foreach ($grades as $grade) {
            if (!empty($grade->attempts)) {
                $sessionscount+= count($grade->attempts);
            } else {
                $sessionscount++;
            }
        }
        return [
            'sessions' => $sessionscount,
            'sectors' => count($this->sectors),
            'totalflight' => empty($summary->totalflighttime) ? 0 : $summary->totalflighttime,
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
                'avggrade' => $comp->avggradename,
                'lastgrade' => $comp->lastgradename,
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
        if ($last > $avg) {
            return ['text' => 'Improving', 'improving' => true, 'flat' => false, 'worsening' => false];
        } else if ($last < $avg) {
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
                $ischecked = $item->teachercheck==1;

                if ($ischecked) {
                    $completedcount++;
                    $statuslabel = get_string('signedoff', 'local_booking');
                    $statustext = get_string('signedoff', 'local_booking');
                    $statusclass = 'done';
                    $dateformatted = userdate($item->teachertimestamp, '%d %b %Y');
                } else {
                    $outstandingcount++;
                    // Determine status based on item data
                    $statuslabel = get_string('pending', 'local_booking');
                    $statustext = get_string('pending', 'local_booking');
                    $statusclass = 'pending';
                    $dateformatted = get_string('planned', 'local_booking');
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
     * Format sessions data by booked sessions
     *
     * @param array $bookings
     * @param array $grades
     * @param array $checklists
     * @return array
     */
    private function format_sessions($bookings, $grades, $checklists) {
        $sessions = [];
        // Get checklist updates for this student
        $sessionprogress = $this->student->get_progress_flag('sessions', true) ?? [];

        foreach ($bookings as $index => $booking) {

            // Initialize session object
            $session = new \stdClass();
            $session->id = $booking->get_id();
            $session->exerciseid = $booking->get_exercise_id();
            $session->itemid = $booking->get_itemid();
            $session->keynote = '';
            $session->totaltime = '';
            $session->pftime = '';
            $session->pmtime = '';
            $session->studentlandings = 0;
            $session->instructorlandings = 0;
            $session->overallgrade = 0;
            $session->gradetext = '';
            $session->sectornumbers = '';
            $session->route= '';
            $session->sectors = [];
            $session->islatest = false;
            $session->checklistsupdated = [];

            // Get session instructor information
            $instructor = $this->course->get_instructor($booking->get_instructorid(), true);

            // Get sectors from logbook
            $i = 0;
            $sessionsectors = array_filter($this->sectors, fn($sector) => $sector->get_sessionid() == $session->id);
            // Get sectors from all logbook entries to determine total sectors for display
            foreach ($sessionsectors as $idx => $sector) {
                // Get first sector number and session route
                $firstsectornumber = !empty(array_keys($sessionsectors)) ? array_keys($sessionsectors)[0] + 1 : 0;
                if ($idx === $firstsectornumber - 1) {
                    $session->route = $sector->get_depicao() . "→" . $sector->get_arricao();
                } else {
                    $session->route .= "→" . $sector->get_arricao();
                }

                $i++;
                $role = $sector->get_pilotrole();
                $session->sectors[] = [
                    'number' => $i,
                    'departure' => $sector->get_depicao(),
                    'destination' => $sector->get_arricao(),
                    'aircraft' => $sector->get_aircraft(),
                    'flighttime' => $sector->get_flighttime(false),
                    'role' => $role,
                    'studentlandings' => ($role === 'PF') ? 1 : 0,
                    'instructorlandings' => ($role === 'PM') ? 1 : 0,
                ];
            }

            // Get grade
            $gradeattempt = null;
            if (key_exists($session->exerciseid, $grades) && $grade = $grades[$session->exerciseid]) {
                // Check if the item id is related to an attempt and if so get the latest attempt grade for display
                if ($grade && !empty($grade->attempts) && $session->itemid) {
                    $attempt_index = array_search($session->itemid, array_column($grade->attempts, 'itemid'));
                    $attempt_key = $attempt_index !== false ? array_keys($grade->attempts)[$attempt_index] : null;
                    if ($attempt_key !== false && $attempt_key !== null && key_exists($attempt_key, $grade->attempts)) {
                        $gradeattempt = $grade->attempts[$attempt_key];
                        $session->overallgrade = $gradeattempt->grade;
                    }
                }

                // Get keynote from grade feedback, limit to 100 characters for display
                if ($grade) {
                    $feedback = $gradeattempt ? $gradeattempt->commenttext : $grade->get_feedback_comments();
                    $session->keynote = $feedback ? explode("\n\n", $feedback, 2)[0] : '';
                    $session->keynote = strlen($session->keynote) > 100 ? substr($session->keynote, 0, 100) : $session->keynote;
                    $session->keynote = strip_tags(format_text($session->keynote, FORMAT_HTML));
                } else {
                    $session->keynote = '';
                }

                // Get session total time, PF time, PM time, and landings from logbook summary for display
                $logbooksummary = $this->logbook->get_summary(true, false, 0, $session->id);
                $session->totaltime = empty($logbooksummary->totalflighttime) ? 0 : $logbooksummary->totalflighttime;
                $session->pftime = empty($logbooksummary->totalpftime) ? 0 : $logbooksummary->totalpftime;
                $session->pmtime = empty($logbooksummary->totalpmtime) ? 0 : $logbooksummary->totalpmtime;
                $session->studentlandings = empty($logbooksummary->totalpftime) ? 0 : $logbooksummary->totalpftime;
                $session->instructorlandings = empty($logbooksummary->totalpmtime) ? 0 : $logbooksummary->totalpmtime;

                // Get overall grade
                $session->overallgrade = $gradeattempt ? intval($gradeattempt->grade) : intval($grade->finalgrade);
            }

            $checkeditems = $sessionprogress[$session->id]['checkeditems'] ?? [];
            // Flip array for O(1) lookup instead of O(n) with in_array()
            $checkeditemsmap = array_flip($checkeditems);

            foreach ($checklists as $checklist) {
                $checklistitems = [];

                // Process each item in the checklist
                foreach ($checklist->items as $item) {
                    // Check if this item is in the student's checked items (O(1) lookup)
                    $ischecked = isset($checkeditemsmap[$item->id]);

                    if (!$ischecked) {
                        continue; // Only include checked items in checklist updates
                    }

                    $checklistitems[] = [
                        'title' => $item->displaytext,
                        'status' => $ischecked ?
                            get_string('signedoff', 'local_booking') :
                            get_string('pending', 'local_booking'),
                        'statusclass' => $ischecked ? 'done' : 'pending',
                        'statustext' => $ischecked ?
                            get_string('signedoff', 'local_booking') :
                            get_string('pending', 'local_booking'),
                    ];
                }

                // Only add checklist if it has items
                if (!empty($checklistitems)) {
                    $session->checklistsupdated[] = [
                        'checklistname' => $checklist->name,
                        'items' => $checklistitems,
                    ];
                }
            }

            $sessions[] = [
                'id' => $session->id,
                'itemid' => $session->itemid,
                'number' => $index+1,
                'date' => userdate($booking->get_bookingdate(), '%d %b %Y'),
                'instructorusername' => $instructor->get_name(false, 'alternate'),
                'route' => $session->route,
                'keynotes' => $session->keynote,
                'totaltime' => $session->totaltime,
                'overallgrade' => $session->overallgrade,
                'gradetext' => $this->get_grade_text($session->overallgrade),
                'islatest' => ($index === count($bookings) - 1),
                'pftime' => $session->pftime,
                'pmtime' => $session->pmtime,
                'totalstudentlandings' => $session->studentlandings,
                'totalinstructorlandings' => $session->instructorlandings,
                'hassignoffs' => !empty($session->checklistsupdated),
                'hassectors' => !empty($session->sectors),
                'sectors' => $session->sectors,
                'checklistsupdated' => $session->checklistsupdated,
            ];

        }

        // Get sector numbers for display (e.g. "1-3" or "1-2, 4-5" if non-consecutive)
        $lastsectornumber = 0;
        foreach ($sessions as &$session) {
            $sessionsectors = $session['sectors'];
            if (!empty($sessionsectors)) {
                $firstsectornumber =  $lastsectornumber + 1;
                $lastsectornumber = $firstsectornumber + count($sessionsectors);
                $session['sectornumbers'] = count($sessionsectors)>0 ? $firstsectornumber . "–" . $lastsectornumber : '';
            }
        }

        // Sort by sortorder
        array_multisort(array_column($sessions, 'number'), SORT_DESC, $sessions);

        return $sessions;
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
