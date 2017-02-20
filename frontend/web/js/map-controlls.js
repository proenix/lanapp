// Initialize svgPanZoom with Hammer.js
var panZoom = svgPanZoom('#svg-map-object', {
    controlIconsEnabled: true
    , controlIconsEnabled: true
    , customEventsHandler: {
        // Halt all touch events
        haltEventListeners: ['touchstart', 'touchend', 'touchmove', 'touchleave', 'touchcancel']

        // Init custom events handler
        , init: function(options) {
            var instance = options.instance
              , initialScale = 1
              , pannedX = 0
              , pannedY = 0

            // Init Hammer
            // Listen only for pointer and touch events
            this.hammer = Hammer(options.svgElement, {
                inputClass: Hammer.SUPPORT_POINTER_EVENTS ? Hammer.PointerEventInput : Hammer.TouchInput
            })

            // Enable pinch
            this.hammer.get('pinch').set({enable: true})

            // Handle double tap
            this.hammer.on('doubletap', function(ev){
                instance.zoomIn()
            })

            // Handle pan
            this.hammer.on('panstart panmove', function(ev){
                // On pan start reset panned variables
                if (ev.type === 'panstart') {
                    pannedX = 0
                    pannedY = 0
                }

                // Pan only the difference
                instance.panBy({x: ev.deltaX - pannedX, y: ev.deltaY - pannedY})
                pannedX = ev.deltaX
                pannedY = ev.deltaY
            })

            // Handle pinch
            this.hammer.on('pinchstart pinchmove', function(ev){
                // On pinch start remember initial zoom
                if (ev.type === 'pinchstart') {
                  initialScale = instance.getZoom()
                  instance.zoom(initialScale * ev.scale)
                }

                instance.zoom(initialScale * ev.scale)
            })

            // Prevent moving the page on some devices when panning over SVG
            options.svgElement.addEventListener('touchmove', function(e){      e.preventDefault(); });
        }

  , destroy: function(){
      this.hammer.destroy()
    }
      }
});

// Set full width for svg element on page.
$('div.container:has(div.site-svg-map)').css('width', '95vw');

// Resize SVG to use page efficiently.
function resizeSVG() {
    vheight = $('body').height()-195;
    vwidth = $('#svg-map-object').width();
    $('#svg-map-object').attr('height',vheight+'px');
    $('#svg-pan-zoom-controls').attr('transform','translate(' + (vwidth-60) + ',' + (vheight-70) + ') scale(0.75)');
}
function cursorPoint(evt) {
    var svg = document.getElementById('svg-map-object');
    var pt = svg.createSVGPoint();
    pt.x = evt.clientX;
    pt.y = evt.clientY;
    return pt.matrixTransform(svg.getScreenCTM().inverse());
}

$(window).ready(function() {
    resizeSVG();
    redrawPositionMap();
})

$(window).resize(function() {
    resizeSVG();
});


// Action after click
$('#svg-map-object').on('click press', function(event) {
    var pan = panZoom.getPan();
    var sizes = panZoom.getSizes();
    var zoom = sizes.realZoom;

    var x, y, pt;
    pt = cursorPoint(event);

    x = pt.x;
    y = pt.y;

    x = (x - pan.x) / zoom;
    y = (y - pan.y)/zoom;

    var svg = document.querySelector('svg');

    if (event.button == 2) {
        var position;
        position = $(event.target).parent().attr('data-pos');

    } else {
        $(".custom-menu").hide(100);
    }
});

