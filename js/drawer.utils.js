drawer.utils = drawer.utils || {};

drawer.utils.parseJson = function(s) {
  if (!s) {
    return null;
  } else if (typeof s === "object") {
    //déjà un objet
    return s;
  } else if (typeof s === "string") {
    return (typeof JSON === 'undefined') ? eval(s) : JSON.parse(s);
  } else {
    return null;
  }
};