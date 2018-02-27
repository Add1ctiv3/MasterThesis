var RECORDS_PER_PAGE = 600;
var NETWORK = null;
var NODES = null;
var EDGES = null;
var OPTIONS = {
    autoResize: true, //detect when container is resized and adapt
    height: '100%',
    width: '100%',
    groups: {
        mobiles: {
            shape: 'circularImage',
            image: 'templates/images/mobile_icon.png',
            size:60,
            borderWidth: 1,
            borderWidthSelected: 2,
            font: {
                color: '#070707',
                size: 35,
                face: 'arial'
            },
            color: {
                border: "#4f4f4f",
                color:'#4f4f4f',
                highlight:'#d65100',
                hover: '#d65100',
                opacity:1.0
            }
        },
        landlines: {
            shape: 'circularImage',
            image: 'templates/images/landline_icon.png',
            size:60,
            borderWidth: 1,
            borderWidthSelected: 2,
            font: {
                color: '#070707',
                size: 35,
                face: 'arial'
            },
            color: {
                border: "#4f4f4f",
                color:'#4f4f4f',
                highlight:'#d65100',
                hover: '#d65100',
                opacity:1.0
            }
        }
    },
    nodes: {
        font: {
            color: '#343434',
            size: 25, // px
            face: 'arial',
            background: '#e4e9f0',
            strokeWidth: 1, // px
            strokeColor: '#ffffff',
            align: 'center',
            multi: false,
            vadjust: 0,
            bold: {
                color: '#e6920b',
                size: 26, // px
                face: 'arial',
                vadjust: 0,
                mod: 'bold'
            }
        },
        physics:true,
        shapeProperties: {
            interpolation: false    // 'true' for intensive zooming
        },
        labelHighlightBold: true
    },
    edges: {
        scaling:{
            min: 4,
            max: 15,
            label: {
                enabled: false,
                min: 18,
                max: 20,
                maxVisible: 20,
                drawThreshold: 5
            }
        },
        color: {
            color:'#888',
            highlight:'#d65100',
            hover: '#d65100',
            opacity:1.0
        },
        font: {
            color: '#343434',
            size: 22, // px
            face: 'arial'
        },
        physics: true,
        smooth: {
            enabled: true,
            type: 'discrete',
            roundness: 0.5
        }
    },
    physics: {
        "barnesHut": {
            "gravitationalConstant": -50000,
            "centralGravity": 0.1,
            "springLength": 300,
            "springConstant": 0.01,
            "damping": 1,
            "avoidOverlap": 1
        },
        "minVelocity": 0.75,
        enabled: true,
        stabilization: {
            enabled: true,
            iterations: 1000,
            updateInterval: 50,
            onlyDynamicEdges: false,
            fit: true
        },
        timestep: 0.3,
        adaptiveTimestep: true
    },
    layout: {
        improvedLayout:true,
        hierarchical: {
            enabled: false,
            levelSeparation: 250,
            nodeSpacing: 150,
            treeSpacing: 220,
            blockShifting: true,
            edgeMinimization: true,
            direction: 'UD',        // UD, DU, LR, RL
            sortMethod: 'hubsize'   // hubsize, directed
        }
    },
    interaction:{
        dragNodes:true,
        dragView: true,
        hideEdgesOnDrag: false,
        hideNodesOnDrag: false,
        hover: false,
        hoverConnectedEdges: true,
        keyboard: {
            enabled: false,
            speed: {x: 10, y: 10, zoom: 0.02},
            bindToWindow: true
        },
        multiselect: true,
        navigationButtons: false,
        selectable: true,
        selectConnectedEdges: false,
        tooltipDelay: 300,
        zoomView: true
    }
};

var CLIPBOARD = [];
var ACTIONS = [];

var CANVAS;
var CTX;
var RECT = {}, DRAG = false;
var drawingSurfaceImageData;

var MULTISELECT_MODE = false;