// Show custom context menu
$('#svg-map-object').bind("contextmenu", function (event) {
    var pan = panZoom.getPan();
    var sizes = panZoom.getSizes();
    var zoom = sizes.realZoom;
    var x, y, pt;
    pt = cursorPoint(event);

    x = pt.x;
    y = pt.y;
    x = (x - pan.x) / zoom;
    y = (y - pan.y)/zoom;

    var svg = document.querySelector('svg');

    // if right button is clicked
    if (event.button == 0 || event.button == 2) {
        var position;
        position = $(event.target).attr('data-pos');
        if (!position) {
            position = $(event.target).parent().attr('data-pos');
            position = $(event.target).parent().attr('data-pos');
        }

        // Hide all custom menu elements
        $('.custom-menu li').hide();

        if (position) {
            // UPDATE data-pos for edit/delete buttons
            $('.custom-menu [data-action="edit"]').attr("data-pos", position).show();
            $('.custom-menu [data-action="delete"]').attr("data-pos",position).show();
            $('.custom-menu [data-action="move"]').show();
        } else {
            // NEW data-posX and data-posY
            $('.custom-menu [data-action="new"]').attr("data-pos-x",x).attr('data-pos-y',y).show();
        }
    }

    // avoid default menu
    event.preventDefault();

    // Hide custom menu if already visible somewhere
    if ($(".custom-menu").is(":visible")) {
        $(".custom-menu").hide(100);
    }

    // Show custom menu
    $(".custom-menu").finish().toggle(200).css({
        top: event.pageY + "px",
        left: event.pageX + "px"
    });
});

// If the menu element is clicked
$(".custom-menu li").click(function(){

    // This is the triggered action name
    switch($(this).attr("data-action")) {

        // A case for each action. Your actions here
        case "new": positionNewForm($(this).attr('data-pos-x'),$(this).attr('data-pos-y')); break;
        case "edit": positionEditForm($(this).attr('data-pos')); break;
        case "move": alert("move"); break;
        case "delete": positionDeleteForm($(this).attr('data-pos')); break;
    }

    // Hide it AFTER the action was triggered
    $(".custom-menu").hide(100);
 });

function positionNewForm(posx,posy) {
    var modal = $('#positionForm');
    modal.find('div.alert').remove();
    modal.find('#position-form').trigger('reset');
    modal.find('#position-form-delete').hide();
    modal.find('#position-form').show();
    modal.find('[name="save-button"]').val('new');
    modal.find('[name="EditPositionModel[pPosition]"]').val(0);
    modal.find('[name="EditPositionModel[pX]"]').val(posx);
    modal.find('[name="EditPositionModel[pY]"]').val(posy);
    modal.find('[name="EditPositionModel[pName]"]').val('');
    modal.find('[name="EditPositionModel[pDescription]"]').val('');
    modal.modal('show');
}

function positionEditForm(pos) {
    var modal = $('#positionForm');
    modal.find('div.alert').remove();
    modal.find('#position-form-delete').hide();
    modal.find('#position-form').show();
    modal.find('#position-form').trigger('reset');
    $.ajax({
        type: 'POST',
        url: '/map/position-form',
        data: {
            pPosition: pos,
        },
        success: function (data) {
            modal.find('[name="EditPositionModel[pName]"]').val(data.pName);
            modal.find('[name="EditPositionModel[pDescription]"]').val(data.pDescription);
            modal.find('[name="EditPositionModel[pX]"]').val(data.pX);
            modal.find('[name="EditPositionModel[pY]"]').val(data.pY);
        },
    });
    modal.find('#position-form [name="save-button"]').val('edit');
    modal.find('[name="EditPositionModel[pPosition]"]').val(pos);
    modal.modal('show');

}

function positionDeleteForm(pos) {
    var modal = $('#positionForm');
    modal.find('div.alert').remove();
    modal.find('#position-form-delete').trigger('reset');
    modal.find('[name="EditPositionModel[pPosition]"]').val(pos);
    modal.find('#position-form-delete').show();
    modal.find('#position-form').hide();
    modal.find('#position-form-delete [name="save-button"]').val('delete');
    modal.find('#position-form-delete [name="save-button"]').show();
    modal.modal('show');
}
// Set style for active position on map.
function setActivePosition(pos) {
    $('.site-svg-map ellipse.current').attr('style','fill:#ffa7a7;stroke-width:4;stroke:#ffa7a7;').removeClass('current');
    $('.site-svg-map #ellipse-pos-'+pos).addClass('current');
    $('.site-svg-map #ellipse-pos-'+pos).attr('style','fill:#ff0000;stroke-width:10;stroke:#ff0000;');

    $('.site-svg-map line.current').attr('style','stroke:#333333;stroke-width:3px;').removeClass('current');
    $('.site-svg-map *[pos1="' + pos + '"]').addClass('current').attr('style','stroke:#ff0000;stroke-width:3px;');
    $('.site-svg-map *[pos2="' + pos + '"]').addClass('current').attr('style','stroke:#ff0000;stroke-width:3px;');

}
// Redraw all connections on map and redraw active position.
function redrawPositionMap() {
    // Redraw all connections on map.
    var connections = JSON.parse($('#containerPJAX_connections').attr('data'));
    $('.site-svg-map #data-connections').empty();
    $.each(connections, function(id,group) {
        $('.site-svg-map #data-connections').append(svgLine(group[0],group[1]));
    })
    setActivePosition($('#containerPJAX_pos').attr('data-pos'));
}
// Higlight for clicked position on svg map.
$('#svg-map-object').on('click', 'a', function(event) {
    if (event.button == 0) {
        var pos = $(this).parent().attr('id').split('-').pop();
        setActivePosition(pos);
    }
})

