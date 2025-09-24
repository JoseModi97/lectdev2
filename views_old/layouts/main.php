<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Url;
use app\widgets\Alert;
use kartik\icons\Icon;
use yii\bootstrap5\Nav;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use kartik\widgets\Growl;
use yii\bootstrap5\NavBar;
use app\helpers\GraduateHelper;
use yii\bootstrap5\Breadcrumbs;
use yii\web\ServerErrorHttpException;
use yii\bootstrap5\BootstrapIconAsset;

if (Yii::$app->user->isGuest) {
    return Yii::$app->response->redirect(['/site/login']);
}

AppAsset::register($this);
BootstrapIconAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

// $roleItems = [];
// foreach (Yii::$app->session->get('roles') ?? [] as $role) {
//     $roleItems[] = ['label' => $role];
// }
// $lecturerItems = [
//     [
//         'icon' => 'fas fa-tachometer-alt',
//         'title' => 'Dashboard',
//         'description' => 'Dashboard',
//         'url' => '/dashboard',
//         'color' => 'warning'
//     ],
//     [
//         'icon' => 'fas fa-chalkboard-teacher',
//         'title' => 'My course allocations',
//         'description' => 'View assigned courses',
//         'url' => '/allocated-courses',
//         'color' => 'primary'
//     ],
//     [
//         'icon' => 'fas fa-file-alt',
//         'title' => 'Student marksheet',
//         'description' => 'Manage student grades',
//         'url' => '/lecturer/marksheet',
//         'color' => 'success'
//     ],
//     [
//         'icon' => 'fas fa-check-circle',
//         'title' => 'HoD approval',
//         'description' => 'Department approvals',
//         'url' => '/site/hod',
//         'color' => 'warning'
//     ],
//     [
//         'icon' => 'fas fa-user-tie',
//         'title' => 'Dean approval',
//         'description' => 'Faculty approvals',
//         'url' => '/lecturer/dean-approval',
//         'color' => 'info'
//     ]
// ];

// $reportsItems = [
//     [
//         'icon' => 'fas fa-clipboard-check',
//         'title' => 'Marks submission status',
//         'description' => 'Track submission progress',
//         'url' => '/reports/marks-submission',
//         'color' => 'secondary'
//     ],
//     [
//         'icon' => 'fas fa-chart-bar',
//         'title' => 'Course analysis',
//         'description' => 'Analyze course performance',
//         'url' => '/reports/course-analysis',
//         'color' => 'dark'
//     ]
// ];


?>
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

// Initialize navigation items
$navigationItems = [];

