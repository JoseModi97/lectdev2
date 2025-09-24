<?php
// Get user roles from session
$userRoles = Yii::$app->session->get('roles') ?? [];

// Check for specific roles
$isLecturer = false;
$isHod = false;
$isDean = false;
$isFacultyAdmin = false;
$isSystemAdmin = false;

foreach ($userRoles as $role) {
    $roleLabel = $role['label'] ?? $role;

    if (strpos($roleLabel, 'LEC_') === 0) {
        if (strpos($roleLabel, 'LECTURER') !== false) {
            $isLecturer = true;
        } elseif (strpos($roleLabel, 'HOD') !== false) {
            $isHod = true;
        } elseif (strpos($roleLabel, 'DEAN') !== false) {
            $isDean = true;
        } elseif (strpos($roleLabel, 'FAC_ADMIN') !== false) {
            $isFacultyAdmin = true;
        } elseif (strpos($roleLabel, 'ADMIN') !== false) {
            $isSystemAdmin = true;
        }
    }
}

// Initialize navigation grouped by actor
$navigationByActor = [];

// LECTURER - Most restricted access
if ($isLecturer) {
    $navigationByActor['LECTURER'] = [
        'title' => 'Lecturer Functions',
        'icon' => 'fas fa-chalkboard-teacher',
        'color' => 'primary',
        'sections' => [
            'Primary Functions' => [
                [
                    'icon' => 'fas fa-list-alt',
                    'title' => 'My course allocations',
                    'description' => 'View assigned courses',
                    'url' => '/lecturer/course-allocations',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-file-alt',
                    'title' => 'Student marksheet',
                    'description' => 'Manage student grades',
                    'url' => '/lecturer/marksheet',
                    'color' => 'success'
                ],
                [
                    'icon' => 'fas fa-check-circle',
                    'title' => 'HoD approval',
                    'description' => 'Submit for department approval',
                    'url' => '/lecturer/hod-approval',
                    'color' => 'warning'
                ],
                [
                    'icon' => 'fas fa-user-tie',
                    'title' => 'Dean approval',
                    'description' => 'Submit for faculty approval',
                    'url' => '/lecturer/dean-approval',
                    'color' => 'info'
                ]
            ],
            'Reports' => [
                [
                    'icon' => 'fas fa-clipboard-check',
                    'title' => 'Marks submission status',
                    'description' => 'Track submission progress',
                    'url' => '/reports/marks-submission',
                    'color' => 'secondary'
                ],
                [
                    'icon' => 'fas fa-chart-bar',
                    'title' => 'Course analysis',
                    'description' => 'Analyze course performance',
                    'url' => '/reports/course-analysis',
                    'color' => 'dark'
                ]
            ]
        ]
    ];
}

// HEAD OF DEPARTMENT - Departmental management
if ($isHod) {
    $navigationByActor['HOD'] = [
        'title' => 'Head of Department',
        'icon' => 'fas fa-user-cog',
        'color' => 'success',
        'sections' => [
            'HOD Functions' => [
                [
                    'icon' => 'fas fa-file-upload',
                    'title' => 'View uploaded results (interface 1)',
                    'description' => 'Review uploaded results - Interface 1',
                    'url' => '/hod/uploaded-results-1',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-file-upload',
                    'title' => 'View uploaded results (interface 2)',
                    'description' => 'Review uploaded results - Interface 2',
                    'url' => '/hod/uploaded-results-2',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-file-upload',
                    'title' => 'View uploaded results (interface 3)',
                    'description' => 'Review uploaded results - Interface 3',
                    'url' => '/hod/uploaded-results-3',
                    'color' => 'primary'
                ]
            ],
            'Lecturer Allocation' => [
                [
                    'icon' => 'fas fa-calendar-alt',
                    'title' => 'Programme timetables',
                    'description' => 'Manage program schedules',
                    'url' => '/hod/programme-timetables',
                    'color' => 'info'
                ],
                [
                    'icon' => 'fas fa-calendar-plus',
                    'title' => 'Supplementary timetables',
                    'description' => 'Handle supplementary schedules',
                    'url' => '/hod/supplementary-timetables',
                    'color' => 'warning'
                ]
            ],
            'Departmental Requests' => [
                [
                    'icon' => 'fas fa-user-plus',
                    'title' => 'Lecturer requests',
                    'description' => 'Handle lecturer requests',
                    'url' => '/hod/lecturer-requests',
                    'color' => 'success'
                ],
                [
                    'icon' => 'fas fa-book',
                    'title' => 'Service courses',
                    'description' => 'Manage service courses',
                    'url' => '/hod/service-courses',
                    'color' => 'secondary'
                ]
            ],
            'Reports' => [
                [
                    'icon' => 'fas fa-chart-bar',
                    'title' => 'Course analysis',
                    'description' => 'Analyze course performance',
                    'url' => '/reports/course-analysis',
                    'color' => 'dark'
                ],
                [
                    'icon' => 'fas fa-chart-line',
                    'title' => 'Course analysis (Submitted)',
                    'description' => 'Submitted course analytics',
                    'url' => '/reports/course-analysis-submitted',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-layer-group',
                    'title' => 'Consolidated marksheet (level based)',
                    'description' => 'Level-based consolidated reports',
                    'url' => '/reports/consolidated-marksheet',
                    'color' => 'success'
                ],
                [
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'Received/Missing marks',
                    'description' => 'Track missing submissions',
                    'url' => '/reports/missing-marks',
                    'color' => 'danger'
                ]
            ]
        ]
    ];
}

