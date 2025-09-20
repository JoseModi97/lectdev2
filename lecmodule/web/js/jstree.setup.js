$(function () {
    $(".search-menu").focus();
    var treeview = $('.rq').jstree({
        "themes" : { "stripes" : true },
        "types" : {
            "file" : {
                "icon" : "glyphicon glyphicon-link"
            },
            "search" : {
                "icon" : "glyphicon glyphicon-search"
            },
            "stats" : {
                "icon" : "glyphicon glyphicon-stats"
            },
            "book" : {
                "icon" : "glyphicon glyphicon-book"
            },
            "new" : {
                "icon" : "glyphicon glyphicon-plus-sign"
            },
            "edit" : {
                "icon" : "glyphicon glyphicon-edit"
            },
            "wrench" : {
                "icon" : "glyphicon glyphicon-wrench"
            },
            "hand" : {
                "icon" : "glyphicon glyphicon-hand-right"
            },
            "cog" : {
                "icon" : "glyphicon glyphicon-cog"
            },
            "options" : {
                "icon" : "glyphicon glyphicon-option-horizontal"
            },
        },
        "search": {
            "case_insensitive": false,
            "show_only_matches" : true
        },
        "plugins" : [ "types","search" ]
    })
        .on('ready.jstree', function() {
            $(this).jstree('open_all');
        })
        .on("select_node.jstree", function (e, data) {
            if((data.node.a_attr.href).indexOf('void(') == -1){
                if( typeof (data.node.a_attr.target) == "undefined" )
                    document.location = data.node.a_attr.href;
                else
                    window.open( data.node.a_attr.href, data.node.a_attr.target );
            }
        })
        .on('search.jstree', function (nodes, str, res) {
            if (str.nodes.length===0) {
                $(this).jstree(true).hide_all();
            }
        });

    $("#men-search input").keyup(function() {
        $.each($('.rq'),function(js){
            $(this).jstree(true).show_all();
        });
        treeview.jstree('search', $(this).val());
    });
});