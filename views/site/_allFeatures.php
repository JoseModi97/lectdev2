<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


/**
 * @since 2.0
 */

use app\assets\JstreeAsset;
use yii\helpers\Html;
use app\components\Menu;

JstreeAsset::register($this);
$this->title = '';
?>
<div class="container">
    <header class="pb-1 mb-4 border-bottom fs-5l d-flex flex-col text-primary">
  
        <div class="row justify-content-center align-items-center fs-6 flex-fill pt-2">
            <div class="col d-flex flex-row ">
                <i class="bi bi-search fs-6 pr-2"></i>
                <label class="w-100">
                    <input id="menu-search-input" type="text" class="px-2 border-0 border-bottom w-100 f-5 bg-transparent" placeholder="Menu Search" style="outline: none;" />
                </label>
                <span id="menu-clear" class="fw-bold text-danger" style="cursor: pointer"><i class="bi bi-x-octagon-fill"></i></span>
            </div>
        </div>

    </header>

    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-2 menu-container">
        <?php for ($i = 0; $i < 9; $i++): ?>
            <div class="col menu-tree-container<?= $i ?>"></div>
        <?php endfor; ?>
    </div>
</div>

<?php

// $jsonMenu = [
//     "[
//         {'text' : '<span style=\"font-weight:bold;\">Department administrator</span>','a_attr':{'href':'javascript:void(0)'},
//         'children' : [
//             { 'text' : 'A','a_attr' : {'href':'/gr'},'type':'links' }
//         ]}
//     ]",
//     "[
//         {'text' : '<span style=\"font-weight:bold;\">Lecturer</span>','a_attr':{'href':'javascript:void(0)'},
//         'children' : [
//             { 'text' : 'View','a_attr' : {'href':'/gr'},'type':'links' },
//                 {
//                 'text': 'Reports',
//                 'a_attr': {'href': 'javascript:void(0)'},
//                 'state': {'opened': true},
//                 'icon': 'fa fa-chart-bar',
//                 'children': [
//                     {
//                         'text': ' status',
//                         'a_attr': {'href': '/gra'},
//                         'type': 'links',
//                         'icon': 'fa fa-user-tie',
//                         'description': 'Students'
//                     },

//                 ]
//             }
//         ]}
//     ]"
// ];
$jsonMenu = [
    Menu::build([
        Menu::parent('Menu section', [
            Menu::link('Test', '/gr')
        ])
    ]),
    Menu::build([
        Menu::parent('Complex test', [
            Menu::link('View test', '/l'),
            Menu::reports([
                Menu::link(
                    'Test 3',
                    '/gr',
                    'fa fa-user-tie',
                    ['description' => 'lorem lorem lorem']
                )
            ])
        ])
    ])
];

$this->registerJs(
    <<<JS
let clearBtn = $("#menu-clear");
clearBtn.hide();

$.jstree.defaults.plugins = ['html_data','types',"search"];
$.jstree.defaults.search = {case_insensitive: false, show_only_matches: true};
$.jstree.defaults.state = {preserve_loaded: true};
$.jstree.defaults.types = {
    "default": {"icon":"bi bi-folder-fill text-success pr-3"},
    "links": {"icon":"bi bi-link text-primary"}
};

$("#menu-search-input").on('keyup change', function() {
    $('div.menu-container > div').each(function() {
        $(this).jstree(true).show_all();
    });
    $('[class*="menu-tree-container"]').jstree('search', $(this).val());
    $("#menu-search-input").val() ? clearBtn.show() : clearBtn.hide();
});

clearBtn.on('click', function () {
    $('[class*="menu-tree-container"]').jstree("clear_search");
    $("#menu-search-input").val('').trigger('change');
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
    .on('ready.jstree', function () { $(this).jstree('open_all'); })
    .on('search.jstree', function (nodes, str) {
        if (str.nodes.length === 0) {
            $(this).jstree(true).hide_all();
        }
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