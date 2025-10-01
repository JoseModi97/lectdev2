<?php

use app\assets\JstreeAsset;
use yii\helpers\Html;
use app\components\Menu;
use yii\helpers\Url;

JstreeAsset::register($this);
$this->title = '';

?>

<style>
    /* Skeleton preloader */
    .skeleton-preloader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #fff;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .skeleton-loader {
        width: 2rem;
        height: 2rem;
        border: 3px solid #e9ecef;
        border-top: 3px solid #0d6efd;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        /* slight upward movement */
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        /* stronger shadow */
    }


    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<div class="container py-2 position-relative">
    <!-- Preloader overlay -->
    <div id="menu-preloader" class="skeleton-preloader">
        <div class="skeleton-loader"></div>
    </div>

    <header class="header-bar d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-primary d-flex align-items-center">
            <i class="bi bi-diagram-3-fill me-2"></i> Hod
        </h5>
        <div class="d-flex align-items-center w-50">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input id="menu-search-input"
                    type="text"
                    class="form-control border-start-0"
                    placeholder="Search menu..."
                    aria-label="Menu Search" />
                <button id="menu-clear" class="btn btn-outline-danger d-none" type="button">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="row row-cols-1 row-cols-lg-3 g-3 menu-container">
        <?php
        $jsonMenu = [
            Menu::build([
                Menu::parent('HOD', [
                    Menu::link('View uploaded results (interface 1)', '/gr', 'fa fa-file-upload'),
                    Menu::link('View uploaded results (interface 2)', '/gr', 'fa fa-file-upload'),
                    Menu::link('View uploaded results (interface 3)', '/gr', 'fa fa-file-upload'),
                    Menu::parent('Allocate lecturers', [
                        Menu::link('Programme timetables', '/semester/index?filtersFor=nonSuppCourses', 'fa fa-calendar-alt'),
                        Menu::link('Supplementary timetables', '/semester/index?filtersFor=suppCourses', 'fa fa-calendar-plus'),
                        Menu::parent('Departmental requests', [
                            Menu::link(
                                'Lecturer requests',
                                Url::to([
                                    'allocation/give',
                                    'CourseAllocationFilter' => [
                                        'purpose' => 'requestedCourses',
                                        'academicYear' => '2024/2025',
                                    ],
                                    '_csrf' => Yii::$app->request->csrfToken,
                                ]),
                                'fa fa-chalkboard-teacher'
                            ),
                            'Service courses <span class="badge bg-primary">New</span>',
                            Url::to([
                                'allocation/give',
                                'CourseAllocationFilter' => [
                                    'purpose' => 'serviceCourses',
                                    'academicYear' => '2024/2025',
                                ],
                                '_csrf' => Yii::$app->request->csrfToken,
                            ]),
                        ]),
                    ]),
                    Menu::parent('Reports', [
                        Menu::link('Course analysis', '/gr', 'fa fa-chart-pie'),
                        Menu::link('Course analysis (Submitted)', '/gr', 'fa fa-chart-line'),
                        Menu::link('Consolidated marksheet (level based)', '/gr', 'fa fa-layer-group'),
                        Menu::link('Received/Missing marks', '/gr', 'fa fa-exclamation-triangle'),
                    ]),
                ]),
            ]),
        ];

        foreach ($jsonMenu as $i => $jsonData): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body menu-tree-container<?= $i ?>"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$this->registerJs(
    <<<JS
let clearBtn = $("#menu-clear");
clearBtn.hide();

$.jstree.defaults.plugins = ['html_data','types','search'];
$.jstree.defaults.search = {case_insensitive: true, show_only_matches: true};
$.jstree.defaults.types = {
    "default": {"icon":"bi bi-folder-fill text-primary me-1"},
    "links": {"icon":"bi bi-link-45deg text-success"}
};

$("#menu-search-input").on('keyup change', function() {
    let query = $(this).val();
    $('[class*="menu-tree-container"]').each(function() {
        let tree = $(this).jstree(true);
        if (tree) {
            tree.search(query);
        }
    });
    query ? clearBtn.show() : clearBtn.hide();
});

clearBtn.on('click', function () {
    $('[class*="menu-tree-container"]').each(function() {
        let tree = $(this).jstree(true);
        if (tree) {
            tree.clear_search();
        }
    });
    $("#menu-search-input").val('');
    clearBtn.hide();
});

// Hide preloader once DOM + trees are ready
$(document).ready(function() {
    let totalTrees = $('[class*="menu-tree-container"]').length;
    let readyCount = 0;

    $('[class*="menu-tree-container"]').on('ready.jstree', function () {
        readyCount++;
        if (readyCount === totalTrees) {
            $("#menu-preloader").fadeOut(300);
        }
    });
});
JS
);

foreach ($jsonMenu as $i => $jsonData) {
    $this->registerJs(
        <<<JS
$('div.menu-tree-container{$i}')
    .jstree({
        'core': { 'data': $jsonData }
    })
    .on('ready.jstree', function () { 
        $(this).jstree('open_all'); 
    })
    .on("select_node.jstree", function (e, data) {
        if ((data.node.a_attr.href).indexOf('void(') === -1) {
            if (typeof(data.node.a_attr.target) === "undefined")
                document.location = data.node.a_attr.href;
            else
                window.open(data.node.a_attr.href, data.node.a_attr.target);
        }
    });
JS
    );
}
?>