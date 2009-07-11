if (window.attachEvent) {
  window["event_load" + initialize] = initialize;
  window["load" + initialize] = function () {
    window["event_load" + initialize](window.event)
  };
  window.attachEvent("onload", window["load" + initialize])
} else {
  window.addEventListener("load", initialize, false)
}
function initialize() {
  elements = getElementsByClassName(document, "*", "element");
  for (i = 0; i < elements.length; i++) {
    elements[i].onfocus = function () {
      addClassName(this.parentNode.parentNode, "highlighted", true)
    };
    elements[i].onblur = function () {
      removeClassName(this.parentNode.parentNode, "highlighted")
    }
  }
}
function getElementsByClassName(_6, _7, _8) {
  var _9 = (_7 == "*" && _6.all) ? _6.all: _6.getElementsByTagName(_7);
  var _a = new Array();
  _8 = _8.replace(/\-/g, "\\-");
  var _b = new RegExp("(^|\\s)" + _8 + "(\\s|$)");
  var _c;
  for (var i = 0; i < _9.length; i++) {
    _c = _9[i];
    if (_b.test(_c.className)) {
      _a.push(_c)
    }
  }
  return (_a)
}
function removeClassName(_e, _f) {
  if (_e.className) {
    var _10 = _e.className.split(" ");
    var _11 = _f.toUpperCase();
    for (var i = 0; i < _10.length; i++) {
      if (_10[i].toUpperCase() == _11) {
        _10.splice(i, 1);
        i--
      }
    }
    _e.className = _10.join(" ")
  }
}
function addClassName(_13, _14, _15) {
  if (_13.className) {
    var _16 = _13.className.split(" ");
    if (_15) {
      var _17 = _14.toUpperCase();
      for (var i = 0; i < _16.length; i++) {
        if (_16[i].toUpperCase() == _17) {
          _16.splice(i, 1);
          i--
        }
      }
    }
    _16[_16.length] = _14;
    _13.className = _16.join(" ")
  } else {
    _13.className = _14
  }
}