// DEAN - Faculty oversight
if ($isDean) {
    $navigationByActor['DEAN'] = [
        'title' => 'Dean Functions',
        'icon' => 'fas fa-university',
        'color' => 'info',
        'sections' => [
            'Dean Functions' => [
                [
                    'icon' => 'fas fa-file-upload',
                    'title' => 'View uploaded results (interface 1)',
                    'description' => 'Review uploaded results - Interface 1',
                    'url' => '/dean/uploaded-results-1',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-file-upload',
                    'title' => 'View uploaded results (interface 2)',
                    'description' => 'Review uploaded results - Interface 2',
                    'url' => '/dean/uploaded-results-2',
                    'color' => 'primary'
                ]
            ],
            'Reports' => [
                [
                    'icon' => 'fas fa-chart-bar',
                    'title' => 'Course analysis',
                    'description' => 'Analyze course performance',
                    'url' => '/reports/course-analysis',
                    'color' => 'dark'
                ],
                [
                    'icon' => 'fas fa-chart-line',
                    'title' => 'Course analysis (Submitted)',
                    'description' => 'Submitted course analytics',
                    'url' => '/reports/course-analysis-submitted',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-layer-group',
                    'title' => 'Consolidated marksheet (level based)',
                    'description' => 'Level-based consolidated reports',
                    'url' => '/reports/consolidated-marksheet',
                    'color' => 'success'
                ],
                [
                    'icon' => 'fas fa-calendar-alt',
                    'title' => 'Created timetables',
                    'description' => 'View created schedules',
                    'url' => '/reports/created-timetables',
                    'color' => 'info'
                ],
                [
                    'icon' => 'fas fa-user-check',
                    'title' => 'Lecturer course allocation',
                    'description' => 'View lecturer assignments',
                    'url' => '/reports/lecturer-allocation',
                    'color' => 'secondary'
                ],
                [
                    'icon' => 'fas fa-cogs',
                    'title' => 'Course work definition',
                    'description' => 'Course work configurations',
                    'url' => '/reports/course-work-definition',
                    'color' => 'warning'
                ],
                [
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'Received/Missing marks',
                    'description' => 'Track missing submissions',
                    'url' => '/reports/missing-marks',
                    'color' => 'danger'
                ]
            ]
        ]
    ];
}

// FACULTY ADMINISTRATOR - Administrative functions
if ($isFacultyAdmin) {
    $navigationByActor['FACULTY_ADMIN'] = [
        'title' => 'Faculty Administrator',
        'icon' => 'fas fa-user-shield',
        'color' => 'warning',
        'sections' => [
            'Faculty Administrator Functions' => [
                [
                    'icon' => 'fas fa-file-import',
                    'title' => 'Records returned scripts',
                    'description' => 'Manage returned scripts',
                    'url' => '/faculty-admin/returned-scripts',
                    'color' => 'primary'
                ]
            ],
            'Reports' => [
                [
                    'icon' => 'fas fa-undo',
                    'title' => 'Returned scripts',
                    'description' => 'View returned script reports',
                    'url' => '/reports/returned-scripts',
                    'color' => 'warning'
                ],
                [
                    'icon' => 'fas fa-calendar-alt',
                    'title' => 'Created timetables',
                    'description' => 'View created schedules',
                    'url' => '/reports/created-timetables',
                    'color' => 'info'
                ],
                [
                    'icon' => 'fas fa-user-check',
                    'title' => 'Lecturer course allocation',
                    'description' => 'View lecturer assignments',
                    'url' => '/reports/lecturer-allocation',
                    'color' => 'secondary'
                ],
                [
                    'icon' => 'fas fa-cogs',
                    'title' => 'Course work definition',
                    'description' => 'Course work configurations',
                    'url' => '/reports/course-work-definition',
                    'color' => 'success'
                ],
                [
                    'icon' => 'fas fa-chart-bar',
                    'title' => 'Course analysis',
                    'description' => 'Analyze course performance',
                    'url' => '/reports/course-analysis',
                    'color' => 'dark'
                ]
            ]
        ]
    ];
}

