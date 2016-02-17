/**
 * Define a rectangle from tow diagonal points
 * @returns
 */


Rectangle = function (params) {
  this.id = "rect-" + params.id;
  this.pointA = params.points[0];
  this.pointB = params.points[1];
  this.view = new RectangleView(this.id);
};

/**
 * Get the width of the rectangle
 * @returns
 */

Rectangle.prototype.width = function () {
  return Math.abs(this.pointA.left - this.pointB.left);
};

/**
 * Get the height of the rectangle
 * @returns
 */

Rectangle.prototype.height = function () {
  return Math.abs(this.pointA.top - this.pointB.top);
};

/**
 * Get the top position of the top left corner of the rectangle
 * @returns
 */

Rectangle.prototype.top = function () {
  return this.pointA.top < this.pointB.top ? this.pointA.top : this.pointB.top;
};

/**
 * Get the left position of the top left corner of the rectangle
 * @returns
 */

Rectangle.prototype.left = function () {
  return this.pointA.left < this.pointB.left ? this.pointA.left : this.pointB.left;
};

/**
 * Draw the rectangle
 */

Rectangle.prototype.draw = function () {
  this.view.draw.call(this.view, this.top(), this.left(), this.width(), this.height());
};

/**
 * Save the rectangle into the data base
 */

Rectangle.prototype.save = function () {
  $.post("services/services.php", this.getSaveArgs.call(this), this.onSaveDone.bind(this));
};

/**
 * Get arguments for saving the rectangle into the database
 * @returns
 */

Rectangle.prototype.getSaveArgs = function () {
  return {
    request : "saveRectangle",
    topA : this.pointA.top,
    leftA : this.pointA.left,
    topB : this.pointB.top,
    leftB : this.pointB.left
  };
};

/**
 * Action called when saving has been doing well
 */

Rectangle.prototype.onSaveDone = function (json) {
  var data = drawer.utils.parseJson(json);
  if (data.success) {
    drawer.displaySuccess("The rectangle has been saved");
  } else {
    drawer.displayError(data.error ? data.error : null);
  }
};