// LECTURER Navigation Items
if ($isLecturer) {
    $navigationItems['Lecturer'] = [
        'icon' => 'fas fa-chalkboard-teacher',
        'items' => [
            [
                'icon' => 'fas fa-list-alt',
                'title' => 'My course allocations',
                'description' => 'View assigned courses',
                'url' => '/allocated-courses',
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
                'url' => '/site/hod',
                'color' => 'warning'
            ],
            [
                'icon' => 'fas fa-user-tie',
                'title' => 'Dean approval',
                'description' => 'Submit for faculty approval',
                'url' => '/lecturer/dean-approval',
                'color' => 'info'
            ]
        ]
    ];

    $navigationItems['Reports'] = [
        'icon' => 'fas fa-chart-line',
        'items' => [
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
    ];
}

// HOD Navigation Items
if ($isHod) {
    $navigationItems['HOD Functions'] = [
        'icon' => 'fas fa-user-cog',
        'items' => [
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
        ]
    ];

    $navigationItems['Allocate lecturers'] = [
        'icon' => 'fas fa-users',
        'items' => [
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
        ]
    ];

    $navigationItems['Departmental requests'] = [
        'icon' => 'fas fa-building',
        'items' => [
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
        ]
    ];

    $navigationItems['Reports'] = [
        'icon' => 'fas fa-chart-line',
        'items' => [
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
    ];
}

// DEAN Navigation Items
if ($isDean) {
    $navigationItems['Dean Functions'] = [
        'icon' => 'fas fa-university',
        'items' => [
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
        ]
    ];

    $navigationItems['Reports'] = [
        'icon' => 'fas fa-chart-line',
        'items' => [
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
    ];
}

// FACULTY ADMINISTRATOR Navigation Items
if ($isFacultyAdmin) {
    $navigationItems['Faculty Administrator'] = [
        'icon' => 'fas fa-user-shield',
        'items' => [
            [
                'icon' => 'fas fa-file-import',
                'title' => 'Records returned scripts',
                'description' => 'Manage returned scripts',
                'url' => '/faculty-admin/returned-scripts',
                'color' => 'primary'
            ]
        ]
    ];

    $navigationItems['Reports'] = [
        'icon' => 'fas fa-chart-line',
        'items' => [
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
    ];
}

// SYSTEM ADMINISTRATOR Navigation Items
if ($isSystemAdmin) {
    $navigationItems['System Administrator'] = [
        'icon' => 'fas fa-cog',
        'items' => [
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
    ];
}

$navigationItems = array_merge([
    'Dashboard' => [
        'icon' => 'fas fa-tachometer-alt',
        'items' => [
            [
                'icon' => 'fas fa-tachometer-alt',
                'title' => 'Dashboard',
                'description' => 'Main dashboard',
                'url' => '/dashboard',
                'color' => 'warning'
            ]
        ]
    ]
], $navigationItems);

$roleItems = [];
foreach ($userRoles as $role) {
    $roleItems[] = ['label' => $role['label'] ?? $role];
}

$lecturerItems = [];
$reportsItems = [];

foreach ($navigationItems as $sectionName => $section) {
    if (isset($section['items']) && is_array($section['items'])) {
        if ($sectionName === 'Reports') {
            $reportsItems = $section['items'];
        } else {
            $lecturerItems = array_merge($lecturerItems, $section['items']);
        }
    }
}
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
    $this->registerAssetBundle(\kartik\editable\EditableAsset::class);
    $this->registerAssetBundle(\kartik\popover\PopoverXAsset::class);
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css" integrity="sha512-6c4nX2tn5KbzeBJo9Ywpa0Gkt+mzCzJBrE1RB6fmpcsoN+b/w/euwIMuQKNyUoU/nToKN3a8SgNOtPrbW12fug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"></script>

    <style>
        #sidebar-container {
            position: fixed !important;
            top: 45px;
            left: 0;
            z-index: 1000;
            height: calc(100vh - 45px - 60px) !important;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        #sidebar-container.sidebar-hidden {
            transform: translateX(-100%);
        }

        #main-content {
            margin-left: 25%;
            transition: margin-left 0.3s ease;
        }

        #main-content.sidebar-expanded {
            margin-left: 0;
        }

        #footer {
            position: fixed !important;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            height: 40px;
        }

        .container-main {
            padding-bottom: 10px !important;
        }

        @media (max-width: 991.98px) {
            #sidebar-container {
                width: 100% !important;
                max-width: 300px;
                transform: translateX(-100%);
            }

            #sidebar-container:not(.sidebar-hidden) {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0 !important;
            }
        }

        @media (min-width: 992px) and (max-width: 1199.98px) {
            #main-content {
                margin-left: 33.333333%;
            }
        }

        @media (min-width: 1200px) {
            #sidebar-container {
                width: 16.666667% !important;
            }

            #main-content {
                margin-left: 16.666667%;
            }
        }
    </style>
</head>

