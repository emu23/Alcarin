
namespace('Alcarin', function(exports, Alcarin) {
  exports.ActiveView = (function() {

    ActiveView.TYPE_CONTENT = 0;

    ActiveView.TYPE_ATTR = 1;

    ActiveView.regex = /{item\.(.*?)}/g;

    ActiveView.global_list = [];

    ActiveView.prototype.bindings = {};

    ActiveView.prototype.initialized = false;

    ActiveView.prototype.properties_container = {};

    function ActiveView() {
      $.merge(exports.ActiveView.global_list, [this]);
      this.properties_container = {};
      this.initialized = false;
      this.bindings = {};
    }

    ActiveView.initializeAll = function() {
      var view, _i, _len, _ref, _results;
      _ref = exports.ActiveView.global_list;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        view = _ref[_i];
        _results.push(view.initialize());
      }
      return _results;
    };

    ActiveView.prototype.propertyChanged = function(prop_name) {
      var $el, bindings, data, new_val, org, result, val, _i, _len, _results;
      if (!(this.bindings[prop_name] != null)) {
        return;
      }
      bindings = this.bindings[prop_name];
      _results = [];
      for (_i = 0, _len = bindings.length; _i < _len; _i++) {
        data = bindings[_i];
        $el = data.element;
        new_val = org = data.original;
        while (result = ActiveView.regex.exec(org)) {
          val = this.properties_container[result[1]];
          if (val != null) {
            new_val = new_val.replace(result[0], val);
          }
        }
        switch (data.type) {
          case exports.ActiveView.TYPE_CONTENT:
            _results.push($el.html(new_val));
            break;
          case exports.ActiveView.TYPE_ATTR:
            _results.push($el.attr(data.attr, new_val));
            break;
          default:
            throw new Error('"#{data.type}" type not supported.');
        }
      }
      return _results;
    };

    ActiveView.dependencyProperty = function(context, name, default_value) {
      if (default_value != null) {
        context.prototype.properties_container[name] = default_value;
      }
      return function(val) {
        if (!(val != null)) {
          return this.properties_container[name];
        }
        this.properties_container[name] = val;
        if (this.initialized) {
          return this.propertyChanged(name);
        }
      };
    };

    /*private function, storing entries in @bindings table for specific property
    names used in "content". it store object with 'type' (TYPE_ATTR/TYPE_CONTENT)
    'element' (jquery ref), 'original' ("content" value) and attribute
    */


    ActiveView.prototype.prepare_bind = function($root, $child, content, attribute) {
      var checked, property_name, result, _ref;
      checked = {};
      while (result = ActiveView.regex.exec(content)) {
        property_name = result[1];
        if (property_name != null) {
          if (checked[property_name] != null) {
            continue;
          }
          checked[property_name] = true;
          this.bindings[property_name] = (_ref = this.bindings[property_name]) != null ? _ref : [];
          this.bindings[property_name].push({
            'type': attribute != null ? exports.ActiveView.TYPE_ATTR : exports.ActiveView.TYPE_CONTENT,
            'element': $child,
            'original': content,
            'attr': attribute,
            'root': $root
          });
        }
      }
      return true;
    };

    ActiveView.prototype.bind = function(e) {
      var $e,
        _this = this;
      $e = $(e);
      $e.each(function(index, val) {
        var $child, $el, attr, child, children, list, _i, _j, _len, _len1, _ref;
        $el = $(val);
        children = $el.find('*').filter(function(i, val) {
          return !$(val).children().length;
        });
        list = children.toArray();
        if (!$el.children().length) {
          list = list.concat([$el.get(0)]);
        }
        for (_i = 0, _len = list.length; _i < _len; _i++) {
          child = list[_i];
          $child = $(child);
          _this.prepare_bind($e, $child, $child.html());
          _ref = child.attributes;
          for (_j = 0, _len1 = _ref.length; _j < _len1; _j++) {
            attr = _ref[_j];
            _this.prepare_bind($e, $child, attr.value, attr.name);
          }
        }
        return true;
      });
      return true;
    };

    ActiveView.prototype.unbind = function(e) {
      var $e, index, key, list, obj, _ref, _results;
      $e = $(e);
      _ref = this.bindings;
      _results = [];
      for (key in _ref) {
        list = _ref[key];
        _results.push((function() {
          var _i, _len, _results1;
          _results1 = [];
          for (index = _i = 0, _len = list.length; _i < _len; index = ++_i) {
            obj = list[index];
            if (obj.root.is($e)) {
              _results1.push(list.splice(index, 1));
            } else {
              _results1.push(void 0);
            }
          }
          return _results1;
        })());
      }
      return _results;
    };

    ActiveView.prototype.initialize = function() {
      var property;
      this.initialized = true;
      for (property in this.properties_container) {
        this.propertyChanged(property);
      }
      return true;
    };

    return ActiveView;

  })();
  /*
      Usage sample.
  
      class exports.TestView extends exports.ActiveView
          me    : @dependencyProperty(@, 'me')
          teraz : @dependencyProperty(@, 'teraz', 12)
  
      av = new Alcarin.TestView()
      av.me 'psychowico321'
      #av.teraz 0
      #console.log av.teraz()
      av.bind '#active-item'
  
      When you prepare class like this you can use in html:
  
      <li id="active-item">
          <span class="pepe" data-tutaj="jajajaja">tell {item.me} raz</span>
          <span>a {item.teraz} dwa powiedz</span>
          <input type="text" value="{item.teraz}">
      </li>
  
      And later, for sample in click method:
      $('#active-item').click ->
          #set "teraz" value to 13
          av.teraz 13
          #get "teraz" value, only for sample
          av.teraz()
  
      And value on template will be automatically updated.
  */

  return $(function() {
    return $(function() {
      return Alcarin.ActiveView.initializeAll();
    });
  });
});
