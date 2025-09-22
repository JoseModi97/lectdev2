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

$roleItems = [];
foreach (Yii::$app->session->get('roles') ?? [] as $role) {
    $roleItems[] = ['label' => $role];
}

$lecturerItems = [
    [
        'icon' => 'fas fa-chalkboard-teacher',
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
        'description' => 'Department approvals',
        'url' => '/semester/index',
        'color' => 'warning'
    ],
    [
        'icon' => 'fas fa-user-tie',
        'title' => 'Dean approval',
        'description' => 'Faculty approvals',
        'url' => '/lecturer/dean-approval',
        'color' => 'info'
    ]
];

$reportsItems = [
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
];



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

        <?php
        if (Yii::$app->session->hasFlash('title')) {
            echo GraduateHelper::growl(Yii::$app->session->hasFlash('title'), Yii::$app->session->getFlash('body'));
        }
        ?>

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

        <main id="main" role="main" class="mt-5">
            <div class="container-fluid px-0 ">
                <div class="row g-0">
                    <div id="sidebar-container" class="col-lg-3 col-xl-2 bg-light sidebar-collapsemt-5 mt-5-- " style="min-height: calc(100vh - 45px);">
                        <button class="sidebar-toggle-btn" id="sidebar-toggle">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <div class="position-sticky pt-5 px-3">
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-chalkboard-teacher text-primary me-2"></i>
                                    <h6 class="sidebar-section-title mb-0">Lecturer</h6>
                                </div>

                                <?php foreach ($lecturerItems as $item): ?>
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

                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-chart-line text-success me-2"></i>
                                    <h6 class="sidebar-section-title mb-0">Reports</h6>
                                </div>

                                <?php foreach ($reportsItems as $item): ?>
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
                        </div>
                    </div>

                    <div id="main-content" class="col-lg-9 col-xl-10 ms-sm-auto px-md-4 mt-5--" style="min-height: calc(100vh - 45px);">
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

    <footer id="footer" class="mt-auto py-3 bg-light">
        <div class="container">
            <div class="row text-muted">
                <div class="copyright text-center text-muted mt-1">
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