<body class="d-flex flex-column h-100 wrap">
    <?php $this->beginBody() ?>

    <div class="w-100">
        <header id="header">
            <div class="container-navbar">
                <?php
                NavBar::begin([
                    'brandLabel' => Html::img('@web/img/logo.png', [
                        'alt' => Yii::$app->name,
                        'style' => 'height: 25px; margin-right: 10px;'
                    ]) . Yii::$app->params['sitename'],
                    'brandUrl' => Yii::$app->homeUrl,
                    'options' => [
                        'class' => 'navbar navbar-expand-md navbar-custom fixed-top',
                        'style' => 'max-height:45px;'
                    ],
                ]);

                try {
                    echo Nav::widget([
                        'encodeLabels' => false,
                        'options' => ['class' => 'navbar-nav ms-auto'],
                        'items' => [
                            [
                                'label' => Icon::show('user-tie fa-lg') . ' ' . Yii::$app->user->identity->EMP_TITLE . ' ' .
                                    Yii::$app->user->identity->SURNAME,
                            ],
                            [
                                'label' => '<i class="fas fa-cogs"></i> My roles',
                                'items' => $roleItems
                            ],
                            [
                                'label' => '<i class="fas fa-sign-out-alt"></i> Logout',
                                'url' => Url::to(['/site/logout']),
                                'linkOptions' => [
                                    'data-method' => 'post',
                                    'data-confirm' => 'Are you sure you want to logout?'
                                ]
                            ],
                        ],
                    ]);
                } catch (Exception $ex) {
                    $message = $ex->getMessage();
                    if (YII_ENV_DEV) {
                        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                    }
                    throw new ServerErrorHttpException($message, 500);
                }

                NavBar::end();
                ?>
            </div>
        </header>

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <?php
            echo Growl::widget([
                'type' => Growl::TYPE_SUCCESS,
                'title' => 'Well done!',
                'icon' => 'bi bi-check-lg',
                'iconOptions' => ['class' => 'img-circle pull-left'],
                'body' => Yii::$app->session->getFlash('success'),
                'showSeparator' => true,
                'delay' => 0,
                'pluginOptions' => [
                    'showProgressbar' => true,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'right',
                    ]
                ]
            ]);
            ?>
        <?php elseif (Yii::$app->session->hasFlash('error')): ?>
            <?php
            echo Growl::widget([
                'type' => Growl::TYPE_DANGER,
                'title' => 'Oh snap!',
                'icon' => 'fas fa-times-circle',
                'body' => Yii::$app->session->getFlash('error'),
                'showSeparator' => true,
                'delay' => 4500,
                'pluginOptions' => [
                    'showProgressbar' => true,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'right',
                    ]
                ]
            ]);
            ?>
        <?php endif; ?>

        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <main id="main" role="main" class="mt-5 mb-5">
            <div class="container-fluid px-0">
                <div class="row g-0">
                    <div id="sidebar-container" class="col-lg-3 col-xl-2 bg-light sidebar-collapse" style="min-height: calc(100vh - 45px - 60px);">
                        <button class="sidebar-toggle-btn" id="sidebar-toggle">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <div class="position-sticky pt-5 px-3">
                            <?php foreach ($navigationItems as $sectionName => $section): ?>
                                <?php if (!empty($section['items'])): ?>
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="<?= $section['icon'] ?> text-primary me-2"></i>
                                            <h6 class="sidebar-section-title mb-0"><?= $sectionName ?></h6>
                                        </div>

                                        <?php foreach ($section['items'] as $item): ?>
                                            <div class="card sidebar-card card-<?= $item['color'] ?> mb-2" onclick="window.location.href='<?= Url::to([$item['url']]) ?>'">
                                                <div class="card-body p-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="sidebar-icon bg-<?= $item['color'] ?> me-2 flex-shrink-0">
                                                            <i class="<?= $item['icon'] ?>"></i>
                                                        </div>
                                                        <div class="flex-grow-1 min-width-0">
                                                            <div class="sidebar-card-title"><?= $item['title'] ?></div>
                                                            <p class="sidebar-card-text"><?= $item['description'] ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="main-content" class="col-lg-9 col-xl-10 ms-sm-auto px-md-4" style="min-height: calc(100vh - 45px - 60px);">
                        <button class="sidebar-hidden-btn" id="sidebar-hidden-toggle" style="display: none;">
                            <i class="fas fa-bars"></i>
                        </button>

                        <div class="container-main py-3">
                            <?= $content ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer id="footer" class="py-2 navbar-custom">
        <div class="container">
            <div class="row text-muted">
                <div class="copyright text-center" style="color: #fff">
                    &copy; University of Nairobi <?= date('Y') ?>. All Rights Reserved.
                </div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
    <script>
        $(document).ready(function() {
            const sidebarContainer = $('#sidebar-container');
            const mainContent = $('#main-content');
            const sidebarToggle = $('#sidebar-toggle');
            const sidebarHiddenToggle = $('#sidebar-hidden-toggle');
            const sidebarOverlay = $('#sidebar-overlay');

            const sidebarState = localStorage.getItem('sidebarState');
            if (sidebarState === 'hidden') {
                sidebarContainer.addClass('sidebar-hidden');
                mainContent.addClass('sidebar-expanded');
                sidebarHiddenToggle.show();
            }

            sidebarToggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                sidebarContainer.addClass('sidebar-hidden');
                mainContent.addClass('sidebar-expanded');
                sidebarHiddenToggle.show();
                localStorage.setItem('sidebarState', 'hidden');

                if ($(window).width() < 992) {
                    sidebarOverlay.removeClass('show');
                }
            });

            sidebarHiddenToggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                sidebarContainer.removeClass('sidebar-hidden');
                mainContent.removeClass('sidebar-expanded');
                sidebarHiddenToggle.hide();
                localStorage.setItem('sidebarState', 'visible');

                if ($(window).width() < 992) {
                    sidebarOverlay.addClass('show');
                }
            });

            sidebarOverlay.on('click', function() {
                sidebarContainer.addClass('sidebar-hidden');
                mainContent.addClass('sidebar-expanded');
                sidebarHiddenToggle.show();
                sidebarOverlay.removeClass('show');
                localStorage.setItem('sidebarState', 'hidden');
            });

            $(window).resize(function() {
                if ($(window).width() >= 992) {
                    sidebarContainer.removeClass('sidebar-hidden');
                    mainContent.removeClass('sidebar-expanded');
                    sidebarHiddenToggle.hide();
                    sidebarOverlay.removeClass('show');
                } else {
                    if (!sidebarContainer.hasClass('sidebar-hidden')) {
                        sidebarOverlay.addClass('show');
                    }
                }
            });

            if ($(window).width() < 992 && !sidebarContainer.hasClass('sidebar-hidden')) {
                sidebarOverlay.addClass('show');
            }
        });
    </script>
</body>

</html>

<?php $this->endPage() ?>