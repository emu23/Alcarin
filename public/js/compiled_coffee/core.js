var namespace,
  __slice = [].slice,
  _this = this;

namespace = function(target, name, block) {
  var item, main_ns, _i, _len, _ref, _ref1;
  if (arguments.length < 3) {
    _ref = [(typeof exports !== 'undefined' ? exports : window)].concat(__slice.call(arguments)), target = _ref[0], name = _ref[1], block = _ref[2];
  }
  target._ = target.Alcarin = target.Alcarin || (target.Alcarin = {});
  main_ns = target.Alcarin;
  _ref1 = name.split('.');
  for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
    item = _ref1[_i];
    target = target[item] || (target[item] = {});
  }
  return block(target, main_ns);
};

$(function() {
  return $('input[type="text"]:first').focus();
});
