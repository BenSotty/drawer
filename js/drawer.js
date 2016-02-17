var drawer = drawer || {};

drawer.rectangles = [];
drawer.points = [];
drawer.dom = {};
drawer.config = {
  userMsgDuration : 2 * 1000, //2 seconds
  indicatorDuration : 0.1 * 1000
}

/**
 * Start listening to clicks events and get dom containers
 */

drawer.start = function () {
  $(document).mousedown (drawer.checkPoints);
  $(document).mouseup (drawer.checkPoints);
  drawer.dom.userMsg = $("#user-msg");
  drawer.dom.startIndicator = $("#start-indicator");
  drawer.dom.endIndicator = $("#end-indicator");
  drawer.initilaizeRectangles();
};

/**
 * Action call when click is done on the document
 */

drawer.checkPoints = function (event) {
  drawer.addPoint(event);
  drawer.updateIndicatorsView();
  if (drawer.points.length === 2) {
    //It happens that mouse is not realesed in the window area then the second point is not registered and the status of the drawer is wrong,
    //In that case we do not add the rectangle but just remove the registered point and start again
    if (event.type === "mouseup") {
      drawer.addCurrentRectangle();
    }
    drawer.points = [];
  }
  
};

/**
 * Register the selected point
 */

drawer.addPoint = function (event) {
  drawer.points.push({ top : event.pageY, left : event.pageX });
};

drawer.isValidPoints = function () {
  if (drawer.points.length === 2) {
    var pointA = drawer.points[0];
    var pointB = drawer.points[1];
    return pointA.top !== pointB.top && pointA.left !== pointB.left;
  }
};

/**
 * Add the rectangle to the view and database
 */

drawer.addCurrentRectangle = function () {
  if (drawer.isValidPoints()) {
    var rectangle = new Rectangle ({ id : drawer.rectangles.length, points : drawer.points });
    rectangle.draw();
    rectangle.save();
    drawer.rectangles.push(rectangle);
  } else {
    drawer.displayError("Rectangle with no width or height cannot be drawn.");
  }
};

/**
 * Show a successful message to the user
 */

drawer.displaySuccess = function (msg) {
  drawer.dom.userMsg.addClass("success").removeClass("error").html(msg);
  drawer.displayUserMsg();
};

/**
 * Show a error message to the user
 */

drawer.displayError = function (msg) {
  drawer.dom.userMsg.addClass("error").removeClass("success").html(msg);
  drawer.displayUserMsg();
};

drawer.displayUserMsg = function() {
  drawer.dom.userMsg.fadeIn(400, function () {
    setTimeout(function() { 
      drawer.dom.userMsg.fadeOut(); 
    }, drawer.config.userMsgDuration);
  });
};

/**
 * Get the initial list of rectangles from db
 */

drawer.initilaizeRectangles = function () {
  $.post("services/services.php", { request : "getSavedRectangles" }, drawer.drawRectangleList);
};

/**
 * Draw the initial list of rectangles
 */

drawer.drawRectangleList = function (json) {
  var rectangles = drawer.utils.parseJson(json);
  if (rectangles && rectangles.length) {
    for (var i = 0; i < rectangles.length; i++) {
      var dbRect = rectangles[i];
      var rect = new Rectangle ({
        id : drawer.rectangles.length,
        points : [
          {
            top : dbRect.recttopa,
            left : dbRect.rectlefta
          },
          {
            top : dbRect.recttopb,
            left : dbRect.rectleftb
          }
        ]
      });
      rect.draw();
      drawer.rectangles.push(rect);
    }
  }
};

drawer.updateIndicatorsView = function () {
  if (drawer.points.length === 0) {
    drawer.dom.startIndicator.hide();
    drawer.dom.endIndicator.hide();
  } else if (drawer.points.length === 1) {
    drawer.displayIndicator(drawer.dom.startIndicator, drawer.points[0]);
  } else if (drawer.points.length === 2) {
    drawer.displayIndicator(drawer.dom.endIndicator, drawer.points[1], function () {
      setTimeout(function () {
        drawer.dom.startIndicator.fadeOut();
        drawer.dom.endIndicator.fadeOut();
      },drawer.config.indicatorDuration)
    });
  }
};

drawer.displayIndicator = function (elt, point, callback) {
  var top = point.top - elt.height() / 2;
  var left = point.left - elt.width() / 2;
  elt.css ( { top : top + "px",  left : left + "px", } ).fadeIn(100, callback);
};