// SYSTEM ADMINISTRATOR - Highest access level
if ($isSystemAdmin) {
    $navigationByActor['SYSTEM_ADMIN'] = [
        'title' => 'System Administrator',
        'icon' => 'fas fa-cog',
        'color' => 'danger',
        'sections' => [
            'System Administrator Functions' => [
                [
                    'icon' => 'fas fa-calendar-alt',
                    'title' => 'Created timetables',
                    'description' => 'Manage system timetables',
                    'url' => '/admin/created-timetables',
                    'color' => 'primary'
                ],
                [
                    'icon' => 'fas fa-user-check',
                    'title' => 'Lecturer course allocation',
                    'description' => 'System-wide lecturer assignments',
                    'url' => '/admin/lecturer-allocation',
                    'color' => 'secondary'
                ],
                [
                    'icon' => 'fas fa-cogs',
                    'title' => 'Course work definition',
                    'description' => 'System course work configurations',
                    'url' => '/admin/course-work-definition',
                    'color' => 'success'
                ]
            ]
        ]
    ];
}

// DASHBOARD - Available to all users
$navigationByActor['DASHBOARD'] = [
    'title' => 'Dashboard',
    'icon' => 'fas fa-tachometer-alt',
    'color' => 'secondary',
    'sections' => [
        'Main' => [
            [
                'icon' => 'fas fa-tachometer-alt',
                'title' => 'Dashboard',
                'description' => 'Main dashboard',
                'url' => '/dashboard',
                'color' => 'warning'
            ]
        ]
    ]
];

// Helper functions for backward compatibility and easy access
function getNavigationByRole($role)
{
    global $navigationByActor;
    return $navigationByActor[$role] ?? [];
}

function getAllUserNavigation()
{
    global $navigationByActor;
    return $navigationByActor;
}

function getUserAccessLevel()
{
    global $isSystemAdmin, $isFacultyAdmin, $isDean, $isHod, $isLecturer;

    if ($isSystemAdmin) return 'SYSTEM_ADMIN';
    if ($isFacultyAdmin) return 'FACULTY_ADMIN';
    if ($isDean) return 'DEAN';
    if ($isHod) return 'HOD';
    if ($isLecturer) return 'LECTURER';
    return 'GUEST';
}

function getNavigationCount($role = null)
{
    global $navigationByActor;

    if ($role && isset($navigationByActor[$role])) {
        $count = 0;
        foreach ($navigationByActor[$role]['sections'] as $section) {
            $count += count($section);
        }
        return $count;
    }

    $totalCount = 0;
    foreach ($navigationByActor as $actor) {
        foreach ($actor['sections'] as $section) {
            $totalCount += count($section);
        }
    }
    return $totalCount;
}

// Role items for dropdown (keep existing functionality)
$roleItems = [];
foreach ($userRoles as $role) {
    $roleItems[] = ['label' => $role['label'] ?? $role];
}

// Create flat arrays for backward compatibility with existing layout
$lecturerItems = [];
$reportsItems = [];

foreach ($navigationByActor as $actorKey => $actor) {
    foreach ($actor['sections'] as $sectionName => $items) {
        if ($sectionName === 'Reports') {
            $reportsItems = array_merge($reportsItems, $items);
        } else {
            $lecturerItems = array_merge($lecturerItems, $items);
        }
    }
}

// Debug information (remove in production)
if (YII_DEBUG) {
    echo "<!-- Navigation Debug Info:\n";
    echo "User Access Level: " . getUserAccessLevel() . "\n";
    echo "Total Navigation Items: " . getNavigationCount() . "\n";
    foreach ($navigationByActor as $role => $data) {
        echo "  $role: " . getNavigationCount($role) . " items\n";
    }
    echo "-->\n";
}
