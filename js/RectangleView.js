
RectangleView = function (id) {
  this.id = id;
  //this.container = null;
};

RectangleView.prototype.draw = function (top, left, width, height) {
  var html = "<div class='rectangle' ";
  html += "id='" + this.id + "' ";
  html += "style=' ";
  html += "top : " + top + "px; ";
  html += "left : " + left + "px; ";
  html += "width : " + width + "px; ";
  html += "height : " + height + "px; ";
  html += "'></div>";
  $("body").append(html);
};