// Create SVG compatibile ellipse element meeting svg specification.
// @params x x-axis coordinates in svg
// @params y y-axis coordinates in svg
// @params id id
function svgEllipse(x,y,id) {
    var elemento = document.createElementNS("http://www.w3.org/2000/svg", 'ellipse');
    elemento.setAttribute('ry',14);
    elemento.setAttribute('rx',14);
    elemento.setAttribute('cx',x);
    elemento.setAttribute('cy',y);
    elemento.setAttribute('id','ellipse-pos-'+id);
    elemento.setAttribute('style','fill:#ffa7a7;');
    return elemento;
}
// Create SVG compatibile foreignObject element meeting svg specification.
// Use only for creating foreignObject on svg map that should link to position in app logic.
// @params x x-axis coordinates in svg
// @params y y-axis coordinates in svg
// @params id id of position item element links to
function svgForeignObject(x,y,id) {
    var elemento = document.createElementNS("http://www.w3.org/2000/svg", 'foreignObject');
    elemento.setAttribute('width',28);
    elemento.setAttribute('height',28);
    elemento.setAttribute('x',x-14+5-15);
    elemento.setAttribute('y',y-14+5-15);
    elemento.setAttribute('id','link-pos-'+id);
    elemento.innerHTML = '<a class="text-info bigger-link-area" href="/map/index?pos='+id+'&amp;tab=tab_group" data-pjax="#containerPJAX" data-pos="'+id+'"><i class="fa fa-plug"></i></a>';
    return elemento;
}
// Create SVG compatibile line that links two elipses.
// Use only for creating lines on svg map that should link to position in app logic.
// @params pos1 id of position of ellipse1
// @params pos2 id of position of ellipse2
function svgLine(pos1, pos2) {
    var elemento = document.createElementNS("http://www.w3.org/2000/svg", 'line');
    var x1 = $('#ellipse-pos-' + pos1).attr('cx');
    var x2 = $('#ellipse-pos-' + pos2).attr('cx')
    var y1 = $('#ellipse-pos-' + pos1).attr('cy')
    var y2 = $('#ellipse-pos-' + pos2).attr('cy')
    elemento.setAttribute('x1',x1);
    elemento.setAttribute('x2',x2);
    elemento.setAttribute('y1',y1);
    elemento.setAttribute('y2',y2);
    elemento.setAttribute('pos1',pos1);
    elemento.setAttribute('pos2',pos2);
    elemento.setAttribute('style','stroke:#333333;stroke-width:3px');
    return elemento;
}

$('#positionForm').on('ready pjax:success', function(event, data, status, xhr, options){
    var modal = $('#positionForm');
    var x = modal.find('[name="EditPositionModel[pX]"]').val();
    var y = modal.find('[name="EditPositionModel[pY]"]').val();
    var pos = modal.find('[name="EditPositionModel[pPosition]"]').val();
    if (!($('#ellipse-pos-' + pos).length)) {
        $('.site-svg-map #data').append(svgEllipse(x, y, pos));
        $('.site-svg-map #data').append(svgForeignObject(x, y, pos));
    }
    if ($('#position-form-delete').is(":visible") && $('#position-form-delete [name="save-button"]').is(":hidden")) {
        $('.site-svg-map #ellipse-pos-'+pos).remove();
        $('.site-svg-map #link-pos-'+pos).remove();
    }
});