$(document).ready(function() {

    resizeSidebar();

    $(window).on('resize', function(e) {
        resizeSidebar();
    });

    document.onkeydown = KeyPress;

    document.oncontextmenu = function(e) { e.preventDefault(); };

    //import data from dataset button click
    $("#visualize-dataset-button").click(function() {
        $.ajax({
            type: "POST",
            url: "ajax/ajax.file_oriented_requests.php",
            dataType:"json",
            data: {uri: "getSets"},
            beforeSend: function() {

            },
            error: function(xhr, ajaxOptions, thrownError) {
                toastr.error(thrownError);
            },
            success : function(json) {

                if (json.error != null) {
                    toastr.error(json.error);
                    return;
                }

                if (json.reply && json.reply.result == "success") {

                    var html = "";

                    for(var i=0; i < json.reply.message.length; i++) {

                        var record = json.reply.message[i];
                        html += "<div class='set-block selectable-set' records='"+record.load+"' rel='"+record.name+"'>"+record.name + " - " + record.creator + " ("+record.visibility+")" + "</div>";

                    }

                    $("#sets-container").html(html);

                    //make the sets dialog
                    $( "#sets-panel" ).dialog({
                        autoOpen: true,
                        height: 530,
                        width: 550,
                        modal: true,
                        draggable: true,
                        resizable: false,
                        closeOnEscape: true,
                        title: "Import data from dataset",
                        buttons: {
                            "Import": function() {
                                if($(".selected-set").length == 0) {
                                    toastr.error("Select a dataset to import!");
                                    return;
                                }

                                var load = $(".selected-set").attr("records");

                                if(load > 3000) {
                                    $("#exact-nodes-number").text(load);
                                    getRecords($(".selected-set").attr("rel"), true);
                                } else {
                                    getRecords($(".selected-set").attr("rel"), false);
                                }

                            }
                        }
                    });

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call to get sets
    });

    //what happens when you click a set
    $(document).on("click", ".selectable-set", function() {

        if($(this).hasClass("selected-set")) {
            $(this).removeClass("selected-set");
            return;
        }

        $(".selectable-set").removeClass("selected-set");

        $(this).addClass("selected-set");

    });

    enableHighlightRectangle();
    setupContextMenu();

    //what happens when you click the select connected nodes and edges
    $("#select-next-level-button").click(function() {

        if(NETWORK === null) {
            toastr.error("You need a network first!!");
            return;
        }

        var selection = NETWORK.getSelectedNodes();

        if(selection.length == 0) {
            toastr.error("You need to select at least one telephone to expand to its connections!");
            return;
        }

        askForLevelOfConnection();

    });

    $("#multiselect-button").click(function() {

        if($(this).attr("active") == "false") {
            $(this).attr("active", "true");
            MULTISELECT_MODE = true;
            $(this).html('<img src="templates/images/select_disabled.png" width="30" height="30" />');
            toastr.info("Multiselect mode enabled");
        } else {
            $(this).attr("active", "false");
            MULTISELECT_MODE = false;
            $(this).html('<img src="templates/images/select.png" width="30" height="30" />');
            toastr.info("Multiselect mode disabled");
        }

    });

    $("#trace-path-button").click(function() {

        if(NETWORK == null) { return; }

        var selectedNodes = NETWORK.getSelectedNodes();

        if(selectedNodes.length !== 2) {
            toastr.error("You need to have two nodes selected to trace their path!")
            return;
        }

        discoverPaths(selectedNodes);

    });
    
    $("#search-node-button").click(function () {

        showSearchDialog();

    });

    $("#network-capital-analysis-button").click(function() {
        askForRSL();
        //networkCapitalAnalysis();
    });

    $(document).on("click", ".result-number, .nf-result-number", function() {

        if(NETWORK === null) {
            return;
        }

        var nodeId = $(this).attr("rel");

        NETWORK.setSelection({nodes: [nodeId], edges: []});
        NETWORK.focus(nodeId, {
            scale: 1,
            offset: {x:0, y:0},
            locked: false,
            animation: true
        })
    });

    $("#fragmentation-analysis-button").click(function() {
        networkFragmentationAnalysis();
    });

    $("#reach-analysis-button").click(function() {
        networkReachAnalysis();
    });

    $(document).on("click", ".query-result-record", function() {
        var curVal = $("span#selected-records").html();

        if($(this).hasClass("selected-record")) {
            $(this).removeClass("selected-record");
            curVal--;
            $("#selected-records").text(curVal);
            return;
        }
        curVal++;
        $("#selected-records").text(curVal);
        $(this).addClass("selected-record");
    });

    $("#degree-button").click(function() {

        getNodesDegree();

    });

    $("#closeness-button").click(function() {

        getNodesCloseness();

    });

    $("#betweenness-button").click(function() {

        getNodesBetweenness();

    });

});

function getNodesBetweenness() {

    if(NETWORK === null) {
        toastr.error("You need a network to analyze.");
        return;
    }

    var nodes = [];
    NODES.get().forEach(function(node) {
        var edgeIds = NETWORK.getConnectedEdges(node.id);
        var n = {
            id: node.id,
            connections: EDGES.get(edgeIds)
        }
        nodes.push(n);
    });

    var net = {
        nodes: nodes,
        uri: "betweennessCentrality"
    };

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: net,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                var nodes = json.reply.data;
                var html = "";

                nodes.forEach(function(node) {
                    html += "<tr>" +
                    "<td class='result-number' rel='"+node.number+"'>"+node.number+"</td>" +
                    "<td>"+node.bc+"</td>" +
                    "</tr>";
                });

                $(html).appendTo("#nodes-reach-analysis-results-table");

                sizeNodesByBetweenness(nodes);

                //make the sets dialog
                $( "#nodes-reach-analysis-results-panel" ).dialog({
                    width: 400,
                    height: 470,
                    autoOpen: true,
                    modal: false,
                    draggable: true,
                    resizable: true,
                    closeOnEscape: true,
                    title: "Nodes Betweenness Centrality Analysis Results",
                    close: function() {
                        sizeBackNodes(nodes);
                        $("#nodes-reach-analysis-results-table tr").remove();
                        $( "#nodes-reach-analysis-results-panel" ).dialog("destroy");
                    },
                    buttons: {
                        "OK": function() {
                            sizeBackNodes(nodes);
                            $("#nodes-reach-analysis-results-table tr").remove();
                            $( "#nodes-reach-analysis-results-panel" ).dialog("destroy");
                        }
                    }
                });

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets

}

function getNodesCloseness() {

    if(NETWORK === null) {
        toastr.error("You need a network to analyze.");
        return;
    }

    var nodes = [];
    NODES.get().forEach(function(node) {
        var edgeIds = NETWORK.getConnectedEdges(node.id);
        var n = {
            id: node.id,
            connections: EDGES.get(edgeIds)
        }
        nodes.push(n);
    });

    var net = {
        nodes: nodes,
        uri: "closenessCentrality"
    };

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: net,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                var nodes = json.reply.data;
                var html = "";

                nodes.forEach(function(node) {
                    html += "<tr>" +
                    "<td class='result-number' rel='"+node.number+"'>"+node.number+"</td>" +
                    "<td>"+node.cc+"</td>" +
                    "</tr>";
                });

                $(html).appendTo("#nodes-reach-analysis-results-table");

                sizeNodesByCloseness(nodes);

                //make the sets dialog
                $( "#nodes-reach-analysis-results-panel" ).dialog({
                    width: 400,
                    height: 470,
                    autoOpen: true,
                    modal: false,
                    draggable: true,
                    resizable: true,
                    closeOnEscape: true,
                    title: "Nodes Closeness Centrality Analysis Results",
                    close: function() {
                        sizeBackNodes(nodes);
                        $("#nodes-reach-analysis-results-table tr").remove();
                        $( "#nodes-reach-analysis-results-panel" ).dialog("destroy");
                    },
                    buttons: {
                        "OK": function() {
                            sizeBackNodes(nodes);
                            $("#nodes-reach-analysis-results-table tr").remove();
                            $( "#nodes-reach-analysis-results-panel" ).dialog("destroy");
                        }
                    }
                });

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets

}

function getNodesDegree() {

    if(NETWORK === null) {
        toastr.error("You need a network to analyze.");
        return;
    }

    var nodes = [];
    NODES.get().forEach(function(node) {
        var edgeIds = NETWORK.getConnectedEdges(node.id);
        var n = {
            id: node.id,
            connections: EDGES.get(edgeIds)
        }
        nodes.push(n);
    });

    var net = {
        nodes: nodes,
        uri: "nodesDegreeAlgorithm"
    };

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: net,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                var nodes = json.reply.data;
                var html = "";

                nodes.forEach(function(node) {
                    html += "<tr>" +
                                "<td class='result-number' rel='"+node.number+"'>"+node.number+"</td>" +
                                "<td>"+node.reach+"</td>" +
                            "</tr>";
                });

                $(html).appendTo("#nodes-reach-analysis-results-table");

                sizeNodesByReach(nodes);

                //make the sets dialog
                $( "#nodes-reach-analysis-results-panel" ).dialog({
                    width: 400,
                    height: 470,
                    autoOpen: true,
                    modal: false,
                    draggable: true,
                    resizable: true,
                    closeOnEscape: true,
                    title: "Nodes Degree Centrality Analysis Results",
                    close: function() {
                        sizeBackNodes(nodes);
                        $("#nodes-reach-analysis-results-table tr").remove();
                        $( "#nodes-reach-analysis-results-panel" ).dialog("destroy");
                    },
                    buttons: {
                        "OK": function() {
                            sizeBackNodes(nodes);
                            $("#nodes-reach-analysis-results-table tr").remove();
                            $( "#nodes-reach-analysis-results-panel" ).dialog("destroy");
                        }
                    }
                });

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets
}

function networkReachAnalysis() {
    if(NETWORK === null) {
        toastr.error("You need a network to analyze.");
        return;
    }

    var nodes = [];
    NODES.get().forEach(function(node) {
        var edgeIds = NETWORK.getConnectedEdges(node.id);
        var n = {
            id: node.id,
            connections: EDGES.get(edgeIds)
        }
        nodes.push(n);
    });

    var net = {
        nodes: nodes,
        uri: "networkReachAlgorithm"
    };

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: net,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                $("#reach-analysis-results-table #result-number-1").html("1. " + json.reply.data[0].number).attr("rel", json.reply.data[0].number);
                $("#reach-analysis-results-table #result-number-2").html("2. " + json.reply.data[1].number).attr("rel", json.reply.data[1].number);
                $("#reach-analysis-results-table #result-number-3").html("3. " + json.reply.data[2].number).attr("rel", json.reply.data[2].number);

                $("#numbers-reach-1").html(json.reply.data[0].value);
                $("#numbers-reach-2").html(json.reply.data[1].value);
                $("#numbers-reach-3").html(json.reply.data[2].value);

                //make the sets dialog
                $( "#reach-analysis-results-panel" ).dialog({
                    width: 400,
                    height: 470,
                    autoOpen: true,
                    modal: false,
                    draggable: true,
                    resizable: true,
                    closeOnEscape: true,
                    title: "Network Reach Analysis Results",
                    close: function() {
                        $( "#reach-analysis-results-panel" ).dialog("destroy");
                        editNodes([json.reply.data[0].number, json.reply.data[1].number, json.reply.data[2].number], false);
                    },
                    buttons: {
                        "OK": function() {
                            $( "#reach-analysis-results-panel" ).dialog("destroy");
                            editNodes([json.reply.data[0].number, json.reply.data[1].number, json.reply.data[2].number], false);
                        }
                    }
                });

                //change the nodes size and color
                editNodes([json.reply.data[0].number, json.reply.data[1].number, json.reply.data[2].number], true);

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets
}

function networkFragmentationAnalysis() {

    if(NETWORK === null) {
        toastr.error("You need a network to analyze.");
        return;
    }

    var nodes = [];
    NODES.get().forEach(function(node) {
        var edgeIds = NETWORK.getConnectedEdges(node.id);
        var n = {
            id: node.id,
            connections: EDGES.get(edgeIds)
        }
        nodes.push(n);
    });

    var net = {
        nodes: nodes,
        uri: "networkFragmentationAlgorithm"
    };

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: net,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                var html = "<tr><td align='center' colspan='2' id='initial-network-fragmentation'><b>Initial NF: </b>"+json.reply.initialNF+"</td></tr>";
                json.reply.data.forEach(function(object) {
                    html += "<tr><td colspan='2' rel='"+object.node+"' class='nf-result-number'>"+object.node+"</td></tr>";
                    html += "<tr><td><b>After Removal:</b></td><td>"+object.score+"</td></tr>";
                    //change the nodes size and color
                    editNodes([object.node], true);
                });

                $("#nf-analysis-results-table").html(html);

                //make the sets dialog
                $( "#nf-analysis-results-panel" ).dialog({
                    width: 400,
                    height: 470,
                    autoOpen: true,
                    modal: false,
                    draggable: true,
                    resizable: true,
                    closeOnEscape: true,
                    title: "Network Fragmentation Analysis Results",
                    close: function() {
                        $( "#analysis-results-panel" ).dialog("destroy");
                        var nodes = [];
                        json.reply.data.forEach(function(object) {
                            nodes.push(object.node);
                        });
                        editNodes(nodes, false);

                    },
                    buttons: {
                        "OK": function() {
                            $( "#nf-analysis-results-panel" ).dialog("destroy");
                            var nodes = [];
                            json.reply.data.forEach(function(object) {
                                nodes.push(object.node);
                            });
                            editNodes(nodes, false);
                        }
                    }
                });



            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets
}

function askForRSL() {

    if(NETWORK === null) {
        toastr.error("You need a network to analyze.");
        return;
    }

    //make the sets dialog
    $( "#require-rsl-panel" ).dialog({
        width: 400,
        autoOpen: true,
        modal: false,
        draggable: true,
        resizable: false,
        closeOnEscape: true,
        title: "Network Capital Analysis RSL",
        close: function() {
            $("#rsl-input").val("");
            $( "#require-rsl-panel" ).dialog("destroy");
        },
        buttons: {
            "OK": function() {
                var rsl = $("#rsl-input").val();
                if(!isNumber(rsl) || rsl > 1 || rsl < 0) {
                    toastr.error("RSL can vary between 0 and 1.");
                }
                $( "#require-rsl-panel").dialog("destroy");
                $("#rsl-input").val("");
                networkCapitalAnalysis(rsl);
            }
        }
    });

}

function networkCapitalAnalysis(rsl) {

    var nodes = [];
    NODES.get().forEach(function(node) {
        var edgeIds = NETWORK.getConnectedEdges(node.id);
        var n = {
            id: node.id,
            weight: node.weight,
            connections: EDGES.get(edgeIds)
        }
        nodes.push(n);
    });

    var edges = [];
    EDGES.get().forEach(function(edge) {
        edges.push({from: edge.from, to: edge.to, weight: edge.value });
    });

    var net = {
        nodes: nodes,
        edges: edges,
        rsl: rsl,
        uri: "networkCapitalAlgorithm"
    };

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: net,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                $("#result-number-1").html("1. " + json.reply.data.FIRST_ITERATION.NODE).attr("rel", json.reply.data.FIRST_ITERATION.NODE);
                $("#result-number-2").html("2. " + json.reply.data.SECOND_ITERATION.NODE).attr("rel", json.reply.data.SECOND_ITERATION.NODE);
                $("#result-number-3").html("3. " + json.reply.data.THIRD_ITERATION.NODE).attr("rel", json.reply.data.THIRD_ITERATION.NODE);

                var nc1 = json.reply.data.FIRST_ITERATION.NODE_CAPITAL;
                nc1 = nc1.toFixed(5);

                var nc2 = json.reply.data.SECOND_ITERATION.NODE_CAPITAL
                nc2 = nc2.toFixed(5);

                var nc3 = json.reply.data.THIRD_ITERATION.NODE_CAPITAL;
                nc3 = nc3.toFixed(5);

                $("#numbers-network-capital-1").html(nc1);
                $("#numbers-network-capital-2").html(nc2);
                $("#numbers-network-capital-3").html(nc3);

                var first_impact = ((json.reply.data.SECOND_ITERATION.NETWORK_CAPITAL - json.reply.data.FIRST_ITERATION.NETWORK_CAPITAL)/json.reply.data.FIRST_ITERATION.NETWORK_CAPITAL) * 100;
                var second_impact = ((json.reply.data.THIRD_ITERATION.NETWORK_CAPITAL - json.reply.data.SECOND_ITERATION.NETWORK_CAPITAL) / json.reply.data.SECOND_ITERATION.NETWORK_CAPITAL) * 100;
                var third_impact =  (( json.reply.data.FOURTH_ITERATION.NETWORK_CAPITAL - json.reply.data.THIRD_ITERATION.NETWORK_CAPITAL) / json.reply.data.THIRD_ITERATION.NETWORK_CAPITAL) * 100;

                first_impact = first_impact.toFixed(2);
                second_impact = second_impact.toFixed(2);
                third_impact = third_impact.toFixed(2);

                $("#analysis-removal-impact-1").html(first_impact + "%");
                $("#analysis-removal-impact-2").html(second_impact + "%");
                $("#analysis-removal-impact-3").html(third_impact + "%");

                var ncc1 = json.reply.data.FIRST_ITERATION.NETWORK_CAPITAL;
                ncc1 = ncc1.toFixed(5);

                var ncc2 = json.reply.data.SECOND_ITERATION.NETWORK_CAPITAL;
                ncc2 = ncc2.toFixed(5);

                var ncc3 = json.reply.data.THIRD_ITERATION.NETWORK_CAPITAL;
                ncc3 = ncc3.toFixed(5);

                $("#analysis-initial-network-capital-1").html(ncc1);
                $("#analysis-initial-network-capital-2").html(ncc2);
                $("#analysis-initial-network-capital-3").html(ncc3);

                //make the sets dialog
                $( "#nc-analysis-results-panel" ).dialog({
                    width: 400,
                    height: 470,
                    autoOpen: true,
                    modal: false,
                    draggable: true,
                    resizable: true,
                    closeOnEscape: true,
                    title: "Network Capital Analysis Results",
                    close: function() {
                        $( "#analysis-results-panel" ).dialog("destroy");
                        editNodes([json.reply.data.FIRST_ITERATION.NODE, json.reply.data.SECOND_ITERATION.NODE, json.reply.data.THIRD_ITERATION.NODE], false);

                    },
                    buttons: {
                        "OK": function() {
                            $( "#nc-analysis-results-panel" ).dialog("destroy");
                            editNodes([json.reply.data.FIRST_ITERATION.NODE, json.reply.data.SECOND_ITERATION.NODE, json.reply.data.THIRD_ITERATION.NODE], false);

                        }
                    }
                });

                //change the nodes size and color
                editNodes([json.reply.data.FIRST_ITERATION.NODE, json.reply.data.SECOND_ITERATION.NODE, json.reply.data.THIRD_ITERATION.NODE], true);

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets

}

function setupDoubleClick() {

    NETWORK.on("doubleClick", function(e) {

        var selectedNode = NETWORK.getSelectedNodes();
        var selectedEdge = NETWORK.getSelectedEdges();

        if(selectedNode.length !== 1 && selectedEdge.length !== 1 && !(selectedNode.length === 1 && selectedEdge.length === 1)) {
            return;
        }

        if(selectedNode.length === 1) {

            var ajax = {
                uri: "recordDetails",
                node: selectedNode[0]
            }

            $.ajax({
                type: "POST",
                url: "ajax/ajax.visual_requests.php",
                dataType:"json",
                data: ajax,
                beforeSend: function() {
                    $( "#ajax-spinner-dialog" ).dialog({
                        modal: true,
                        resizable: false,
                        title: "Please Wait...",
                        closeOnEscape: false,
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                        }
                    });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    toastr.error(thrownError);
                    $( "#ajax-spinner-dialog" ).dialog("close");
                },
                success : function(json) {

                    $( "#ajax-spinner-dialog" ).dialog("close");

                    if (json.error != null) {
                        toastr.error(json.error);
                        return;
                    }

                    if (json.reply && json.reply.result == "success") {

                        var record = {
                            number: json.reply.data.number,
                            ccode: json.reply.data.ccode,
                            type: json.reply.data.type,
                            num_weight: json.reply.data.num_weight,
                            record_type: json.reply.data.record_type,
                            creation: json.reply.data.creation
                        }

                        viewRecordsDetails(record);

                    } //end of success if block

                    if (json.reply && json.reply.result == "failure") {
                        toastr.error(json.reply.message);
                    }
                }
            }); //end of ajax call to get sets
        }

        if(selectedEdge.length === 1) {

            var nodes = NETWORK.getConnectedNodes(selectedEdge[0]);

            var ajax = {
                uri: "edgeDetails",
                node1: nodes[0],
                node2: nodes[1]
            }

            $.ajax({
                type: "POST",
                url: "ajax/ajax.visual_requests.php",
                dataType:"json",
                data: ajax,
                beforeSend: function() {
                    $( "#ajax-spinner-dialog" ).dialog({
                        modal: true,
                        resizable: false,
                        title: "Please Wait...",
                        closeOnEscape: false,
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                        }
                    });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    toastr.error(thrownError);
                    $( "#ajax-spinner-dialog" ).dialog("close");
                },
                success : function(json) {

                    $( "#ajax-spinner-dialog" ).dialog("close");

                    if (json.error != null) {
                        toastr.error(json.error);
                        return;
                    }

                    if (json.reply && json.reply.result == "success") {

                        showQueryResults(json.reply.data, json.reply.total_records, 1);

                    } //end of success if block

                    if (json.reply && json.reply.result == "failure") {
                        toastr.error(json.reply.message);
                    }
                }
            }); //end of ajax call to get sets
        }

    });

}

function showQueryResults(data, total_records, formNumber) {

    var html = "";

    for(var i = 0; i < data.length; i++) {
        html += commRecordHtml(data[i]);
    }

    $("#query-results-inner-container").html(html);
    $("#query-results-records-number").html(+total_records+" Records");

    //reset the selected records
    $("#selected-records").text("0");
    $(".selected-records").removeClass(".selected-records");

    if(total_records <= RECORDS_PER_PAGE) {
        $("#query-results-pagination-container").hide();
        $("#query-results-pagination-current-page").text("Page 1 ( 1 - "+total_records+" )");
    } else {
        $("#query-results-pagination-container").show();
    }

    $("div[aria-describedby=query-results-container]").attr("total_records", total_records);

    $("#query-results-container").dialog({
        width: 640,
        height: (total_records <= RECORDS_PER_PAGE) ? 635 : 695,
        autoOpen: true,
        resizable: false,
        draggable: true,
        modal:true,
        title: "Total Telecommunications for these Numbers",
        close: function() {
            $(this).dialog("destroy");
            $("#selected-records").text("0");
            //reset the page number
            $("#query-results-pagination-current-page").attr("rel", 1);
            $("#query-results-pagination-current-page").text("Page 1 ( 1 - "+RECORDS_PER_PAGE+" )");
            $("div[aria-describedby=query-results-container]").removeAttr("form");
        },
        open: function() {
            $("div[aria-describedby=query-results-container]").attr("total_records", total_records);
            $("div[aria-describedby=query-results-container]").attr("form", formNumber);
            setupRightClickTrigger();
        }
    });

}

function commRecordHtml(rec) {

    if(rec.record_type == "telecommunication") {
        var icon = "telecommunication.png"

        if(rec.type == "SMS") {
            icon = "sms.png";
        }

        var html = "<div class='query-result-record no-select' creation='"+rec.creation_date+"' record_type='"+rec.record_type+"' type='"+rec.type+"' intTimestamp='"+rec.stamp+"' caller='"+rec.caller+"' called='"+rec.called+"' timestamp='"+rec.timestamp+"' duration='"+rec.duration+"' weight='"+rec.weight+"'>";
        html+= "<img class='query-result-record-icon no-select' src='templates/images/"+icon+"'>";
        html+= '<div class="query-result-record-caller no-select">'+rec.caller+'</div>';
        html += '<img src="templates/images/arrow-pointing-to-right.png" class="telecommunication-arrow no-select">';
        html += '<div class="query-result-record-called no-select">'+rec.called+'</div>';
        html += " | ";
        html += '<div class="query-result-record-date-and-time no-select">'+rec.timestamp+'</div>';
        html += " | ";
        html += '<div class="query-result-record-duration no-select">'+rec.duration+' seconds</div>';
        html += "</div>";

        return html;
    }

    if(rec.record_type == "telephone") {

        var icon = "mobile.png";

        if(rec.type == "landline") {
            icon = "telephone.png";
        }

        var html = '<div class="query-result-record no-select" num_weight="'+rec.num_weight+'" creation="'+rec.creation+'" type="'+rec.type+'" record_type="'+rec.record_type+'" number="'+rec.number+'" country_code="'+rec.country_code+'">';

        html += '<img class="query-result-record-icon no-select" src="templates/images/'+icon+'">';
        html += '<div class="query-result-telephone-number no-select">'+rec.number+'</div>';

        html += '</div>';

        return html;

    }


}

function setupRightClickTrigger() {

    $.contextMenu({
        selector: '.query-result-record',
        events: {
            show: function(options) {
                if(!$(this).hasClass("selected-record")) {
                    $(this).addClass("selected-record");
                    var curVal = $("span#selected-records").html();
                    curVal++;
                    $("#selected-records").text(curVal);
                }
            }
        },
        items: {
            "view_details": {
                name: "View Details",
                icon: "edit",
                callback: function(key, opt){

                    var record = {
                        number: $(this).attr("number"),
                        ccode: $(this).attr("country_code"),
                        type: $(this).attr("type"),
                        caller: $(this).attr("caller"),
                        called: $(this).attr("called"),
                        timestamp: $(this).attr("intTimestamp"),
                        timestamp_str: $(this).attr("timestamp"),
                        duration: $(this).attr("duration"),
                        weight: $(this).attr("weight"),
                        record_type: $(this).attr("record_type"),
                        creation: $(this).attr("creation"),
                        num_weight: $(this).attr("num_weight")
                    }

                    viewRecordsDetails(record);

                }
            }
        }
    });

}

function editNodeCallback(beforeNode, afterNode) {

    if(typeof beforeNode.id === 'undefined' || typeof afterNode.id === 'undefined') {
        return;
    }

    if(NODES.get(beforeNode.id) === null) {
        return;
    }

    NODES.update(afterNode);

    var connectedEdges = NETWORK.getConnectedEdges(beforeNode.id);

    if(connectedEdges.length > 0) {
        connectedEdges.forEach(function(edge) {

            var EDGE = EDGES.get(edge);

            if(EDGE.from == beforeNode.id) {
                EDGES.update({ id: EDGE.id, from: afterNode.id });
            }

            if(EDGE.to == beforeNode.id) {
                EDGES.update({ id: EDGE.id, to: afterNode.id });
            }

        });
    }

    if(beforeNode.id !== afterNode.id) {
        NODES.remove(beforeNode.id);
        NETWORK.setSelection({ nodes: [afterNode.id], edges: [] });
    }

}

function viewRecordsDetails(record) {

    RECORD.init(record, editNodeCallback)
        .initDialog();

}

function showSearchDialog() {

    //make the sets dialog
    $( "#search-for-node-panel" ).dialog({
        width: 360,
        height: 200,
        autoOpen: true,
        modal: true,
        draggable: true,
        resizable: false,
        closeOnEscape: true,
        title: "Search For",
        open: function(event, ui ) {
            $("#search-for-node-input").val("");
            $("#search-for-node-input").focus();
        },
        buttons: {
            "Cancel": function() {
                $("#search-for-node-panel" ).dialog("destroy");
                $("#search-for-node-input").val("");
            },
            "OK": function() {

                if(NETWORK === null) {
                    $("#search-for-node-panel" ).dialog("destroy");
                    return;
                }

                var nodeId = $("#search-for-node-input").val();

                var actualNode = NODES.get(nodeId);
                if(actualNode !== null) {
                    var actualNodeId = actualNode.id;
                } else {
                    $("#search-for-node-panel" ).dialog("destroy");
                    return;
                }

                NETWORK.setSelection({nodes: [actualNodeId], edges: []});
                NETWORK.focus(actualNodeId, {
                    scale: 1,
                    offset: {x:0, y:0},
                    locked: false,
                    animation: true
                });

                $("#search-for-node-panel" ).dialog("destroy");

            }
        }
    });

}

function askForLevelOfConnection() {

    //make the sets dialog
    $( "#level-of-connection-panel" ).dialog({
        width: 360,
        height: 220,
        autoOpen: true,
        modal: true,
        draggable: true,
        resizable: false,
        closeOnEscape: true,
        title: "Level of connection?",
        buttons: {
            "Cancel": function() {
                $("#level-of-connection-panel" ).dialog("destroy");
                $("#level-of-connection-input").val("1");
            },
            "OK": function() {
                getConnectedSelectionAtLevel($("#level-of-connection-input").val());
            }
        }
    });

}

function getConnectedSelectionAtLevel(level) {

    $( "#level-of-connection-panel" ).dialog("close");
    $("#level-of-connection-input").val("1");

    var usersSelectedNodes = NETWORK.getSelectedNodes();
    var usersSelectedEdges = NETWORK.getSelectedEdges();

    var finalSelectedNodes = usersSelectedNodes;
    var finalSelectedEdges = usersSelectedEdges;

    var nodesLevelArray = usersSelectedNodes;

    //level iteration
    for(var lvl = 0; lvl < level; lvl++) {

        var limit = nodesLevelArray.length;
        var tNodes = [];

        //iterate through the nodesLevelArray
        for(var i = 0; i < limit; i++) {
            var connectedNodes = NETWORK.getConnectedNodes(nodesLevelArray[i]);
            //load this arrays contents to the tNode array
            for(var j = 0; j < connectedNodes.length; j++) {
                if(!inArray(finalSelectedNodes, connectedNodes[j])) {
                    finalSelectedNodes.push(connectedNodes[j]);
                }
                tNodes.push(connectedNodes[j]);
            }
            var connectedEdges = NETWORK.getConnectedEdges(nodesLevelArray[i]);
            //load this arrays content to tEdges array
            for(var k = 0; k < connectedEdges.length; k++) {
                if(!inArray(finalSelectedEdges, connectedEdges[k])) {
                    finalSelectedEdges.push(connectedEdges[k]);
                }
            }
        } //end of this levels nodes array iteration

        nodesLevelArray = tNodes;

    }//end of level iteration

    NETWORK.setSelection({nodes: finalSelectedNodes, edges: finalSelectedEdges});

}

function getRecords(setname, tooMany) {

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: {uri: "importFromDataset", set: setname},
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                $( "#sets-panel" ).dialog("close");
                if(tooMany) {
                    tooManyRecordsDialog();
                }
                importNewNetwork(eval(json.reply.data.nodes), eval(json.reply.data.edges));

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets

}

function importNewNetwork(nodes, edges) {

    //first show the spinner
    $( "#ajax-spinner-dialog" ).dialog({
        modal: true,
        resizable: false,
        title: "Please Wait...",
        closeOnEscape: false,
        open: function(event, ui) {
            $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
        }
    });

    if(NETWORK !== null) {

        //here calculate the load and adjust the options accordingly
        var numberOfNodesInNetwork = NODES.get().length;
        var total = numberOfNodesInNetwork + nodes.length;

        if(total >= 200) {
            OPTIONS.physics.stabilization.enabled = false;
        }

        if(total >= 600) {
            OPTIONS.physics.enabled = false;
            OPTIONS.edges.smooth.enabled = false;
        }

        NODES.update(nodes);
        EDGES.update(edges);

    } else { // if the network is already filled with some data

        NODES = new vis.DataSet(nodes);
        EDGES = new vis.DataSet(edges);

        if(NODES.get().length >= 200) {
            OPTIONS.physics.stabilization.enabled = false;
        }

        if(NODES.get().length >= 600) {
            OPTIONS.physics.enabled = false;
            OPTIONS.edges.smooth.enabled = false;
        }
    }

    $("#main-container").css({'height' :  window.innerHeight-60+ "px"});

    // create a network
    var container = document.getElementById('main-container');

    // provide the data in the vis format
    var data = {
        nodes: NODES,
        edges: EDGES
    };

    if(NETWORK === null) {
        // initialize your network!
        NETWORK = new vis.Network(container, data, OPTIONS);
        CANVAS = NETWORK.canvas.frame.canvas;
        CTX = CANVAS.getContext('2d');
        setupDoubleClick();
        resizeSidebar();
    }

    $( "#ajax-spinner-dialog" ).dialog("close");

}

function tooManyRecordsDialog() {
    $( "#too-many-records-dialog" ).dialog({
        modal: true,
        width:400,
        position: {my: "left top", at: "left top", of: "#main-container"},
        buttons: {
            Ok: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}

function saveDrawingSurface() {
    drawingSurfaceImageData = CTX.getImageData(0, 0, CANVAS.width, CANVAS.height);
}

function restoreDrawingSurface() {
    CTX.putImageData(drawingSurfaceImageData, 0, 0);
}

function selectNodesFromHighlight() {
    var fromX, toX, fromY, toY;
    var nodesIdInDrawing = [];
    var xRange = getStartToEnd(RECT.startX, RECT.w);
    var yRange = getStartToEnd(RECT.startY, RECT.h);

    var allNodes = NODES.get();
    for (var i = 0; i < allNodes.length; i++) {
        var curNode = allNodes[i];
        var nodePosition = NETWORK.getPositions([curNode.id]);
        var nodeXY = NETWORK.canvasToDOM({x: nodePosition[curNode.id].x, y: nodePosition[curNode.id].y});
        if (xRange.start <= nodeXY.x && nodeXY.x <= xRange.end && yRange.start <= nodeXY.y && nodeXY.y <= yRange.end) {
            nodesIdInDrawing.push(curNode.id);
        }
    }
    NETWORK.selectNodes(nodesIdInDrawing);
}

function getStartToEnd(start, theLen) {
    return theLen > 0 ? {start: start, end: start + theLen} : {start: start + theLen, end: start};
}

function setupContextMenu() {

    $.contextMenu({
        selector: 'canvas',
        build: function($triggerElement, e){

            if(MULTISELECT_MODE) {
                return false;
            }

            var items = {};

            //work on the menu options

            items.remove_from_network = {
                name: "Remove selected from network",
                icon: "delete",
                callback: function() {
                    deleteSelected();
                }
            };

            items.expand_from_database = {
                name: "Expand selected from Database",
                icon: "fa-arrows-alt",
                callback: function() {
                    expandFromDatabase();
                }
            };

            items.undo = {
                name: "Undo (Ctrl + Z)",
                icon: "fa-undo",
                callback: function() {
                    undo();
                }
            };

            return {
                callback: function(){},
                items: items
            };
        },
        events: {
            show: function(options) {

            }
        }
    });
}

function enableHighlightRectangle() {

    var container = $("#main-container");

    container.on("mousemove", function(e) {
        if (MULTISELECT_MODE && DRAG) {
            restoreDrawingSurface();
            RECT.w = (e.pageX - this.offsetLeft) - RECT.startX;
            RECT.h = (e.pageY - this.offsetTop) - RECT.startY ;
            CTX.setLineDash([5]);
            CTX.strokeStyle = "rgb(0, 102, 0)";
            CTX.strokeRect(RECT.startX, RECT.startY, RECT.w, RECT.h);
            CTX.setLineDash([]);
            CTX.fillStyle = "rgba(0, 255, 0, 0.2)";
            CTX.fillRect(RECT.startX, RECT.startY, RECT.w, RECT.h);
        }
    });

    container.on("mouseup", function(e) {
        if (e.button == 2) {
            if(MULTISELECT_MODE) {
                if($("#main-container").attr("viewmode") == "free") {
                    OPTIONS.physics.enabled = true;
                    NETWORK.setOptions(OPTIONS);
                }
                restoreDrawingSurface();
                DRAG = false;
                container[0].style.cursor = "default";
                selectNodesFromHighlight();
            }
        }
    });

    container.on("mousedown", function(e) {
        if (e.button == 2) {
            if(MULTISELECT_MODE) {
                selectedNodes = e.ctrlKey ? network.getSelectedNodes() : null;
                saveDrawingSurface();
                var that = this;
                RECT.startX = e.pageX - this.offsetLeft;
                RECT.startY = e.pageY - this.offsetTop;
                DRAG = true;
                container[0].style.cursor = "crosshair";
            }
        }
    });


}

function deleteSelected() {

    var selectedNodesToBeDeleted = NETWORK.getSelectedNodes();
    var selectedEdgesToBeDeleted = NETWORK.getSelectedEdges();

    if(selectedNodesToBeDeleted.length === 0 && selectedEdgesToBeDeleted.length === 0) {
        return;
    }

    if(ACTIONS.length == 20) {
        ACTIONS.splice(0, 1);
    }

    var extraEdges;
    if(selectedNodesToBeDeleted.length > 0) {
        extraEdges = EDGES.get(selectedEdgesToBeDeleted).concat(EDGES.get(NETWORK.getConnectedEdges(selectedNodesToBeDeleted)));
    } else {
        extraEdges = EDGES.get(selectedEdgesToBeDeleted);
    }

    ACTIONS.push({NODES: NODES.get(selectedNodesToBeDeleted), EDGES: extraEdges });

    if(selectedNodesToBeDeleted.length > 0) {
        EDGES.remove(NETWORK.getConnectedEdges(selectedNodesToBeDeleted));
    }

    editNodes(selectedNodesToBeDeleted, false);

    EDGES.remove(selectedEdgesToBeDeleted);
    NODES.remove(selectedNodesToBeDeleted);

}

function cutSelection() {

    var selectedNodes = NETWORK.getSelectedNodes();
    var selectedEdges = NETWORK.getSelectedEdges();

    if(selectedNodes.length == 0 && selectedEdges.length == 0) {
        return;
    }

    if(CLIPBOARD.length == 1) {
        CLIPBOARD.splice(0, 1);
    }

    if(ACTIONS.length == 20) {
        ACTIONS.splice(0, 1);
    }

    ACTIONS.push({NODES: NODES.get(selectedNodes), EDGES: EDGES.get(selectedEdges)});
    CLIPBOARD.push({NODES: NODES.get(selectedNodes), EDGES: EDGES.get(selectedEdges)});

    NETWORK.deleteSelected();

}

function copySelection() {

    var selectedNodes = NETWORK.getSelectedNodes();
    var selectedEdges = NETWORK.getSelectedEdges();

    if(selectedNodes.length == 0 && selectedEdges.length == 0) {
        return;
    }

    if(CLIPBOARD.length == 1) {
        CLIPBOARD.splice(0, 1);
    }

    if(ACTIONS.length == 20) {
        ACTIONS.splice(0, 1);
    }

    CLIPBOARD.push({NODES: NODES.get(selectedNodes), EDGES: EDGES.get(selectedEdges)});

}

function undo() {

    var lastIndex = ACTIONS.length - 1;
    if(lastIndex < 0) {
        return;
    }

    if(ACTIONS[lastIndex].NODES.length > 0) {
        NODES.update(ACTIONS[lastIndex].NODES);
        ACTIONS[lastIndex].NODES.forEach(function(node) {
            editNodes([node.id], false);
        });

    }

    if(ACTIONS[lastIndex].EDGES.length > 0) {
        EDGES.update(ACTIONS[lastIndex].EDGES);
    }

    ACTIONS.splice(lastIndex, 1);

}

function paste() {

    var lastIndex = CLIPBOARD.length - 1;
    if(lastIndex < 0) {
        return;
    }

    if(CLIPBOARD[lastIndex].NODES.length > 0) {
        NODES.update(CLIPBOARD[lastIndex].NODES);
    }

    if(CLIPBOARD[lastIndex].EDGES.length > 0) {
        EDGES.update(CLIPBOARD[lastIndex].EDGES);
    }

    CLIPBOARD.splice(lastIndex, 1);

}

function selectEverything() {
    if(NETWORK !== null && NODES !== null) {

        var nodes = NODES.get();
        var nodesArray = $.map(nodes, function(value, index) {
            return value.id;
        });

        var edges = EDGES.get();
        var edgesArray = $.map(edges, function(value, index) {
            return value.id;
        });

        NETWORK.setSelection({nodes: nodesArray, edges: edgesArray});

    }
}

function expandFromDatabase() {

    //first get the selected nodes
    var selectedNodes = NETWORK.getSelectedNodes();
    if(selectedNodes.length < 1) {
        toastr.error("You need to select at least one node to expand it!");
        return;
    }

    var obj = {
        nodes: selectedNodes,
        uri: "expandNodes"
    }

    $.ajax({
        type: "POST",
        url: "ajax/ajax.visual_requests.php",
        dataType:"json",
        data: obj,
        beforeSend: function() {
            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                NODES.update(eval(json.reply.data.nodes));
                EDGES.update(eval(json.reply.data.edges));

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

function discoverPaths(selectedNodes) {

    unmarkVisitedNodes();

    var start = selectedNodes[0];

    markAsVisited(start);

    var connectedNodes = NETWORK.getConnectedNodes(start);

    for(var i = 0; i < connectedNodes.length; i++) {
        if(!isVisited(connectedNodes[i])) {

            //first mark them as visited
            markAsVisited(connectedNodes[i]);

            if(arrayContainsArray([start, connectedNodes[i]], selectedNodes)) {
                NETWORK.selectNodes([start, connectedNodes[i]]);
                markOnlyNeededEdges([start, connectedNodes[i]]);
                return;
            } else {
                goOn(connectedNodes[i], [start, connectedNodes[i]], selectedNodes);
            }
        }
    }

    var resultingArray = NETWORK.getSelectedNodes();

    if(arraysEqual(selectedNodes, resultingArray)) {
        toastr.error("There is no path connecting the selected nodes!");
    }

}

function goOn(node, branch, targetNodes) {

    var connected = NETWORK.getConnectedNodes(node);

    if(connected.length == 1 && isVisited(connected[0])) {
        return;
    }

    for(var i = 0; i < connected.length; i++) {
        if(!isVisited(connected[i])) {

            var br = Array.from(branch);
            markAsVisited(connected[i]);
            br.push(connected[i]);

            if(arrayContainsArray(br, targetNodes)) {

                //stop the loop
                i = connected.length + 1;

                NETWORK.selectNodes(br);
                markOnlyNeededEdges(br);

            }

            goOn(connected[i], br, targetNodes);
        }
    }

}

function markOnlyNeededEdges(targetNodes) {
    var selectedEdges = NETWORK.getSelectedEdges();
    var neededEdges = [];

    selectedEdges.forEach(function(edge) {
        EDGEobj = EDGES.get(edge);
        var fromNode = EDGEobj.from;
        var toNode = EDGEobj.to;
        if(inArray(targetNodes, fromNode) && inArray(targetNodes, toNode)) {
            neededEdges.push(EDGEobj.id);
        }
    });

    NETWORK.setSelection({nodes:targetNodes,edges:neededEdges});
}

function inArray(array, element) {

    for(var i = 0; i < array.length; i++) {
        if(array[i] == element) {
            return true;
        }
    }
    return false;

}

function markAsVisited(nodeId) {
    if(NETWORK != null) {
        NODES.update({id: nodeId, visited: true});
    }
}

function unmarkVisitedNodes() {
    if(NETWORK != null) {

        var nodes = NODES.get();

        nodes.forEach(function(node) {
            NODES.update({id: node.id, visited: false});
        });

    }
}

function isVisited(nodeId) {
    if(NETWORK != null) {
        return NODES.get(nodeId).visited;
    }
}

function arrayContainsArray(sup, sub) {

    if(typeof sup === 'undefined' || typeof sub === 'undefined') {
        return false;
    }

    if(sub.length > sup.length || sub.length === 0) {
        return false;
    }

    sup.sort();
    sub.sort();
    var i, j;
    for (i=0,j=0; i<sup.length && j<sub.length;) {
        if (sup[i] < sub[j]) {
            ++i;
        } else if (sup[i] == sub[j]) {
            ++i; ++j;
        } else {
            // sub[j] not in sup, so sub not subbag
            return false;
        }
    }
    // make sure there are no elements left in sub
    return j == sub.length;
}

function arraysEqual(a, b) {
    if (a === b) return true;
    if(typeof a === 'undefined' || typeof b === 'undefined') { return false; }
    if (a == null || b == null) return false;
    if (a.length != b.length) return false;

    // If you don't care about the order of the elements inside
    // the array, you should sort both arrays here.
    a.sort();
    b.sort();

    for (var i = 0; i < a.length; ++i) {
        if (a[i] !== b[i]) return false;
    }
    return true;
}

function editNodes(arrayOfNodeIds, markUnmark) {

    if(NETWORK === null) {
        return;
    }

    for(var i = 0; i < arrayOfNodeIds.length; i++) {

        if(NODES.get(arrayOfNodeIds[i])) {

            if(markUnmark) {
                var size = 0;
                if(i === 0) {
                    size = 180;
                } else if(i===1) {
                    size = 140;
                } else {
                    size = 100;
                }
                NODES.update({id: arrayOfNodeIds[i], size: size, color: { border: "#d65100" }, font: { size: 60, color: "#d65100" }});
            } else {
                NODES.update({id: arrayOfNodeIds[i], size: 60, color: { border: "#4f4f4f" }, font: { size: 25, color: "#070707" }});
            }

        }

    }

}

function sizeNodesByReach(data) {

    if(NETWORK === null) {
        return;
    }

    data.forEach(function(node) {

        var size = node.reach * 700 / NODES.length;

        if(size == 0 || size < 30) {
            size = 30;
        }

        NODES.update({id: node.number, size: size, color: { border: "#d65100" }, font: { size: 60, color: "#d65100" }});

    });

}

function sizeNodesByCloseness(data) {

    if(NETWORK === null) {
        return;
    }

    data.forEach(function(node) {

        var size = node.cc * 500;

        if(size == 0 || size < 30) {
            size = 30;
        }

        NODES.update({id: node.number, size: size, color: { border: "#d65100" }, font: { size: 60, color: "#d65100" }});

    });

}

function sizeNodesByBetweenness(data) {
    if(NETWORK === null) {
        return;
    }

    data.forEach(function(node) {

        var size = node.bc;

        if(size == 0 || size < 30) {
            size = 30;
        }

        NODES.update({id: node.number, size: size, color: { border: "#d65100" }, font: { size: 60, color: "#d65100" }});

    });

}

function sizeBackNodes(data) {

    if(NETWORK === null) {
        return;
    }

    data.forEach(function(node) {

        var size = 60;
        NODES.update({id: node.number, size: size, color: { border: "#4f4f4f" }, font: { size: 25, color: "#070707" }});

    });

}

//here are all the keyboard triggers
function KeyPress(e) {

    var evtobj = window.event? event : e

    //Ctrl + z trigger
    if(evtobj.keyCode == 90 && evtobj.ctrlKey) { undo(); }

    //Ctrl + a trigger
    if((evtobj.keyCode == 65 || evtobj.keyCode == 97) && evtobj.ctrlKey) { selectEverything(); }

    //Ctrl + x trigger
    if(evtobj.keyCode == 88 && evtobj.ctrlKey) { cutSelection(); }

    //Delete trigger
    if(evtobj.keyCode == 46) { deleteSelected(); }

    //Ctrl + v trigger
    if(evtobj.keyCode == 86 && evtobj.ctrlKey) { paste(); }

    //Ctrl + c trigger
    if(evtobj.keyCode == 67 && evtobj.ctrlKey) { copySelection(); }

    //Ctrl + F trigger
    if(evtobj.keyCode == 70 && evtobj.ctrlKey) { showSearchDialog(); }

}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function resizeSidebar() {
    var body = document.body,
        html = document.documentElement;
    var height = Math.max( body.scrollHeight, body.offsetHeight,
        html.clientHeight, html.scrollHeight, html.offsetHeight );
    $("#left-side-bar").css({'height': height+15 + "px"}); // +90 px
    if(window.innerHeight < 990) {
        console.log("Inner Height < 990 px");
        if(NETWORK !== null) {
            NETWORK.setSize("100%", "990")
        }
    }
}
