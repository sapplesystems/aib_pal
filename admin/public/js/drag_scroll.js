///**
// * @fileoverview dragscroll - scroll area by dragging
// * @version 0.0.8
// * 
// * @copyright 2018 sapple system <www.sapple.co.in> 
// */

(function($){ 
$.fn.dragscrollable = function( options ){
  var settings = $.extend({
      dragSelector:'>:first',
      acceptPropagatedEvent: true,
      preventDefault: true,
      // Hovav:
      allowY: true
  }, options || {});

  var dragscroll= {
    startDrag: function(event, x, y) {
      // Initial coordinates will be the last when dragging
      event.data.lastCoord = {left: x, top: y};
    },
    doDrag: function(event, x, y) {
// How much did the mouse move?
      var delta = {
        left: (x - event.data.lastCoord.left),
        top: ((settings.allowY) ? y - event.data.lastCoord.top : 0)
      };

      // Set the scroll position relative to what ever the scroll is now
      event.data.scrollable.scrollLeft(event.data.scrollable.scrollLeft() - delta.left);
      event.data.scrollable.scrollTop(event.data.scrollable.scrollTop() - delta.top);

      // Save where the cursor is
      event.data.lastCoord={ left: x, top: y };
    },
    /* ==========================================================
       Touch */
    touchStartHandler: function(event) {
      var touch = event.originalEvent.touches[0];
      dragscroll.startDrag(event, touch.pageX, touch.pageY);

      $.event.add( document, "touchend", dragscroll.touchEndHandler, event.data );
      $.event.add( document, "touchmove",  dragscroll.touchMoveHandler, event.data );
    },
    touchMoveHandler: function(event) {
      var touch = event.originalEvent.touches[0];
      dragscroll.doDrag(event, touch.pageX, touch.pageY);
    },
    touchEndHandler: function(event) {
      $.event.remove( document, "touchmove", dragscroll.mouseMoveHandler);
      $.event.remove( document, "touchend", dragscroll.mouseUpHandler);
    },
    /* ==========================================================
        Mouse */
    mouseDownHandler : function(event) {
      // mousedown, left click, check propagation
      if (event.which != 1 || (!event.data.acceptPropagatedEvent && event.target != this)){
        return false;
      }

      dragscroll.startDrag(event, event.clientX, event.clientY);

      $.event.add( document, "mouseup", dragscroll.mouseUpHandler, event.data );
      $.event.add( document, "mousemove",  dragscroll.mouseMoveHandler, event.data );

      if (event.data.preventDefault) {
        event.preventDefault();
        return false;
      }
    },
    mouseMoveHandler : function(event) { // User is dragging
      dragscroll.doDrag(event, event.clientX, event.clientY);

      if (event.data.preventDefault) {
        event.preventDefault();
        return false;
      }
    },
    mouseUpHandler : function(event) { // Stop scrolling
      $.event.remove( document, "mousemove", dragscroll.mouseMoveHandler);
      $.event.remove( document, "mouseup", dragscroll.mouseUpHandler);
      if (event.data.preventDefault) {
        event.preventDefault();
        return false;
      }
    }
  }

   // set up the initial events
  this.each(function() {
    // closure object data for each scrollable element
    var data = {
      scrollable : $(this),
      acceptPropagatedEvent : settings.acceptPropagatedEvent,
      preventDefault : settings.preventDefault
    };
    // Set mouse initiating event on the desired descendant
    $(this).find(settings.dragSelector).bind('mousedown',  data, dragscroll.mouseDownHandler);
    $(this).find(settings.dragSelector).bind('touchstart', data, dragscroll.touchStartHandler);
  });
}; //end plugin dragscrollable


})( jQuery ); // confine scope